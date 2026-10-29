<?php

namespace App\Repositories;

use App\Core\Database;
use App\Services\NhatKyService;
use PDO;
use Exception;

/**
 * Repository quản lý dữ liệu Biên bản kiểm kê & Chi tiết kiểm kê.
 * Sử dụng PDO 100% prepared statements, tuân thủ transaction an toàn.
 */
class KiemKeRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Lấy danh sách biên bản kiểm kê kèm bộ lọc tìm kiếm.
     */
    public function getAll(array $filters = []): array
    {
        $sql = "SELECT b.maBienBan, b.ngayKiemKe, b.maKho, k.tenKho, b.maNhanVienLap,
                       nv.hoTen AS tenNhanVienLap, b.trangThai, b.ngayCapNhat,
                       b.tongGiaTriThua, b.tongGiaTriThieu, b.tongGiaTriChenhLech,
                       b.giaTriChenhLechRong,
                       COUNT(c.id) AS soMatHang
                FROM bien_ban_kiem_ke b
                LEFT JOIN kho k ON b.maKho = k.maKho
                LEFT JOIN nhan_vien nv ON b.maNhanVienLap = nv.maNhanVien
                LEFT JOIN chi_tiet_kiem_ke c ON b.maBienBan = c.maBienBan
                WHERE 1=1";

        $params = [];

        if (!empty($filters['keyword'])) {
            $sql .= " AND (b.maBienBan LIKE :kw OR k.tenKho LIKE :kw2)";
            $params['kw'] = '%' . trim($filters['keyword']) . '%';
            $params['kw2'] = '%' . trim($filters['keyword']) . '%';
        }

        if (!empty($filters['maKho'])) {
            $sql .= " AND b.maKho = :maKho";
            $params['maKho'] = $filters['maKho'];
        }

        if (!empty($filters['trangThai'])) {
            $sql .= " AND b.trangThai = :trangThai";
            $params['trangThai'] = $filters['trangThai'];
        }

        if (!empty($filters['tuNgay'])) {
            $sql .= " AND b.ngayKiemKe >= :tuNgay";
            $params['tuNgay'] = $filters['tuNgay'];
        }

        if (!empty($filters['denNgay'])) {
            $sql .= " AND b.ngayKiemKe <= :denNgay";
            $params['denNgay'] = $filters['denNgay'];
        }

        $sql .= " GROUP BY b.maBienBan, b.ngayKiemKe, b.maKho, k.tenKho, b.maNhanVienLap,
                           nv.hoTen, b.trangThai, b.ngayCapNhat, b.tongGiaTriThua,
                           b.tongGiaTriThieu, b.tongGiaTriChenhLech, b.giaTriChenhLechRong
                  ORDER BY b.ngayKiemKe DESC, b.maBienBan DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tìm biên bản theo mã kèm chi tiết mặt hàng kiểm kê.
     */
    public function findByMa(string $maBienBan): ?array
    {
        $sql = "SELECT b.maBienBan, b.ngayKiemKe, b.maKho, k.tenKho, b.maNhanVienLap,
                       nvLap.hoTen AS tenNhanVienLap, b.trangThai, b.ngayCapNhat,
                       b.maNhanVienCapNhat, nvCapNhat.hoTen AS tenNhanVienCapNhat,
                       b.tongGiaTriThua, b.tongGiaTriThieu, b.tongGiaTriChenhLech,
                       b.giaTriChenhLechRong, b.lyDoChenhLech, b.yKienPheDuyet
                FROM bien_ban_kiem_ke b
                LEFT JOIN kho k ON b.maKho = k.maKho
                LEFT JOIN nhan_vien nvLap ON b.maNhanVienLap = nvLap.maNhanVien
                LEFT JOIN nhan_vien nvCapNhat ON b.maNhanVienCapNhat = nvCapNhat.maNhanVien
                WHERE b.maBienBan = :maBienBan
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['maBienBan' => $maBienBan]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$record) {
            return null;
        }

        $detailSql = "SELECT c.id, c.maBienBan, c.maSKU, m.tenHang, m.loaiHang, m.donViTinh,
                             c.maLo, l.soLo, l.ngayHetHan, c.maViTri,
                             c.soLuongSoSach, c.soLuongThucTe, c.donGia,
                             c.soLuongChenhLech, c.phanLoai, c.giaTriChenhLech
                      FROM chi_tiet_kiem_ke c
                      JOIN mat_hang m ON c.maSKU = m.maSKU
                      LEFT JOIN lo_hang l ON c.maLo = l.maLo
                      WHERE c.maBienBan = :maBienBan
                      ORDER BY c.id ASC";

        $dStmt = $this->db->prepare($detailSql);
        $dStmt->execute(['maBienBan' => $maBienBan]);
        $record['chiTiet'] = $dStmt->fetchAll(PDO::FETCH_ASSOC);

        return $record;
    }

    /**
     * Lưu kết quả tính chênh lệch kiểm kê (UC 2.6.3).
     * RÀNG BUỘC NGHIỆP VỤ:
     * - Chỉ cập nhật khi biên bản ở trạng thái DANG_KIEM_KE.
     * - Cập nhật chi tiết và tổng hợp giá trị chênh lệch.
     * - Chuyển trạng thái: <= 10.000.000 đ sang CHO_QLK_DUYET, > 10.000.000 đ sang CHO_BGD_DUYET (BR-01, BR-08).
     * - Tuyệt đối KHÔNG cập nhật tồn kho (BR-01, BR-08).
     * - Ghi nhận lịch sử vào nhat_ky_he_thong (giaTriCu và giaTriMoi).
     */
    public function saveCalculated(string $maBienBan, array $calculatedResult, string $maNhanVien, ?string $maTaiKhoan = null): bool
    {
        $hasOuterTx = $this->db->inTransaction();
        if (!$hasOuterTx) {
            $this->db->beginTransaction();
        }

        try {
            // 1. Khóa bản ghi biên bản FOR UPDATE
            $lockStmt = $this->db->prepare("SELECT * FROM bien_ban_kiem_ke WHERE maBienBan = :maBienBan FOR UPDATE");
            $lockStmt->execute(['maBienBan' => $maBienBan]);
            $currentBB = $lockStmt->fetch(PDO::FETCH_ASSOC);

            if (!$currentBB) {
                throw new Exception("Không tìm thấy biên bản kiểm kê {$maBienBan}.");
            }

            if ($currentBB['trangThai'] !== 'DANG_KIEM_KE') {
                throw new Exception("Biên bản kiểm kê {$maBienBan} đã ở trạng thái {$currentBB['trangThai']}, chỉ được xem và không thể cập nhật chênh lệch.");
            }

            // 2. Chụp snapshot giá trị cũ (giaTriCu)
            $oldDetailsStmt = $this->db->prepare("SELECT * FROM chi_tiet_kiem_ke WHERE maBienBan = :maBienBan ORDER BY id ASC");
            $oldDetailsStmt->execute(['maBienBan' => $maBienBan]);
            $giaTriCu = [
                'bienBan' => $currentBB,
                'chiTiet' => $oldDetailsStmt->fetchAll(PDO::FETCH_ASSOC)
            ];

            // 3. Cập nhật từng dòng chi tiết kiểm kê
            $updateDetailSql = "UPDATE chi_tiet_kiem_ke
                                SET soLuongChenhLech = :diff,
                                    phanLoai = :kind,
                                    giaTriChenhLech = :val
                                WHERE id = :id AND maBienBan = :maBienBan";
            $updateDetailStmt = $this->db->prepare($updateDetailSql);

            foreach ($calculatedResult['chiTiet'] as $line) {
                $updateDetailStmt->execute([
                    'diff'        => $line['soLuongChenhLech'],
                    'kind'        => $line['phanLoai'],
                    'val'         => $line['giaTriChenhLech'],
                    'id'          => $line['id'],
                    'maBienBan'   => $maBienBan,
                ]);
            }

            // 4. Phân định thẩm quyền phê duyệt theo ngưỡng 10 triệu VND (BR-01, BR-08)
            $tongChenhLech = (float) $calculatedResult['tongChenhLech'];
            $newStatus = ($tongChenhLech <= 10000000.0) ? 'CHO_QLK_DUYET' : 'CHO_BGD_DUYET';

            // 5. Cập nhật biên bản kiểm kê
            $updateBBSql = "UPDATE bien_ban_kiem_ke
                            SET trangThai = :trangThai,
                                ngayCapNhat = NOW(),
                                maNhanVienCapNhat = :maNhanVien,
                                tongGiaTriThua = :tongThua,
                                tongGiaTriThieu = :tongThieu,
                                tongGiaTriChenhLech = :tongChenhLech,
                                giaTriChenhLechRong = :giaTriRong
                            WHERE maBienBan = :maBienBan";
            $updateBBStmt = $this->db->prepare($updateBBSql);
            $updateBBStmt->execute([
                'trangThai'          => $newStatus,
                'maNhanVien'         => $maNhanVien,
                'tongThua'           => $calculatedResult['tongThua'],
                'tongThieu'          => $calculatedResult['tongThieu'],
                'tongChenhLech'      => $tongChenhLech,
                'giaTriRong'         => $calculatedResult['giaTriRong'],
                'maBienBan'          => $maBienBan,
            ]);

            // 6. Chụp snapshot giá trị mới (giaTriMoi)
            $giaTriMoi = [
                'trangThai'          => $newStatus,
                'maNhanVienCapNhat'  => $maNhanVien,
                'tongGiaTriThua'     => $calculatedResult['tongThua'],
                'tongGiaTriThieu'    => $calculatedResult['tongThieu'],
                'tongGiaTriChenhLech'=> $tongChenhLech,
                'giaTriChenhLechRong'=> $calculatedResult['giaTriRong'],
                'chiTiet'            => $calculatedResult['chiTiet']
            ];

            // 7. Ghi nhật ký hệ thống
            NhatKyService::ghi(
                $maTaiKhoan,
                'kiem-ke',
                'CAP_NHAT_CHENH_LECH',
                "Cập nhật kết quả chênh lệch cho biên bản {$maBienBan}. Trạng thái chuyển thành {$newStatus}.",
                $giaTriCu,
                $giaTriMoi
            );

            if (!$hasOuterTx) {
                $this->db->commit();
            }

            return true;
        } catch (\Throwable $e) {
            if (!$hasOuterTx && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}

