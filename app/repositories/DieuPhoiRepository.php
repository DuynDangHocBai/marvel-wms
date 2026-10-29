<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * Repository: Điều phối nhập kho & Quản lý lệnh điều phối (Prompt 6 - QLK, NVK)
 */
class DieuPhoiRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Lấy danh sách phiếu nhập kho đang chờ điều phối (CHO_DIEU_PHOI)
     */
    public function getPendingInboundRequests(?string $keyword = null, ?string $maKho = null): array
    {
        $sql = "SELECT pn.maPhieuNhap, pn.maKho, k.tenKho, k.trangThai AS trangThaiKho,
                       pn.maDonMua, pn.maNCC, ncc.tenNCC,
                       pn.ngayLap, pn.ngayHenGiao, pn.tongSoLuong, pn.tongTheTich,
                       pn.trangThai, pn.ghiChu,
                       COUNT(ct.id) AS soDongHang
                FROM phieu_nhap_kho pn
                JOIN kho k ON pn.maKho = k.maKho
                LEFT JOIN nha_cung_cap ncc ON pn.maNCC = ncc.maNCC
                LEFT JOIN chi_tiet_phieu_nhap_kho ct ON pn.maPhieuNhap = ct.maPhieuNhap
                WHERE pn.trangThai = 'CHO_DIEU_PHOI'";

        $params = [];
        if (!empty($keyword)) {
            $sql .= " AND (pn.maPhieuNhap LIKE :kw OR pn.maDonMua LIKE :kw OR k.tenKho LIKE :kw OR ncc.tenNCC LIKE :kw)";
            $params['kw'] = '%' . trim($keyword) . '%';
        }
        if (!empty($maKho)) {
            $sql .= " AND pn.maKho = :maKho";
            $params['maKho'] = $maKho;
        }

        $sql .= " GROUP BY pn.maPhieuNhap, pn.maKho, k.tenKho, k.trangThai,
                           pn.maDonMua, pn.maNCC, ncc.tenNCC,
                           pn.ngayLap, pn.ngayHenGiao, pn.tongSoLuong, pn.tongTheTich,
                           pn.trangThai, pn.ghiChu
                  ORDER BY pn.ngayLap DESC, pn.maPhieuNhap DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy tất cả phiếu nhập kho (cho tab Lịch sử / Tra cứu)
     */
    public function getAllInboundRequests(array $filters = []): array
    {
        $sql = "SELECT pn.maPhieuNhap, pn.maKho, k.tenKho,
                       pn.maDonMua, pn.maNCC, ncc.tenNCC,
                       pn.ngayLap, pn.ngayHenGiao, pn.ngayNhap,
                       pn.tongSoLuong, pn.tongTheTich, pn.trangThai, pn.ghiChu,
                       ldp.maLenhDieuPhoi, ldp.trangThai AS trangThaiLDP
                FROM phieu_nhap_kho pn
                JOIN kho k ON pn.maKho = k.maKho
                LEFT JOIN nha_cung_cap ncc ON pn.maNCC = ncc.maNCC
                LEFT JOIN lenh_dieu_phoi_kho ldp ON pn.maPhieuNhap = ldp.maPhieuNhap
                WHERE 1=1";

        $params = [];
        if (!empty($filters['keyword'])) {
            $sql .= " AND (pn.maPhieuNhap LIKE :kw OR pn.maDonMua LIKE :kw OR k.tenKho LIKE :kw OR ncc.tenNCC LIKE :kw)";
            $params['kw'] = '%' . trim($filters['keyword']) . '%';
        }
        if (!empty($filters['maKho'])) {
            $sql .= " AND pn.maKho = :maKho";
            $params['maKho'] = $filters['maKho'];
        }
        if (!empty($filters['trangThai'])) {
            $sql .= " AND pn.trangThai = :trangThai";
            $params['trangThai'] = $filters['trangThai'];
        }

        $sql .= " ORDER BY pn.ngayLap DESC, pn.maPhieuNhap DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Chi tiết phiếu nhập kho kèm mặt hàng và thông tin lô / kiểm định QC
     */
    public function getInboundRequest(string $maPhieuNhap): ?array
    {
        $sql = "SELECT pn.maPhieuNhap, pn.maKho, k.tenKho, k.trangThai AS trangThaiKho,
                       k.sucChuaToiDa, k.sucChuaDaSuDung, k.sucChuaDaHenNhap,
                       (k.sucChuaToiDa - k.sucChuaDaSuDung - k.sucChuaDaHenNhap) AS sucChuaKhaDungKho,
                       pn.maDonMua, pn.maNCC, ncc.tenNCC,
                       pn.ngayLap, pn.ngayHenGiao, pn.ngayNhap, pn.maNhanVienLap,
                       nv.hoTen AS tenNhanVienLap,
                       pn.tongSoLuong, pn.tongTheTich, pn.trangThai, pn.ghiChu
                FROM phieu_nhap_kho pn
                JOIN kho k ON pn.maKho = k.maKho
                LEFT JOIN nha_cung_cap ncc ON pn.maNCC = ncc.maNCC
                LEFT JOIN nhan_vien nv ON pn.maNhanVienLap = nv.maNhanVien
                WHERE pn.maPhieuNhap = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $maPhieuNhap]);
        $req = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$req) {
            return null;
        }

        $itemSql = "SELECT ct.id, ct.maLo, ct.maSKU, mh.tenHang, mh.loaiHang, mh.donViTinh,
                           ct.soLuong, ct.theTichDonVi, ct.tongTheTich,
                           lh.ngaySanXuat, lh.ngayHetHan, lh.trangThaiChatLuong, lh.trangThai AS trangThaiLo,
                           DATEDIFF(lh.ngayHetHan, CURDATE()) AS soNgayConLai
                    FROM chi_tiet_phieu_nhap_kho ct
                    JOIN mat_hang mh ON ct.maSKU = mh.maSKU
                    LEFT JOIN lo_hang lh ON ct.maLo = lh.maLo
                    WHERE ct.maPhieuNhap = :id
                    ORDER BY ct.id ASC";

        $iStmt = $this->db->prepare($itemSql);
        $iStmt->execute(['id' => $maPhieuNhap]);
        $req['items'] = $iStmt->fetchAll(PDO::FETCH_ASSOC);

        return $req;
    }

    /**
     * Lấy thông tin kho
     */
    public function getWarehouse(string $maKho): ?array
    {
        $sql = "SELECT maKho, tenKho, diaDiem, loaiKho, sucChuaToiDa, sucChuaDaSuDung, sucChuaDaHenNhap,
                       (sucChuaToiDa - sucChuaDaSuDung - sucChuaDaHenNhap) AS sucChuaKhaDung,
                       trangThai
                FROM kho
                WHERE maKho = :maKho
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['maKho' => $maKho]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Danh sách tất cả các kho
     */
    public function getWarehouses(): array
    {
        $sql = "SELECT maKho, tenKho, diaDiem, loaiKho, sucChuaToiDa, sucChuaDaSuDung, sucChuaDaHenNhap,
                       (sucChuaToiDa - sucChuaDaSuDung - sucChuaDaHenNhap) AS sucChuaKhaDung,
                       trangThai
                FROM kho
                ORDER BY maKho ASC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách vị trí lưu kho khả dụng trong kho (trạng thái HOAT_DONG, còn sức chứa)
     */
    public function getAvailablePositionsForWarehouse(string $maKho, ?string $loaiHang = null): array
    {
        $sql = "SELECT vt.maViTri, vt.maKho, vt.maKe, vt.tang, vt.oChua, vt.loaiHangLuuTru,
                       vt.sucChuaToiDa, vt.sucChuaDaSuDung,
                       COALESCE(SUM(ctldp.soLuongDieuPhoi), 0) AS sucChuaDaHenNhapViTri,
                       (vt.sucChuaToiDa - vt.sucChuaDaSuDung - COALESCE(SUM(ctldp.soLuongDieuPhoi), 0)) AS sucChuaKhaDung,
                       vt.trangThai
                FROM vi_tri_luu_kho vt
                LEFT JOIN chi_tiet_lenh_dieu_phoi ctldp ON vt.maViTri = ctldp.maViTri AND ctldp.trangThai = 'CHO_NHAP'
                WHERE vt.maKho = :maKho
                  AND vt.trangThai = 'HOAT_DONG'";

        $params = ['maKho' => $maKho];

        if (!empty($loaiHang)) {
            if ($loaiHang === 'THANH_PHAM' || $loaiHang === 'TP') {
                $sql .= " AND (vt.loaiHangLuuTru IN ('TP', 'THANH_PHAM', 'CHUNG') OR vt.loaiHangLuuTru IS NULL)";
            } elseif ($loaiHang === 'NGUYEN_VAT_LIEU' || $loaiHang === 'NVL') {
                $sql .= " AND (vt.loaiHangLuuTru IN ('NVL', 'NGUYEN_VAT_LIEU', 'CHUNG') OR vt.loaiHangLuuTru IS NULL)";
            } else {
                $sql .= " AND (vt.loaiHangLuuTru = :loaiHang OR vt.loaiHangLuuTru = 'CHUNG' OR vt.loaiHangLuuTru IS NULL)";
                $params['loaiHang'] = $loaiHang;
            }
        }

        $sql .= " GROUP BY vt.maViTri, vt.maKho, vt.maKe, vt.tang, vt.oChua, vt.loaiHangLuuTru,
                           vt.sucChuaToiDa, vt.sucChuaDaSuDung, vt.trangThai
                  HAVING sucChuaKhaDung > 0
                  ORDER BY sucChuaKhaDung DESC, vt.maViTri ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Danh sách nhân viên kho để phân công thực hiện lệnh điều phối
     */
    public function getWorkersList(): array
    {
        $sql = "SELECT nv.maNhanVien, nv.hoTen, nv.chucDanh, tk.vaiTro
                FROM nhan_vien nv
                JOIN tai_khoan_nguoi_dung tk ON nv.maNhanVien = tk.maNhanVien
                WHERE tk.vaiTro = 'NVK' AND tk.trangThai = 'HOAT_DONG'
                ORDER BY nv.maNhanVien ASC";

        $stmt = $this->db->query($sql);
        $workers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Nếu không có NVK nào trong bảng tài khoản, fallback lấy nhân viên kho bất kỳ
        if (empty($workers)) {
            $sql2 = "SELECT maNhanVien, hoTen, chucDanh, 'NVK' AS vaiTro FROM nhan_vien ORDER BY maNhanVien ASC LIMIT 5";
            $workers = $this->db->query($sql2)->fetchAll(PDO::FETCH_ASSOC);
        }

        return $workers;
    }

    /**
     * Sinh mã lệnh điều phối: LDP-YYMMDD-NNNN
     */
    public function generateMaLenhDieuPhoi(): string
    {
        $prefix = 'LDP-' . date('ymd') . '-';
        $sql = "SELECT maLenhDieuPhoi FROM lenh_dieu_phoi_kho WHERE maLenhDieuPhoi LIKE :prefix ORDER BY maLenhDieuPhoi DESC LIMIT 1";
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
     * Tạo lệnh điều phối nhập kho
     */
    public function createDispatch(
        string $maPhieuNhap,
        string $maKho,
        array $allocLines,
        string $maNhanVienPhatLenh,
        string $maNhanVienThucHien,
        string $ghiChu = '',
        ?string $maTaiKhoan = null
    ): string {
        $wasInTransaction = $this->db->inTransaction();
        if (!$wasInTransaction) {
            $this->db->beginTransaction();
        }

        try {
            $maLDP = $this->generateMaLenhDieuPhoi();

            // 1. Tạo lệnh điều phối kho
            $ldpSql = "INSERT INTO lenh_dieu_phoi_kho 
                       (maLenhDieuPhoi, maPhieuNhap, maKho, ngayPhatLenh, maNhanVienPhatLenh, maNhanVienThucHien, trangThai, ghiChu)
                       VALUES (:maLDP, :maPhieuNhap, :maKho, NOW(), :maPhat, :maThucHien, 'CHO_THUC_HIEN', :ghiChu)";
            $ldpStmt = $this->db->prepare($ldpSql);
            $ldpStmt->execute([
                'maLDP'        => $maLDP,
                'maPhieuNhap'  => $maPhieuNhap,
                'maKho'        => $maKho,
                'maPhat'       => $maNhanVienPhatLenh,
                'maThucHien'   => $maNhanVienThucHien,
                'ghiChu'       => $ghiChu,
            ]);

            // 2. Thêm từng dòng chi tiết phân bổ vị trí
            $detailSql = "INSERT INTO chi_tiet_lenh_dieu_phoi 
                          (maLenhDieuPhoi, maLo, maSKU, maViTri, soLuongDieuPhoi, soLuongThucHien, trangThai)
                          VALUES (:maLDP, :maLo, :maSKU, :maViTri, :soLuong, 0, 'CHO_NHAP')";
            $detailStmt = $this->db->prepare($detailSql);

            $tongTheTichAlloc = 0.0;

            foreach ($allocLines as $line) {
                $qty = (float)($line['soLuongDieuPhoi'] ?? 0);
                if ($qty <= 0) continue;

                $detailStmt->execute([
                    'maLDP'    => $maLDP,
                    'maLo'     => $line['maLo'],
                    'maSKU'    => $line['maSKU'],
                    'maViTri'  => $line['maViTri'],
                    'soLuong'  => $qty,
                ]);

                // Tính thể tích (nếu có theTichDonVi, mặc định 1 đơn vị = 1 m3 nếu không có)
                $tongTheTichAlloc += $qty;
            }

            // 3. Cập nhật sucChuaDaHenNhap của kho
            $updateWhSql = "UPDATE kho SET sucChuaDaHenNhap = sucChuaDaHenNhap + :vol WHERE maKho = :maKho";
            $this->db->prepare($updateWhSql)->execute([
                'vol'   => $tongTheTichAlloc,
                'maKho' => $maKho,
            ]);

            // 4. Cập nhật trạng thái phiếu nhập kho sang DA_DIEU_PHOI
            $updatePnSql = "UPDATE phieu_nhap_kho SET trangThai = 'DA_DIEU_PHOI' WHERE maPhieuNhap = :maPhieu";
            $this->db->prepare($updatePnSql)->execute(['maPhieu' => $maPhieuNhap]);

            // 5. Ghi nhật ký hệ thống
            $this->logActivity(
                $maTaiKhoan,
                'DIEU_PHOI_NHAP_KHO',
                "Phát lệnh điều phối {$maLDP} cho phiếu nhập {$maPhieuNhap}, kho {$maKho}",
                null,
                ['maLenhDieuPhoi' => $maLDP, 'maPhieuNhap' => $maPhieuNhap, 'allocLines' => $allocLines]
            );

            if (!$wasInTransaction) {
                $this->db->commit();
            }

            return $maLDP;

        } catch (\Throwable $e) {
            if (!$wasInTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Lấy header lệnh điều phối
     */
    public function getDispatchHeader(string $maLenhDieuPhoi): ?array
    {
        $sql = "SELECT ldp.maLenhDieuPhoi, ldp.maPhieuNhap, ldp.maKho, k.tenKho,
                       ldp.ngayPhatLenh, ldp.ngayHoanThanh,
                       ldp.maNhanVienPhatLenh, nvP.hoTen AS tenNhanVienPhatLenh,
                       ldp.maNhanVienThucHien, nvT.hoTen AS tenNhanVienThucHien,
                       ldp.trangThai, ldp.ghiChu
                FROM lenh_dieu_phoi_kho ldp
                JOIN kho k ON ldp.maKho = k.maKho
                LEFT JOIN nhan_vien nvP ON ldp.maNhanVienPhatLenh = nvP.maNhanVien
                LEFT JOIN nhan_vien nvT ON ldp.maNhanVienThucHien = nvT.maNhanVien
                WHERE ldp.maLenhDieuPhoi = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $maLenhDieuPhoi]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Chi tiết lệnh điều phối kèm danh sách vị trí phân bổ
     */
    public function getDispatchDetail(string $maLenhDieuPhoi): ?array
    {
        $ldp = $this->getDispatchHeader($maLenhDieuPhoi);
        if (!$ldp) return null;

        $sql = "SELECT ct.id, ct.maLo, ct.maSKU, mh.tenHang, mh.donViTinh,
                       ct.maViTri, vt.maKe, vt.tang, vt.oChua,
                       CONCAT(ct.maViTri, ' (Kệ ', COALESCE(vt.maKe, '-'), ')') AS tenViTri,
                       ct.soLuongDieuPhoi, ct.soLuongThucHien, ct.trangThai,
                       lh.ngayHetHan
                FROM chi_tiet_lenh_dieu_phoi ct
                JOIN mat_hang mh ON ct.maSKU = mh.maSKU
                JOIN vi_tri_luu_kho vt ON ct.maViTri = vt.maViTri
                LEFT JOIN lo_hang lh ON ct.maLo = lh.maLo
                WHERE ct.maLenhDieuPhoi = :id
                ORDER BY ct.id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $maLenhDieuPhoi]);
        $ldp['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $tongSL = 0.0;
        foreach ($ldp['items'] as $it) {
            $tongSL += (float)$it['soLuongDieuPhoi'];
        }
        $ldp['tongSoLuongDieuPhoi'] = $tongSL;
        $ldp['soViTri'] = count($ldp['items']);

        return $ldp;
    }

    /**
     * Danh sách lệnh điều phối được giao cho nhân viên kho (NVK)
     */
    public function getAssignedOrdersForWorker(?string $maNhanVien = null, array $filters = []): array
    {
        $sql = "SELECT ldp.maLenhDieuPhoi, ldp.maPhieuNhap, ldp.maKho, k.tenKho,
                       ldp.ngayPhatLenh, ldp.ngayHoanThanh,
                       ldp.maNhanVienThucHien, nvT.hoTen AS tenNhanVienThucHien,
                       ldp.trangThai, ldp.ghiChu,
                       COUNT(ct.id) AS soViTri,
                       SUM(ct.soLuongDieuPhoi) AS tongSoLuong
                FROM lenh_dieu_phoi_kho ldp
                JOIN kho k ON ldp.maKho = k.maKho
                LEFT JOIN nhan_vien nvT ON ldp.maNhanVienThucHien = nvT.maNhanVien
                LEFT JOIN chi_tiet_lenh_dieu_phoi ct ON ldp.maLenhDieuPhoi = ct.maLenhDieuPhoi
                WHERE 1=1";

        $params = [];
        if (!empty($maNhanVien)) {
            $sql .= " AND ldp.maNhanVienThucHien = :maNV";
            $params['maNV'] = $maNhanVien;
        }

        if (!empty($filters['trangThai'])) {
            $sql .= " AND ldp.trangThai = :trangThai";
            $params['trangThai'] = $filters['trangThai'];
        }

        $sql .= " GROUP BY ldp.maLenhDieuPhoi, ldp.maPhieuNhap, ldp.maKho, k.tenKho,
                           ldp.ngayPhatLenh, ldp.ngayHoanThanh,
                           ldp.maNhanVienThucHien, nvT.hoTen,
                           ldp.trangThai, ldp.ghiChu
                  ORDER BY ldp.ngayPhatLenh DESC, ldp.maLenhDieuPhoi DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * NVK xác nhận "Đã nhận lệnh"
     */
    public function confirmReceiptByWorker(string $maLenhDieuPhoi, string $maNhanVien, ?string $maTaiKhoan = null): bool
    {
        $sql = "UPDATE lenh_dieu_phoi_kho 
                SET trangThai = 'DANG_THUC_HIEN' 
                WHERE maLenhDieuPhoi = :id 
                  AND trangThai = 'CHO_THUC_HIEN'";

        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute(['id' => $maLenhDieuPhoi]);

        if ($ok && $stmt->rowCount() > 0) {
            $this->logActivity(
                $maTaiKhoan,
                'XAC_NHAN_LENH_DIEU_PHOI',
                "Nhân viên {$maNhanVien} đã xác nhận nhận lệnh điều phối {$maLenhDieuPhoi}",
                ['trangThai' => 'CHO_THUC_HIEN'],
                ['trangThai' => 'DANG_THUC_HIEN']
            );
            return true;
        }

        return false;
    }

    /**
     * Ghi nhật ký hệ thống
     */
    private function logActivity(?string $maTaiKhoan, string $hanhDong, string $moTa, ?array $cu = null, ?array $moi = null): void
    {
        try {
            $sql = "INSERT INTO nhat_ky_he_thong 
                    (maTaiKhoan, diaChiIP, thoiGian, chucNang, hanhDong, giaTriCu, giaTriMoi)
                    VALUES (:tk, :ip, NOW(), 'dieu-phoi', :hd, :cu, :moi)";
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
