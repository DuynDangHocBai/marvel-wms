<?php

namespace App\Repositories;

use App\Core\Database;
use App\Services\NhatKyService;
use PDO;
use Exception;

/**
 * Repository quản lý hàng lỗi hỏng và đề xuất xử lý hàng lỗi hỏng (UC 2.4.3).
 * Sử dụng PDO 100% prepared statements, tuân thủ transaction an toàn.
 */
class HangLoiRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Lấy danh sách hàng lỗi hỏng kèm số lượng còn lại được tính động.
     * Số lượng còn lại = soLuongLoiHong - SUM(soLuongXuLy) của các đề xuất chưa bị từ chối/hủy.
     */
    public function getDanhSachLoi(array $filters = [], bool $onlyAvailable = false): array
    {
        $sql = "SELECT h.maHangLoi, h.maSKU, h.maLo, h.maKho, h.nguonGoc, h.maChungTuNguon,
                       h.ngayGhiNhan, h.nguyenNhan, h.soLuongLoiHong, h.donGia, h.trangThai,
                       m.tenHang, m.loaiHang, m.donViTinh,
                       l.soLo, l.ngayHetHan,
                       k.tenKho,
                       (h.soLuongLoiHong - COALESCE(
                           (SELECT SUM(ct.soLuongXuLy)
                            FROM chi_tiet_de_xuat_xu_ly ct
                            JOIN de_xuat_xu_ly_hang_loi dx ON ct.deXuatId = dx.id
                            WHERE ct.maHangLoi = h.maHangLoi AND dx.trangThai NOT IN ('TU_CHOI', 'DA_HUY')), 0
                       )) AS soLuongConLai
                FROM hang_loi_hong h
                JOIN mat_hang m ON h.maSKU = m.maSKU
                LEFT JOIN lo_hang l ON h.maLo = l.maLo
                LEFT JOIN kho k ON h.maKho = k.maKho
                WHERE h.trangThai = 'CHO_XU_LY'";

        $params = [];

        if (!empty($filters['keyword'])) {
            $sql .= " AND (m.tenHang LIKE :kw OR h.maSKU LIKE :kw2 OR l.soLo LIKE :kw3 OR h.maHangLoi LIKE :kw4)";
            $kw = '%' . trim($filters['keyword']) . '%';
            $params['kw'] = $kw;
            $params['kw2'] = $kw;
            $params['kw3'] = $kw;
            $params['kw4'] = $kw;
        }

        if (!empty($filters['maKho'])) {
            $sql .= " AND h.maKho = :maKho";
            $params['maKho'] = $filters['maKho'];
        }

        if ($onlyAvailable) {
            $sql .= " HAVING soLuongConLai > 0";
        }

        $sql .= " ORDER BY h.maHangLoi ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tìm hàng lỗi theo mã.
     */
    public function findLoiById(string $maHangLoi): ?array
    {
        $sql = "SELECT h.maHangLoi, h.maSKU, h.maLo, h.maKho, h.nguonGoc, h.maChungTuNguon,
                       h.ngayGhiNhan, h.nguyenNhan, h.soLuongLoiHong, h.donGia, h.trangThai,
                       m.tenHang, m.loaiHang, m.donViTinh,
                       l.soLo, l.ngayHetHan,
                       k.tenKho,
                       (h.soLuongLoiHong - COALESCE(
                           (SELECT SUM(ct.soLuongXuLy)
                            FROM chi_tiet_de_xuat_xu_ly ct
                            JOIN de_xuat_xu_ly_hang_loi dx ON ct.deXuatId = dx.id
                            WHERE ct.maHangLoi = h.maHangLoi AND dx.trangThai NOT IN ('TU_CHOI', 'DA_HUY')), 0
                       )) AS soLuongConLai
                FROM hang_loi_hong h
                JOIN mat_hang m ON h.maSKU = m.maSKU
                LEFT JOIN lo_hang l ON h.maLo = l.maLo
                LEFT JOIN kho k ON h.maKho = k.maKho
                WHERE h.maHangLoi = :maHangLoi
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['maHangLoi' => $maHangLoi]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Lấy danh sách các đề xuất xử lý hàng lỗi hỏng kèm bộ lọc.
     */
    public function getAllDeXuat(array $filters = []): array
    {
        $sql = "SELECT dx.id, dx.maDeXuat, dx.ngayLap, dx.maNhanVienLap, nv.hoTen AS tenNhanVienLap,
                       dx.phuongAn, dx.lyDo, dx.trangThai, dx.tongGiaTri,
                       COUNT(ct.id) AS soMatHang
                FROM de_xuat_xu_ly_hang_loi dx
                LEFT JOIN nhan_vien nv ON dx.maNhanVienLap = nv.maNhanVien
                LEFT JOIN chi_tiet_de_xuat_xu_ly ct ON dx.id = ct.deXuatId
                WHERE 1=1";

        $params = [];

        if (!empty($filters['keyword'])) {
            $sql .= " AND (dx.maDeXuat LIKE :kw OR dx.lyDo LIKE :kw2)";
            $kw = '%' . trim($filters['keyword']) . '%';
            $params['kw'] = $kw;
            $params['kw2'] = $kw;
        }

        if (!empty($filters['phuongAn'])) {
            $sql .= " AND dx.phuongAn = :phuongAn";
            $params['phuongAn'] = $filters['phuongAn'];
        }

        if (!empty($filters['trangThai'])) {
            $sql .= " AND dx.trangThai = :trangThai";
            $params['trangThai'] = $filters['trangThai'];
        }

        if (!empty($filters['tuNgay'])) {
            $sql .= " AND DATE(dx.ngayLap) >= :tuNgay";
            $params['tuNgay'] = $filters['tuNgay'];
        }

        if (!empty($filters['denNgay'])) {
            $sql .= " AND DATE(dx.ngayLap) <= :denNgay";
            $params['denNgay'] = $filters['denNgay'];
        }

        $sql .= " GROUP BY dx.id, dx.maDeXuat, dx.ngayLap, dx.maNhanVienLap, nv.hoTen,
                           dx.phuongAn, dx.lyDo, dx.trangThai, dx.tongGiaTri
                  ORDER BY dx.ngayLap DESC, dx.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tìm thông tin đề xuất xử lý theo mã kèm danh sách mặt hàng chi tiết.
     */
    public function findDeXuatByMa(string $maDeXuat): ?array
    {
        $sql = "SELECT dx.id, dx.maDeXuat, dx.ngayLap, dx.maNhanVienLap, nv.hoTen AS tenNhanVienLap,
                       dx.phuongAn, dx.lyDo, dx.trangThai, dx.tongGiaTri
                FROM de_xuat_xu_ly_hang_loi dx
                LEFT JOIN nhan_vien nv ON dx.maNhanVienLap = nv.maNhanVien
                WHERE dx.maDeXuat = :maDeXuat
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['maDeXuat' => $maDeXuat]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$record) {
            return null;
        }

        $detailSql = "SELECT ct.id, ct.deXuatId, ct.maHangLoi, ct.maSKU, m.tenHang, m.donViTinh,
                             ct.soLo, ct.ngayHetHan, ct.soLuongXuLy, ct.donGia, ct.thanhTien,
                             h.maChungTuNguon, h.nguyenNhan
                      FROM chi_tiet_de_xuat_xu_ly ct
                      JOIN mat_hang m ON ct.maSKU = m.maSKU
                      LEFT JOIN hang_loi_hong h ON ct.maHangLoi = h.maHangLoi
                      WHERE ct.deXuatId = :deXuatId
                      ORDER BY ct.id ASC";

        $dStmt = $this->db->prepare($detailSql);
        $dStmt->execute(['deXuatId' => $record['id']]);
        $record['chiTiet'] = $dStmt->fetchAll(PDO::FETCH_ASSOC);

        return $record;
    }

    /**
     * Sinh mã đề xuất tự động theo quy tắc: DXLH-YYYYMMDD-NNNN
     */
    public function generateMaDeXuat(): string
    {
        $prefix = 'DXLH-' . date('Ymd') . '-';
        $sql = "SELECT maDeXuat FROM de_xuat_xu_ly_hang_loi WHERE maDeXuat LIKE :prefix ORDER BY maDeXuat DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['prefix' => $prefix . '%']);
        $lastCode = $stmt->fetchColumn();

        if ($lastCode) {
            $parts = explode('-', $lastCode);
            $seq = (int) end($parts);
            $nextSeq = $seq + 1;
        } else {
            $nextSeq = 1;
        }

        return $prefix . str_pad((string)$nextSeq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Lưu đề xuất xử lý hàng lỗi hỏng mới (UC 2.4.3).
     * RÀNG BUỘC:
     * - Khóa các dòng hàng lỗi FOR UPDATE để tránh race condition khi cùng đề xuất 1 lô.
     * - Kiểm tra số lượng xử lý <= số lỗi còn lại.
     * - Snapshot mã SKU, số lô, hạn sử dụng, đơn giá vào chi_tiet_de_xuat_xu_ly.
     * - Không sửa cột soLuongConLai trong schema, số lượng tự động trừ theo các đề xuất.
     * - Ghi nhận nhat_ky_he_thong.
     */
    public function saveDeXuat(array $validatedData, string $maNhanVien, ?string $maTaiKhoan = null): string
    {
        $hasOuterTx = $this->db->inTransaction();
        if (!$hasOuterTx) {
            $this->db->beginTransaction();
        }

        try {
            $details = $validatedData['chiTiet'];
            if (empty($details)) {
                throw new Exception("Đề xuất phải có ít nhất một mặt hàng xử lý.");
            }

            // 1. Khóa các mặt hàng lỗi được chọn FOR UPDATE và kiểm tra số lượng còn lại
            $maLoiList = array_column($details, 'maHangLoi');
            $placeholders = implode(',', array_fill(0, count($maLoiList), '?'));

            $lockSql = "SELECT h.maHangLoi, h.soLuongLoiHong, m.tenHang,
                               (h.soLuongLoiHong - COALESCE(
                                   (SELECT SUM(ct.soLuongXuLy)
                                    FROM chi_tiet_de_xuat_xu_ly ct
                                    JOIN de_xuat_xu_ly_hang_loi dx ON ct.deXuatId = dx.id
                                    WHERE ct.maHangLoi = h.maHangLoi AND dx.trangThai NOT IN ('TU_CHOI', 'DA_HUY')), 0
                               )) AS soLuongConLai
                        FROM hang_loi_hong h
                        JOIN mat_hang m ON h.maSKU = m.maSKU
                        WHERE h.maHangLoi IN ({$placeholders})
                        FOR UPDATE";

            $lockStmt = $this->db->prepare($lockSql);
            $lockStmt->execute($maLoiList);
            $lockedRows = $lockStmt->fetchAll(PDO::FETCH_ASSOC);

            $availableMap = [];
            foreach ($lockedRows as $row) {
                $availableMap[$row['maHangLoi']] = (float)$row['soLuongConLai'];
            }

            foreach ($details as $d) {
                $id = $d['maHangLoi'];
                $qty = (float)$d['soLuongXuLy'];
                $avail = $availableMap[$id] ?? 0.0;

                if ($qty > $avail) {
                    $ten = $d['tenHang'] ?? $id;
                    throw new Exception("Mặt hàng {$ten}: Số lượng xử lý ({$qty}) vượt quá số lượng lỗi còn lại ({$avail}).");
                }
            }

            // 2. Sinh mã đề xuất
            $maDeXuat = $this->generateMaDeXuat();

            // 3. Thêm bản ghi vào de_xuat_xu_ly_hang_loi
            $insertHeaderSql = "INSERT INTO de_xuat_xu_ly_hang_loi
                                (maDeXuat, ngayLap, maNhanVienLap, phuongAn, lyDo, trangThai, tongGiaTri)
                                VALUES
                                (:maDeXuat, NOW(), :maNhanVienLap, :phuongAn, :lyDo, 'CHO_XU_LY', :tongGiaTri)";

            $stmtHeader = $this->db->prepare($insertHeaderSql);
            $stmtHeader->execute([
                'maDeXuat'       => $maDeXuat,
                'maNhanVienLap'  => $maNhanVien,
                'phuongAn'       => $validatedData['phuongAn'],
                'lyDo'           => $validatedData['lyDo'],
                'tongGiaTri'     => $validatedData['tongGiaTri'],
            ]);

            $deXuatId = (int)$this->db->lastInsertId();

            // 4. Thêm từng dòng chi tiết kèm snapshot lô và HSD
            $insertDetailSql = "INSERT INTO chi_tiet_de_xuat_xu_ly
                                (deXuatId, maHangLoi, maSKU, soLo, ngayHetHan, soLuongXuLy, donGia, thanhTien)
                                VALUES
                                (:deXuatId, :maHangLoi, :maSKU, :soLo, :ngayHetHan, :soLuongXuLy, :donGia, :thanhTien)";
            $stmtDetail = $this->db->prepare($insertDetailSql);

            foreach ($details as $line) {
                $stmtDetail->execute([
                    'deXuatId'    => $deXuatId,
                    'maHangLoi'   => $line['maHangLoi'],
                    'maSKU'       => $line['maSKU'],
                    'soLo'        => $line['soLo'],
                    'ngayHetHan'  => $line['ngayHetHan'],
                    'soLuongXuLy' => $line['soLuongXuLy'],
                    'donGia'      => $line['donGia'],
                    'thanhTien'   => $line['thanhTien'],
                ]);
            }

            // 5. Ghi nhật ký hệ thống
            $giaTriMoi = [
                'id'         => $deXuatId,
                'maDeXuat'   => $maDeXuat,
                'phuongAn'   => $validatedData['phuongAn'],
                'lyDo'       => $validatedData['lyDo'],
                'tongGiaTri' => $validatedData['tongGiaTri'],
                'chiTiet'    => $details,
            ];

            NhatKyService::ghi(
                $maTaiKhoan,
                'hang-loi',
                'TAO_DE_XUAT',
                "Lập đề xuất xử lý hàng lỗi hỏng {$maDeXuat} với tổng giá trị " . number_format($validatedData['tongGiaTri'], 0, ',', '.') . " đ.",
                null,
                $giaTriMoi
            );

            if (!$hasOuterTx) {
                $this->db->commit();
            }

            return $maDeXuat;
        } catch (\Throwable $e) {
            if (!$hasOuterTx && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}

