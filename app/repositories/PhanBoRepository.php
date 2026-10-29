<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * Repository: Phân bổ số lượng nguyên vật liệu theo lô/vị trí (Prompt 5 - QLK)
 */
class PhanBoRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Lấy danh sách lệnh sản xuất đang chờ phân bổ (DA_PHAT hoặc DANG_PHAN_BO và còn NVL chưa đủ)
     */
    public function getPendingOrders(?string $keyword = null): array
    {
        $sql = "SELECT lsx.maLenhSX, lsx.maKeHoach, lsx.maXuong, xsx.tenXuong,
                       lsx.ngayLap, lsx.ngayBatDau, lsx.ngayHoanThanh, lsx.trangThai, lsx.ghiChu,
                       COUNT(nvl.id) AS tongLoaiNVL,
                       SUM(CASE WHEN nvl.trangThai = 'DA_PHAN_BO_DU' THEN 1 ELSE 0 END) AS soLoaiNVLDaDu,
                       SUM(nvl.soLuongCanCap) AS tongCanCap,
                       SUM(nvl.soLuongDaPhanBo) AS tongDaPhanBo,
                       SUM(nvl.soLuongCanCap - nvl.soLuongDaPhanBo) AS tongConThieu
                FROM lenh_san_xuat lsx
                JOIN ke_hoach_san_xuat kh ON lsx.maKeHoach = kh.maKeHoach
                LEFT JOIN xuong_san_xuat xsx ON lsx.maXuong = xsx.maXuong
                JOIN nvl_can_cap_lenh_sx nvl ON lsx.maLenhSX = nvl.maLenhSX
                WHERE lsx.trangThai IN ('DA_PHAT', 'DANG_PHAN_BO')
                GROUP BY lsx.maLenhSX, lsx.maKeHoach, lsx.maXuong, xsx.tenXuong,
                         lsx.ngayLap, lsx.ngayBatDau, lsx.ngayHoanThanh, lsx.trangThai, lsx.ghiChu
                HAVING tongConThieu > 0";

        $params = [];
        if (!empty($keyword)) {
            $sql .= " AND (lsx.maLenhSX LIKE :kw OR lsx.maKeHoach LIKE :kw OR xsx.tenXuong LIKE :kw)";
            $params['kw'] = '%' . trim($keyword) . '%';
        }

        $sql .= " ORDER BY lsx.ngayLap DESC, lsx.maLenhSX DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lịch sử phiếu phân bổ số lượng
     */
    public function getAllocationHistory(array $filters = []): array
    {
        $sql = "SELECT pb.maPhieuPhanBo, pb.maLenhSX, pb.maKhoNguon, k.tenKho AS tenKhoNguon,
                       pb.loaiNoiNhan, pb.maNoiNhan, pb.ngayPhanBo, pb.maNhanVien, nv.hoTen AS tenNhanVien,
                       pb.trangThai, pb.ghiChu,
                       COUNT(ct.id) AS soDongPhanBo,
                       SUM(ct.soLuongPhanBo) AS tongSoLuongPhanBo
                FROM phieu_phan_bo_so_luong pb
                JOIN kho k ON pb.maKhoNguon = k.maKho
                LEFT JOIN nhan_vien nv ON pb.maNhanVien = nv.maNhanVien
                LEFT JOIN chi_tiet_phan_bo ct ON pb.maPhieuPhanBo = ct.maPhieuPhanBo
                WHERE 1=1";

        $params = [];
        if (!empty($filters['keyword'])) {
            $sql .= " AND (pb.maPhieuPhanBo LIKE :kw OR pb.maLenhSX LIKE :kw OR k.tenKho LIKE :kw OR nv.hoTen LIKE :kw)";
            $params['kw'] = '%' . trim($filters['keyword']) . '%';
        }
        if (!empty($filters['maKho'])) {
            $sql .= " AND pb.maKhoNguon = :maKho";
            $params['maKho'] = $filters['maKho'];
        }
        if (!empty($filters['trangThai'])) {
            $sql .= " AND pb.trangThai = :trangThai";
            $params['trangThai'] = $filters['trangThai'];
        }

        $sql .= " GROUP BY pb.maPhieuPhanBo, pb.maLenhSX, pb.maKhoNguon, k.tenKho,
                           pb.loaiNoiNhan, pb.maNoiNhan, pb.ngayPhanBo, pb.maNhanVien, nv.hoTen,
                           pb.trangThai, pb.ghiChu
                  ORDER BY pb.ngayPhanBo DESC, pb.maPhieuPhanBo DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Chi tiết phiếu phân bổ kèm danh sách lô/vị trí
     */
    public function getAllocationDetail(string $maPhieuPhanBo): ?array
    {
        $sql = "SELECT pb.maPhieuPhanBo, pb.maLenhSX, pb.maKhoNguon, k.tenKho AS tenKhoNguon,
                       pb.loaiNoiNhan, pb.maNoiNhan, pb.ngayPhanBo, pb.maNhanVien, nv.hoTen AS tenNhanVien,
                       pb.trangThai, pb.ghiChu
                FROM phieu_phan_bo_so_luong pb
                JOIN kho k ON pb.maKhoNguon = k.maKho
                LEFT JOIN nhan_vien nv ON pb.maNhanVien = nv.maNhanVien
                WHERE pb.maPhieuPhanBo = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $maPhieuPhanBo]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            return null;
        }

        $itemSql = "SELECT ct.id, ct.maLo, ct.maViTri, CONCAT(vt.maViTri, ' (Kệ ', COALESCE(vt.maKe, '-'), ')') AS tenViTri, ct.maSKU, mh.tenHang, mh.donViTinh,
                           lh.ngayHetHan, ct.soLuongPhanBo
                    FROM chi_tiet_phan_bo ct
                    JOIN mat_hang mh ON ct.maSKU = mh.maSKU
                    JOIN lo_hang lh ON ct.maLo = lh.maLo
                    LEFT JOIN vi_tri_luu_kho vt ON ct.maViTri = vt.maViTri
                    WHERE ct.maPhieuPhanBo = :id
                    ORDER BY ct.id ASC";

        $iStmt = $this->db->prepare($itemSql);
        $iStmt->execute(['id' => $maPhieuPhanBo]);
        $order['items'] = $iStmt->fetchAll(PDO::FETCH_ASSOC);

        // Tính KPI
        $tongSL = 0.0;
        foreach ($order['items'] as $it) {
            $tongSL += (float)$it['soLuongPhanBo'];
        }
        $order['tongSoLuong'] = $tongSL;
        $order['soLoHang'] = count(array_unique(array_column($order['items'], 'maLo')));

        return $order;
    }

    /**
     * Danh sách NVL cần cấp cho lệnh sản xuất
     */
    public function getNVLNeedsForOrder(string $maLenhSX): array
    {
        $sql = "SELECT nvl.id, nvl.maLenhSX, nvl.maSKU, mh.tenHang, mh.donViTinh,
                       nvl.soLuongCanCap, nvl.soLuongDaPhanBo,
                       (nvl.soLuongCanCap - nvl.soLuongDaPhanBo) AS soLuongConLai,
                       nvl.trangThai
                FROM nvl_can_cap_lenh_sx nvl
                JOIN mat_hang mh ON nvl.maSKU = mh.maSKU
                WHERE nvl.maLenhSX = :maLenhSX
                ORDER BY nvl.id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['maLenhSX' => $maLenhSX]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Danh sách kho hoạt động
     */
    public function getWarehouses(): array
    {
        $sql = "SELECT maKho, tenKho, diaDiem, loaiKho, sucChuaToiDa, sucChuaDaSuDung, sucChuaDaHenNhap
                FROM kho
                WHERE trangThai = 'HOAT_DONG'
                ORDER BY maKho ASC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy các lô hàng khả dụng theo mặt hàng (FEFO: hạn sử dụng sớm xếp trước)
     * Loại bỏ các lô CACH_LY, CHO_KIEM, KHONG_DAT hoặc hết hạn (BR-02, BR-09)
     */
    public function getAvailableLotsForSKU(string $maSKU, ?string $maKho = null): array
    {
        $sql = "SELECT tk.maLo, tk.maViTri, CONCAT(vt.maViTri, ' (Kệ ', COALESCE(vt.maKe, '-'), ')') AS tenViTri, vt.maKho, k.tenKho,
                       lh.maSKU, mh.tenHang, mh.donViTinh,
                       lh.ngaySanXuat, lh.ngayHetHan, lh.trangThaiChatLuong,
                       DATEDIFF(lh.ngayHetHan, CURDATE()) AS soNgayConLai,
                       tk.soLuongThucTe, tk.soLuongGiuCho,
                       (tk.soLuongThucTe - tk.soLuongGiuCho) AS tonKhaDungLo
                FROM ton_kho tk
                JOIN lo_hang lh ON tk.maLo = lh.maLo
                JOIN mat_hang mh ON lh.maSKU = mh.maSKU
                JOIN vi_tri_luu_kho vt ON tk.maViTri = vt.maViTri
                JOIN kho k ON vt.maKho = k.maKho
                WHERE lh.maSKU = :maSKU
                  AND lh.trangThaiChatLuong = 'DAT'
                  AND (lh.ngayHetHan IS NULL OR lh.ngayHetHan >= CURDATE())
                  AND (tk.soLuongThucTe - tk.soLuongGiuCho) > 0";

        $params = ['maSKU' => $maSKU];

        if (!empty($maKho)) {
            $sql .= " AND vt.maKho = :maKho";
            $params['maKho'] = $maKho;
        }

        // FEFO: sắp xếp ngày hết hạn tăng dần (NULL xếp cuối)
        $sql .= " ORDER BY CASE WHEN lh.ngayHetHan IS NULL THEN 1 ELSE 0 END ASC, lh.ngayHetHan ASC, tk.maLo ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy 1 dòng trong ton_kho (có hỗ trợ FOR UPDATE để chống race condition)
     */
    public function getTonKhoRow(string $maLo, string $maViTri, bool $forUpdate = false): ?array
    {
        $sql = "SELECT id, maLo, maViTri, soLuongThucTe, soLuongGiuCho,
                       (soLuongThucTe - soLuongGiuCho) AS tonKhaDungLo
                FROM ton_kho
                WHERE maLo = :maLo AND maViTri = :maViTri
                LIMIT 1";

        if ($forUpdate) {
            $sql .= " FOR UPDATE";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['maLo' => $maLo, 'maViTri' => $maViTri]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Lấy 1 dòng trong nvl_can_cap_lenh_sx (có hỗ trợ FOR UPDATE)
     */
    public function getOrderNVLRow(string $maLenhSX, string $maSKU, bool $forUpdate = false): ?array
    {
        $sql = "SELECT id, maLenhSX, maSKU, soLuongCanCap, soLuongDaPhanBo,
                       (soLuongCanCap - soLuongDaPhanBo) AS soLuongConLai, trangThai
                FROM nvl_can_cap_lenh_sx
                WHERE maLenhSX = :maLenhSX AND maSKU = :maSKU
                LIMIT 1";

        if ($forUpdate) {
            $sql .= " FOR UPDATE";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['maLenhSX' => $maLenhSX, 'maSKU' => $maSKU]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Lấy header lệnh sản xuất
     */
    public function getOrderHeader(string $maLenhSX, bool $forUpdate = false): ?array
    {
        $sql = "SELECT maLenhSX, maKeHoach, maXuong, ngayLap, ngayBatDau, ngayHoanThanh, trangThai, ghiChu
                FROM lenh_san_xuat
                WHERE maLenhSX = :maLenhSX
                LIMIT 1";

        if ($forUpdate) {
            $sql .= " FOR UPDATE";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['maLenhSX' => $maLenhSX]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Xem vw_ton_kho_kha_dung
     */
    public function getVwTonKhoKhaDung(string $maSKU): ?array
    {
        $sql = "SELECT maSKU, tenHang, loaiHang, donViTinh, tonToiThieu, tongTonKhaDungLo, tongGiuChoLSX, tonKhaDung
                FROM vw_ton_kho_kha_dung
                WHERE maSKU = :maSKU
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['maSKU' => $maSKU]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Sinh mã phiếu phân bổ: PB-YYMMDD-NNNN
     */
    public function generateMaPhanBo(): string
    {
        $prefix = 'PB-' . date('ymd') . '-';
        $sql = "SELECT maPhieuPhanBo FROM phieu_phan_bo_so_luong WHERE maPhieuPhanBo LIKE :prefix ORDER BY maPhieuPhanBo DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['prefix' => $prefix . '%']);
        $lastCode = $stmt->fetchColumn();

        if ($lastCode) {
            $parts = explode('-', $lastCode);
            $seq = (int) end($parts);
            $nextSeq = str_pad((string)($seq + 1), 4, '0', STR_PAD_LEFT);
        } else {
            $nextSeq = '0001';
        }

        return $prefix . $nextSeq;
    }

    /**
     * Tạo phiếu phân bổ và cập nhật giữ chỗ kho trong 1 transaction an toàn
     */
    public function createAllocation(
        string $maLenhSX,
        string $maKhoNguon,
        array $allocations,
        string $maNhanVien,
        string $ghiChu = '',
        ?int $maTaiKhoan = null
    ): string {
        $wasInTransaction = $this->db->inTransaction();
        if (!$wasInTransaction) {
            $this->db->beginTransaction();
        }

        try {
            $maPhanBo = $this->generateMaPhanBo();

            // 1. Tạo phiếu phân bổ
            $pbSql = "INSERT INTO phieu_phan_bo_so_luong 
                      (maPhieuPhanBo, maLenhSX, maKhoNguon, loaiNoiNhan, maNoiNhan, ngayPhanBo, maNhanVien, trangThai, ghiChu)
                      VALUES (:maPhieu, :maLenhSX, :maKhoNguon, 'LENH_SAN_XUAT', :maNoiNhan, NOW(), :maNV, 'HOAN_THANH', :ghiChu)";
            $pbStmt = $this->db->prepare($pbSql);
            $pbStmt->execute([
                'maPhieu'    => $maPhanBo,
                'maLenhSX'   => $maLenhSX,
                'maKhoNguon' => $maKhoNguon,
                'maNoiNhan'  => $maLenhSX,
                'maNV'       => $maNhanVien,
                'ghiChu'     => $ghiChu,
            ]);

            // Chuẩn bị statement
            $detailSql = "INSERT INTO chi_tiet_phan_bo (maPhieuPhanBo, maLo, maViTri, maSKU, soLuongPhanBo)
                          VALUES (:maPhieu, :maLo, :maViTri, :maSKU, :soLuong)";
            $detailStmt = $this->db->prepare($detailSql);

            $updateTkSql = "UPDATE ton_kho 
                            SET soLuongGiuCho = soLuongGiuCho + :soLuong 
                            WHERE maLo = :maLo AND maViTri = :maViTri";
            $updateTkStmt = $this->db->prepare($updateTkSql);

            $gdSql = "INSERT INTO giao_dich_kho 
                      (loaiGiaoDich, maChungTu, loaiChungTu, ngayGiaoDich, maLo, maViTri, maSKU, soLuongThayDoi, tonTruocGiaoDich, tonSauGiaoDich, maNhanVien, ghiChu)
                      VALUES ('PHAN_BO_GIU_CHO', :maChungTu, 'PHIEU_PHAN_BO', NOW(), :maLo, :maViTri, :maSKU, :soLuong, :tonTruoc, :tonSau, :maNV, :ghiChu)";
            $gdStmt = $this->db->prepare($gdSql);

            $updateNvlSql = "UPDATE nvl_can_cap_lenh_sx 
                             SET trangThai = CASE 
                                     WHEN (soLuongDaPhanBo + :soLuongCheck) >= soLuongCanCap THEN 'DA_PHAN_BO_DU'
                                     ELSE 'DANG_PHAN_BO'
                                 END,
                                 soLuongDaPhanBo = soLuongDaPhanBo + :soLuong
                             WHERE maLenhSX = :maLenhSX AND maSKU = :maSKU";
            $updateNvlStmt = $this->db->prepare($updateNvlSql);

            // Tổng hợp số lượng phân bổ theo SKU để đối chiếu
            $skuAllocTotals = [];

            // 2. Duyệt từng dòng phân bổ
            foreach ($allocations as $item) {
                $maLo = $item['maLo'];
                $maViTri = $item['maViTri'];
                $maSKU = $item['maSKU'];
                $qty = (float)$item['soLuongPhanBo'];

                if ($qty <= 0) {
                    continue;
                }

                // Khóa dòng ton_kho FOR UPDATE
                $tkRow = $this->getTonKhoRow($maLo, $maViTri, true);
                if (!$tkRow) {
                    throw new \RuntimeException("Không tìm thấy dòng tồn kho cho lô {$maLo}, vị trí {$maViTri}.");
                }

                $available = (float)$tkRow['tonKhaDungLo'];
                if ($qty > $available) {
                    throw new \RuntimeException("Số lượng phân bổ vượt quá số lượng tồn khả dụng.");
                }

                // Ghi chi_tiet_phan_bo
                $detailStmt->execute([
                    'maPhieu'  => $maPhanBo,
                    'maLo'     => $maLo,
                    'maViTri'  => $maViTri,
                    'maSKU'    => $maSKU,
                    'soLuong'  => $qty,
                ]);

                // Cập nhật ton_kho.soLuongGiuCho
                $updateTkStmt->execute([
                    'soLuong' => $qty,
                    'maLo'    => $maLo,
                    'maViTri' => $maViTri,
                ]);

                // Ghi giao_dich_kho
                $gdStmt->execute([
                    'maChungTu' => $maPhanBo,
                    'maLo'      => $maLo,
                    'maViTri'   => $maViTri,
                    'maSKU'     => $maSKU,
                    'soLuong'   => $qty,
                    'tonTruoc'  => $tkRow['soLuongGiuCho'],
                    'tonSau'    => $tkRow['soLuongGiuCho'] + $qty,
                    'maNV'      => $maNhanVien,
                    'ghiChu'    => "Phân bổ giữ chỗ cho lệnh {$maLenhSX}",
                ]);

                if (!isset($skuAllocTotals[$maSKU])) {
                    $skuAllocTotals[$maSKU] = 0.0;
                }
                $skuAllocTotals[$maSKU] += $qty;
            }

            // 3. Cập nhật nvl_can_cap_lenh_sx theo từng SKU
            foreach ($skuAllocTotals as $sku => $totalAllocQty) {
                // Khóa dòng nvl_can_cap_lenh_sx
                $nvlRow = $this->getOrderNVLRow($maLenhSX, $sku, true);
                if ($nvlRow) {
                    $remainNeed = (float)$nvlRow['soLuongConLai'];
                    if ($totalAllocQty > $remainNeed) {
                        throw new \RuntimeException("Tổng số lượng phân bổ của {$sku} ({$totalAllocQty}) vượt quá nhu cầu cần cấp còn lại ({$remainNeed}).");
                    }

                    $updateNvlStmt->execute([
                        'soLuong'      => $totalAllocQty,
                        'soLuongCheck' => $totalAllocQty,
                        'maLenhSX'     => $maLenhSX,
                        'maSKU'        => $sku,
                    ]);
                }
            }

            // 4. Kiểm tra trạng thái lệnh sản xuất
            $allNvl = $this->getNVLNeedsForOrder($maLenhSX);
            $allCompleted = true;
            foreach ($allNvl as $n) {
                if ($n['trangThai'] !== 'DA_PHAN_BO_DU') {
                    $allCompleted = false;
                    break;
                }
            }

            // Cập nhật lệnh sản xuất sang DANG_PHAN_BO (hoặc giữ nguyên nếu cần)
            $updateOrderSql = "UPDATE lenh_san_xuat SET trangThai = 'DANG_PHAN_BO' WHERE maLenhSX = :maLenhSX";
            $this->db->prepare($updateOrderSql)->execute(['maLenhSX' => $maLenhSX]);

            // 5. Ghi nhật ký hệ thống
            $this->logActivity(
                $maTaiKhoan,
                'PHAN_BO_SO_LUONG',
                "Tạo phiếu phân bổ {$maPhanBo} cho lệnh {$maLenhSX}",
                null,
                ['maPhieuPhanBo' => $maPhanBo, 'maLenhSX' => $maLenhSX, 'allocations' => $allocations]
            );

            if (!$wasInTransaction) {
                $this->db->commit();
            }

            return $maPhanBo;

        } catch (\Throwable $e) {
            if (!$wasInTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Đếm số lượng bản ghi giao_dich_kho theo mã chứng từ
     */
    public function countGiaoDichKhoForChungTu(string $maChungTu): int
    {
        $sql = "SELECT COUNT(*) FROM giao_dich_kho WHERE maChungTu = :maChungTu";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['maChungTu' => $maChungTu]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Ghi nhật ký hệ thống
     */
    private function logActivity(?int $maTaiKhoan, string $hanhDong, string $moTa, ?array $cu = null, ?array $moi = null): void
    {
        try {
            $sql = "INSERT INTO nhat_ky_he_thong 
                    (maTaiKhoan, diaChiIP, thoiGian, chucNang, hanhDong, giaTriCu, giaTriMoi)
                    VALUES (:tk, :ip, NOW(), 'phan-bo', :hd, :cu, :moi)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'tk'  => $maTaiKhoan,
                'ip'  => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'hd'  => $hanhDong,
                'cu'  => $cu ? json_encode($cu, JSON_UNESCAPED_UNICODE) : null,
                'moi' => $moi ? json_encode($moi, JSON_UNESCAPED_UNICODE) : null,
            ]);
        } catch (\Throwable $e) {
            // Không ngắt transaction nếu lỗi ghi log
        }
    }
}
