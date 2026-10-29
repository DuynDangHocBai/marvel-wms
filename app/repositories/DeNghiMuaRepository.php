<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * Repository: Lập và quản lý đề nghị mua nguyên vật liệu (Prompt 3 - LKH)
 */
class DeNghiMuaRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Danh sách kế hoạch sản xuất đã duyệt (DA_DUYET)
     */
    public function getApprovedPlans(): array
    {
        $sql = "SELECT kh.maKeHoach, kh.maXuong, xsx.tenXuong,
                       kh.ngayLap, kh.ngayBatDau, kh.ngayHoanThanh, kh.trangThai, kh.ghiChu
                FROM ke_hoach_san_xuat kh
                LEFT JOIN xuong_san_xuat xsx ON kh.maXuong = xsx.maXuong
                WHERE kh.trangThai = 'DA_DUYET'
                ORDER BY kh.ngayLap DESC, kh.maKeHoach DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Chi tiết một kế hoạch sản xuất
     */
    public function getPlanHeader(string $maKeHoach): ?array
    {
        $sql = "SELECT kh.maKeHoach, kh.maXuong, xsx.tenXuong,
                       kh.ngayLap, kh.ngayBatDau, kh.ngayHoanThanh, kh.trangThai, kh.ghiChu
                FROM ke_hoach_san_xuat kh
                LEFT JOIN xuong_san_xuat xsx ON kh.maXuong = xsx.maXuong
                WHERE kh.maKeHoach = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $maKeHoach]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Tính toán nhu cầu NVL theo BOM đối chiếu vw_ton_kho_kha_dung và vw_hang_dang_ve
     */
    public function getNVLDemandForPlan(string $maKeHoach): array
    {
        $sql = "SELECT bm.maNVL AS maSKU, mh.tenHang, mh.donViTinh, mh.giaNhap,
                       mh.tonToiThieu,
                       SUM(ct.soLuongSanXuat * bm.soLuongDinhMuc) AS nhuCauSX,
                       COALESCE(tk.tonKhaDung, 0.000) AS tonKhaDung,
                       COALESCE(hdv.hangDangVe, 0.000) AS hangDangVe,
                       GREATEST(0.000, SUM(ct.soLuongSanXuat * bm.soLuongDinhMuc) + mh.tonToiThieu - COALESCE(tk.tonKhaDung, 0.000) - COALESCE(hdv.hangDangVe, 0.000)) AS soLuongDeXuat
                FROM chi_tiet_ke_hoach_san_xuat ct
                JOIN dinh_muc_nvl bm ON ct.maSKU = bm.maThanhPham
                JOIN mat_hang mh ON bm.maNVL = mh.maSKU
                LEFT JOIN vw_ton_kho_kha_dung tk ON bm.maNVL = tk.maSKU
                LEFT JOIN vw_hang_dang_ve hdv ON bm.maNVL = hdv.maSKU
                WHERE ct.maKeHoach = :maKeHoach
                GROUP BY bm.maNVL, mh.tenHang, mh.donViTinh, mh.giaNhap, mh.tonToiThieu, tk.tonKhaDung, hdv.hangDangVe
                ORDER BY bm.maNVL ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['maKeHoach' => $maKeHoach]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Danh sách tất cả các đề nghị mua NVL
     */
    public function getAllProposals(array $filters = []): array
    {
        $sql = "SELECT dnm.maDeNghi, dnm.maKeHoach, dnm.ngayLap, dnm.ngayCanHang,
                       dnm.trangThai, dnm.tongTienDuKien, dnm.lyDo,
                       nv.hoTen AS tenNhanVienLap,
                       COUNT(ct.id) AS soMatHang,
                       SUM(ct.soLuongMua) AS tongSoLuongMua
                FROM de_nghi_mua_nvl dnm
                LEFT JOIN nhan_vien nv ON dnm.maNhanVienLap = nv.maNhanVien
                LEFT JOIN chi_tiet_de_nghi_mua_nvl ct ON dnm.maDeNghi = ct.maDeNghi
                WHERE 1=1";

        $params = [];
        if (!empty($filters['keyword'])) {
            $sql .= " AND (dnm.maDeNghi LIKE :kw OR dnm.maKeHoach LIKE :kw OR nv.hoTen LIKE :kw OR dnm.lyDo LIKE :kw)";
            $params['kw'] = '%' . trim($filters['keyword']) . '%';
        }
        if (!empty($filters['trangThai'])) {
            $sql .= " AND dnm.trangThai = :trangThai";
            $params['trangThai'] = $filters['trangThai'];
        }
        if (!empty($filters['maKeHoach'])) {
            $sql .= " AND dnm.maKeHoach = :maKeHoach";
            $params['maKeHoach'] = $filters['maKeHoach'];
        }

        $sql .= " GROUP BY dnm.maDeNghi, dnm.maKeHoach, dnm.ngayLap, dnm.ngayCanHang,
                           dnm.trangThai, dnm.tongTienDuKien, dnm.lyDo, nv.hoTen
                  ORDER BY dnm.ngayLap DESC, dnm.maDeNghi DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Chi tiết đề nghị mua kèm các dòng mặt hàng
     */
    public function getProposalDetail(string $maDeNghi): ?array
    {
        $sql = "SELECT dnm.maDeNghi, dnm.maNhanVienLap, nv.hoTen AS tenNhanVienLap,
                       dnm.maKeHoach, dnm.ngayLap, dnm.ngayCanHang,
                       dnm.trangThai, dnm.tongTienDuKien, dnm.lyDo
                FROM de_nghi_mua_nvl dnm
                LEFT JOIN nhan_vien nv ON dnm.maNhanVienLap = nv.maNhanVien
                WHERE dnm.maDeNghi = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $maDeNghi]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $itemSql = "SELECT ct.id, ct.maSKU, mh.tenHang, mh.donViTinh, mh.giaNhap,
                           ct.nhuCauSX, ct.tonKhaDung, ct.tonToiThieu, ct.hangDangVe,
                           ct.soLuongDeXuat, ct.soLuongMua, ct.ngoaiKeHoach,
                           (ct.soLuongMua * mh.giaNhap) AS thanhTienDuKien
                    FROM chi_tiet_de_nghi_mua_nvl ct
                    JOIN mat_hang mh ON ct.maSKU = mh.maSKU
                    WHERE ct.maDeNghi = :id
                    ORDER BY ct.id ASC";

        $iStmt = $this->db->prepare($itemSql);
        $iStmt->execute(['id' => $maDeNghi]);
        $row['items'] = $iStmt->fetchAll(PDO::FETCH_ASSOC);

        $tongSL = 0.0;
        foreach ($row['items'] as $it) {
            $tongSL += (float)$it['soLuongMua'];
        }
        $row['tongSoLuongMua'] = $tongSL;

        return $row;
    }

    /**
     * Danh sách tất cả nguyên vật liệu (dùng cho thêm ngoài kế hoạch)
     */
    public function getAllMaterials(): array
    {
        $sql = "SELECT mh.maSKU, mh.tenHang, mh.donViTinh, mh.giaNhap, mh.tonToiThieu,
                       COALESCE(tk.tonKhaDung, 0.000) AS tonKhaDung,
                       COALESCE(hdv.hangDangVe, 0.000) AS hangDangVe
                FROM mat_hang mh
                LEFT JOIN vw_ton_kho_kha_dung tk ON mh.maSKU = tk.maSKU
                LEFT JOIN vw_hang_dang_ve hdv ON mh.maSKU = hdv.maSKU
                WHERE mh.loaiHang = 'NGUYEN_VAT_LIEU' AND mh.trangThai = 'DANG_KINH_DOANH'
                ORDER BY mh.maSKU ASC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Sinh mã đề nghị: DNM-YYYYMMDD-NNNN
     */
    public function generateMaDeNghi(): string
    {
        $prefix = 'DNM-' . date('Ymd') . '-';
        $sql = "SELECT maDeNghi FROM de_nghi_mua_nvl WHERE maDeNghi LIKE :prefix ORDER BY maDeNghi DESC LIMIT 1";
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
     * Tạo đề nghị mua NVL trong transaction
     */
    public function createProposal(array $formData, string $maNhanVien, ?int $maTaiKhoan = null): string
    {
        $wasInTransaction = $this->db->inTransaction();
        if (!$wasInTransaction) {
            $this->db->beginTransaction();
        }

        try {
            $maDNM = $this->generateMaDeNghi();
            $maKeHoach = !empty($formData['maKeHoach']) ? $formData['maKeHoach'] : null;
            $ngayCanHang = !empty($formData['ngayCanHang']) ? $formData['ngayCanHang'] : date('Y-m-d', strtotime('+7 days'));
            $lyDo = $formData['lyDo'] ?? '';

            // Tính tổng tiền dự kiến
            $items = $formData['items'] ?? [];
            $tongTien = 0.0;

            // Truy vấn đơn giá nhập của các mặt hàng
            $skuList = array_column($items, 'maSKU');
            $priceMap = [];
            if (!empty($skuList)) {
                $placeholders = implode(',', array_fill(0, count($skuList), '?'));
                $pStmt = $this->db->prepare("SELECT maSKU, giaNhap FROM mat_hang WHERE maSKU IN ($placeholders)");
                $pStmt->execute($skuList);
                $priceMap = $pStmt->fetchAll(PDO::FETCH_KEY_PAIR);
            }

            foreach ($items as $it) {
                $qty = (float)($it['soLuongMua'] ?? 0);
                $sku = $it['maSKU'] ?? '';
                $price = (float)($priceMap[$sku] ?? 0);
                $tongTien += ($qty * $price);
            }

            // 1. Lưu de_nghi_mua_nvl
            $dnmSql = "INSERT INTO de_nghi_mua_nvl 
                       (maDeNghi, maNhanVienLap, maKeHoach, ngayLap, ngayCanHang, trangThai, tongTienDuKien, lyDo)
                       VALUES (:maDNM, :maNV, :maKH, NOW(), :ngayCanHang, 'CHO_DUYET', :tongTien, :lyDo)";
            $dnmStmt = $this->db->prepare($dnmSql);
            $dnmStmt->execute([
                'maDNM'       => $maDNM,
                'maNV'        => $maNhanVien,
                'maKH'        => $maKeHoach,
                'ngayCanHang' => $ngayCanHang,
                'tongTien'    => $tongTien,
                'lyDo'        => $lyDo,
            ]);

            // 2. Lưu chi_tiet_de_nghi_mua_nvl
            $ctSql = "INSERT INTO chi_tiet_de_nghi_mua_nvl 
                      (maDeNghi, maSKU, nhuCauSX, tonKhaDung, tonToiThieu, hangDangVe, soLuongDeXuat, soLuongMua, ngoaiKeHoach)
                      VALUES (:maDNM, :sku, :nhuCau, :tonKD, :tonTT, :hangDV, :deXuat, :mua, :ngoaiKH)";
            $ctStmt = $this->db->prepare($ctSql);

            foreach ($items as $it) {
                $qty = (float)($it['soLuongMua'] ?? 0);
                if ($qty <= 0) continue;

                $ctStmt->execute([
                    'maDNM'   => $maDNM,
                    'sku'     => $it['maSKU'],
                    'nhuCau'  => (float)($it['nhuCauSX'] ?? 0),
                    'tonKD'   => (float)($it['tonKhaDung'] ?? 0),
                    'tonTT'   => (float)($it['tonToiThieu'] ?? 0),
                    'hangDV'  => (float)($it['hangDangVe'] ?? 0),
                    'deXuat'  => (float)($it['soLuongDeXuat'] ?? $qty),
                    'mua'     => $qty,
                    'ngoaiKH' => !empty($it['ngoaiKeHoach']) ? 1 : 0,
                ]);
            }

            // 3. Ghi nhật ký hệ thống
            $this->logActivity(
                $maTaiKhoan,
                'LAP_DE_NGHI_MUA_NVL',
                "Lập đề nghị mua NVL {$maDNM} với tổng tiền dự kiến " . number_format($tongTien) . " đ",
                null,
                ['maDeNghi' => $maDNM, 'formData' => $formData, 'tongTienDuKien' => $tongTien]
            );

            if (!$wasInTransaction) {
                $this->db->commit();
            }

            return $maDNM;

        } catch (\Throwable $e) {
            if (!$wasInTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Hủy đề nghị mua khi trạng thái còn CHO_DUYET
     */
    public function cancelProposal(string $maDeNghi, string $maNhanVien, string $lyDoHuy = '', ?int $maTaiKhoan = null): bool
    {
        $sql = "UPDATE de_nghi_mua_nvl 
                SET trangThai = 'DA_HUY',
                    lyDo = CONCAT(COALESCE(lyDo, ''), ' [HỦY: ', :lyDoHuy, ']')
                WHERE maDeNghi = :id 
                  AND trangThai = 'CHO_DUYET'";

        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([
            'id'       => $maDeNghi,
            'lyDoHuy'  => $lyDoHuy ?: 'Người lập hủy đề nghị',
        ]);

        if ($ok && $stmt->rowCount() > 0) {
            $this->logActivity(
                $maTaiKhoan,
                'HUY_DE_NGHI_MUA',
                "Hủy đề nghị mua NVL {$maDeNghi}: {$lyDoHuy}",
                ['trangThai' => 'CHO_DUYET'],
                ['trangThai' => 'DA_HUY']
            );
            return true;
        }

        return false;
    }

    /**
     * Ghi nhật ký hệ thống
     */
    private function logActivity(?int $maTaiKhoan, string $hanhDong, string $moTa, ?array $cu = null, ?array $moi = null): void
    {
        try {
            $sql = "INSERT INTO nhat_ky_he_thong 
                    (maTaiKhoan, diaChiIP, thoiGian, chucNang, hanhDong, giaTriCu, giaTriMoi)
                    VALUES (:tk, :ip, NOW(), 'de-nghi-mua', :hd, :cu, :moi)";
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

