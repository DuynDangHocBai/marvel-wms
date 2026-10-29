<?php

namespace App\Repositories;

use App\Core\Database;
use App\Services\NhatKyService;
use PDO;
use Exception;

/**
 * Repository quản lý Kế hoạch sản xuất & Lệnh sản xuất bánh kẹo (Prompt 4 - LKH).
 */
class LenhSanXuatRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Lấy danh sách kế hoạch sản xuất đã được BGD duyệt (BR-01) còn số lượng chưa giao lệnh.
     */
    public function getApprovedPlans(): array
    {
        $sql = "SELECT kh.maKeHoach, kh.ngayLap, kh.ngayBatDau, kh.ngayHoanThanh, kh.maXuong,
                       xsx.tenXuong, kh.ghiChu,
                       SUM(ct.soLuongSanXuat) AS tongSanXuat,
                       SUM(ct.soLuongDaGiaoLenh) AS tongDaGiao,
                       SUM(ct.soLuongSanXuat - ct.soLuongDaGiaoLenh) AS tongConLai
                FROM ke_hoach_san_xuat kh
                JOIN chi_tiet_ke_hoach_san_xuat ct ON kh.maKeHoach = ct.maKeHoach
                LEFT JOIN xuong_san_xuat xsx ON kh.maXuong = xsx.maXuong
                WHERE kh.trangThai = 'DA_DUYET'
                GROUP BY kh.maKeHoach, kh.ngayLap, kh.ngayBatDau, kh.ngayHoanThanh, kh.maXuong, xsx.tenXuong, kh.ghiChu
                HAVING tongConLai > 0
                ORDER BY kh.ngayLap DESC";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy chi tiết kế hoạch sản xuất kèm từng dòng thành phẩm và số còn lại.
     */
    public function getPlanDetail(string $maKeHoach): ?array
    {
        $sql = "SELECT kh.maKeHoach, kh.ngayLap, kh.ngayBatDau, kh.ngayHoanThanh, kh.maXuong,
                       xsx.tenXuong, kh.trangThai, kh.ghiChu
                FROM ke_hoach_san_xuat kh
                LEFT JOIN xuong_san_xuat xsx ON kh.maXuong = xsx.maXuong
                WHERE kh.maKeHoach = :maKeHoach
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['maKeHoach' => $maKeHoach]);
        $plan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$plan) {
            return null;
        }

        $detailSql = "SELECT ct.id, ct.maKeHoach, ct.maSKU, m.tenHang, m.donViTinh,
                             ct.soLuongSanXuat, ct.soLuongDaGiaoLenh,
                             (ct.soLuongSanXuat - ct.soLuongDaGiaoLenh) AS soLuongConLai
                      FROM chi_tiet_ke_hoach_san_xuat ct
                      JOIN mat_hang m ON ct.maSKU = m.maSKU
                      WHERE ct.maKeHoach = :maKeHoach
                      ORDER BY ct.id ASC";

        $dStmt = $this->db->prepare($detailSql);
        $dStmt->execute(['maKeHoach' => $maKeHoach]);
        $details = $dStmt->fetchAll(PDO::FETCH_ASSOC);
        $plan['chiTiet'] = $details;
        $plan['items'] = $details;

        return $plan;
    }

    /**
     * Danh sách các xưởng sản xuất đang hoạt động.
     */
    public function getXuongList(): array
    {
        $sql = "SELECT maXuong, tenXuong, viTri, congSuat FROM xuong_san_xuat WHERE trangThai='HOAT_DONG' ORDER BY maXuong ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy định mức NVL (BOM) của một thành phẩm.
     */
    public function getBOM(string $maThanhPham): array
    {
        $sql = "SELECT dm.maThanhPham, dm.maNVL, m.tenHang AS tenNVL, m.donViTinh, dm.soLuongDinhMuc
                FROM dinh_muc_nvl dm
                JOIN mat_hang m ON dm.maNVL = m.maSKU
                WHERE dm.maThanhPham = :maThanhPham AND dm.trangThai = 'HOAT_DONG'
                ORDER BY dm.maNVL ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['maThanhPham' => $maThanhPham]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy tồn kho khả dụng hiện tại của một nguyên vật liệu từ View (BR-06).
     */
    public function getTonKhaDung(string $maNVL): float
    {
        $sql = "SELECT tonKhaDung FROM vw_ton_kho_kha_dung WHERE maSKU = :maSKU LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['maSKU' => $maNVL]);
        $val = $stmt->fetchColumn();
        return ($val !== false) ? (float)$val : 0.0;
    }

    /**
     * Danh sách tất cả lệnh sản xuất kèm thanh tiến độ phân bổ %.
     */
    public function getAllOrders(array $filters = []): array
    {
        $sql = "SELECT lsx.maLenhSX, lsx.maKeHoach, lsx.maXuong, xsx.tenXuong,
                       lsx.maNhanVienLap, nv.hoTen AS tenNhanVienLap,
                       lsx.ngayLap, lsx.ngayBatDau, lsx.ngayHoanThanh, lsx.trangThai, lsx.ghiChu,
                       COALESCE(SUM(ct.soLuongGiao), 0) AS tongSoLuongGiao,
                       COALESCE(nvl_sum.tongCanCap, 0) AS tongNVLCanCap,
                       COALESCE(nvl_sum.tongDaPhanBo, 0) AS tongNVLDaPhanBo
                FROM lenh_san_xuat lsx
                LEFT JOIN xuong_san_xuat xsx ON lsx.maXuong = xsx.maXuong
                LEFT JOIN nhan_vien nv ON lsx.maNhanVienLap = nv.maNhanVien
                LEFT JOIN chi_tiet_lenh_san_xuat ct ON lsx.maLenhSX = ct.maLenhSX
                LEFT JOIN (
                    SELECT maLenhSX,
                           SUM(soLuongCanCap) AS tongCanCap,
                           SUM(soLuongDaPhanBo) AS tongDaPhanBo
                    FROM nvl_can_cap_lenh_sx
                    GROUP BY maLenhSX
                ) nvl_sum ON lsx.maLenhSX = nvl_sum.maLenhSX
                WHERE 1=1";

        $params = [];

        if (!empty($filters['keyword'])) {
            $sql .= " AND (lsx.maLenhSX LIKE :kw OR lsx.maKeHoach LIKE :kw2 OR xsx.tenXuong LIKE :kw3)";
            $kw = '%' . trim($filters['keyword']) . '%';
            $params['kw'] = $kw;
            $params['kw2'] = $kw;
            $params['kw3'] = $kw;
        }

        if (!empty($filters['trangThai'])) {
            $sql .= " AND lsx.trangThai = :trangThai";
            $params['trangThai'] = $filters['trangThai'];
        }

        if (!empty($filters['maXuong'])) {
            $sql .= " AND lsx.maXuong = :maXuong";
            $params['maXuong'] = $filters['maXuong'];
        }

        $sql .= " GROUP BY lsx.maLenhSX, lsx.maKeHoach, lsx.maXuong, xsx.tenXuong,
                           lsx.maNhanVienLap, nv.hoTen, lsx.ngayLap, lsx.ngayBatDau,
                           lsx.ngayHoanThanh, lsx.trangThai, lsx.ghiChu, nvl_sum.tongCanCap, nvl_sum.tongDaPhanBo
                  ORDER BY lsx.ngayLap DESC, lsx.maLenhSX DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $canCap = (float)$row['tongNVLCanCap'];
            $daPB = (float)$row['tongNVLDaPhanBo'];
            $row['phanTramTienDo'] = ($canCap > 0) ? round(($daPB / $canCap) * 100, 1) : 0.0;
        }

        return $rows;
    }

    /**
     * Chi tiết lệnh sản xuất kèm các thành phẩm và NVL cần cấp.
     */
    public function getOrderDetail(string $maLenhSX): ?array
    {
        $sql = "SELECT lsx.maLenhSX, lsx.maKeHoach, lsx.maXuong, xsx.tenXuong,
                       lsx.maNhanVienLap, nv.hoTen AS tenNhanVienLap,
                       lsx.ngayLap, lsx.ngayBatDau, lsx.ngayHoanThanh, lsx.trangThai, lsx.ghiChu
                FROM lenh_san_xuat lsx
                LEFT JOIN xuong_san_xuat xsx ON lsx.maXuong = xsx.maXuong
                LEFT JOIN nhan_vien nv ON lsx.maNhanVienLap = nv.maNhanVien
                WHERE lsx.maLenhSX = :maLenhSX
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['maLenhSX' => $maLenhSX]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            return null;
        }

        // 1. Chi tiết thành phẩm giao sản xuất
        $itemSql = "SELECT ct.id, ct.maSKU, m.tenHang, m.donViTinh, ct.soLuongGiao
                    FROM chi_tiet_lenh_san_xuat ct
                    JOIN mat_hang m ON ct.maSKU = m.maSKU
                    WHERE ct.maLenhSX = :maLenhSX
                    ORDER BY ct.id ASC";
        $iStmt = $this->db->prepare($itemSql);
        $iStmt->execute(['maLenhSX' => $maLenhSX]);
        $order['items'] = $iStmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Danh sách NVL cần cấp và tiến độ phân bổ
        $nvlSql = "SELECT nvl.id, nvl.maSKU, m.tenHang, m.donViTinh,
                          nvl.soLuongCanCap, nvl.soLuongDaPhanBo,
                          (nvl.soLuongCanCap - nvl.soLuongDaPhanBo) AS soLuongConLai,
                          nvl.trangThai
                   FROM nvl_can_cap_lenh_sx nvl
                   JOIN mat_hang m ON nvl.maSKU = m.maSKU
                   WHERE nvl.maLenhSX = :maLenhSX
                   ORDER BY nvl.id ASC";
        $nStmt = $this->db->prepare($nvlSql);
        $nStmt->execute(['maLenhSX' => $maLenhSX]);
        $order['nvlList'] = $nStmt->fetchAll(PDO::FETCH_ASSOC);

        // Tính tổng hợp KPI
        $tongCanCap = 0.0;
        $tongDaPhanBo = 0.0;
        foreach ($order['nvlList'] as $n) {
            $tongCanCap += (float)$n['soLuongCanCap'];
            $tongDaPhanBo += (float)$n['soLuongDaPhanBo'];
        }
        $order['tongNVLCanCap'] = $tongCanCap;
        $order['tongNVLDaPhanBo'] = $tongDaPhanBo;
        $order['tongNVLConLai'] = max(0.0, $tongCanCap - $tongDaPhanBo);
        $order['phanTramTienDo'] = ($tongCanCap > 0) ? round(($tongDaPhanBo / $tongCanCap) * 100, 1) : 0.0;

        return $order;
    }

    /**
     * Sinh mã lệnh sản xuất tự động theo chuẩn LSX-YYMM-NNNN
     */
    public function generateMaLenh(): string
    {
        $prefix = 'LSX-' . date('ym') . '-';
        $sql = "SELECT maLenhSX FROM lenh_san_xuat WHERE maLenhSX LIKE :prefix ORDER BY maLenhSX DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['prefix' => $prefix . '%']);
        $lastCode = $stmt->fetchColumn();

        if ($lastCode) {
            $parts = explode('-', $lastCode);
            $seq = (int) end($parts);
            $nextSeq = $seq + 1;
        } else {
            // Kiểm tra mã dạng cũ LSX2610NNN
            $oldPrefix = 'LSX' . date('ym');
            $oStmt = $this->db->prepare("SELECT maLenhSX FROM lenh_san_xuat WHERE maLenhSX LIKE :oldPrefix ORDER BY maLenhSX DESC LIMIT 1");
            $oStmt->execute(['oldPrefix' => $oldPrefix . '%']);
            $oldLast = $oStmt->fetchColumn();
            if ($oldLast) {
                $numStr = substr($oldLast, strlen($oldPrefix));
                $nextSeq = ((int)$numStr) + 1;
            } else {
                $nextSeq = 1;
            }
        }

        return $prefix . str_pad((string)$nextSeq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Tạo và phát hành lệnh sản xuất mới (hoặc lưu bản nháp).
     * RÀNG BUỘC:
     * - Chỉ kế hoạch DA_DUYET mới được phát lệnh.
     * - Số lượng giao <= số lượng còn lại của kế hoạch.
     * - Khi phát lệnh (DA_PHAT):
     *   + Lưu chi_tiet_lenh_san_xuat
     *   + Tính BOM và lưu nvl_can_cap_lenh_sx (trạng thái CHO_PHAN_BO)
     *   + Cộng soLuongDaGiaoLenh vào chi_tiet_ke_hoach_san_xuat
     *   + Giữ chỗ BR-06 được kích hoạt tự động qua View vw_ton_kho_kha_dung
     *   + Tuyệt đối KHÔNG sửa ton_kho
     */
    public function createOrder(array $orderData, string $status = 'DA_PHAT', string $maNhanVien = 'NV001', ?string $maTaiKhoan = null): string
    {
        $hasOuterTx = $this->db->inTransaction();
        if (!$hasOuterTx) {
            $this->db->beginTransaction();
        }

        try {
            $maKeHoach = $orderData['maKeHoach'];
            $items = $orderData['items'] ?? [];

            if (empty($items)) {
                throw new Exception("Lệnh sản xuất phải có ít nhất một mặt hàng thành phẩm.");
            }

            // 1. Khóa và kiểm tra kế hoạch sản xuất
            $planStmt = $this->db->prepare("SELECT * FROM ke_hoach_san_xuat WHERE maKeHoach = :maKeHoach FOR UPDATE");
            $planStmt->execute(['maKeHoach' => $maKeHoach]);
            $plan = $planStmt->fetch(PDO::FETCH_ASSOC);

            if (!$plan || $plan['trangThai'] !== 'DA_DUYET') {
                throw new Exception("Kế hoạch sản xuất {$maKeHoach} không ở trạng thái ĐÃ DUYỆT (BR-01).");
            }

            // 2. Khóa chi tiết kế hoạch sản xuất để kiểm tra hạn mức còn lại
            $skuList = array_column($items, 'maSKU');
            $pPlaceholders = implode(',', array_fill(0, count($skuList), '?'));
            $lockSql = "SELECT maSKU, soLuongSanXuat, soLuongDaGiaoLenh,
                               (soLuongSanXuat - soLuongDaGiaoLenh) AS soLuongConLai
                        FROM chi_tiet_ke_hoach_san_xuat
                        WHERE maKeHoach = ? AND maSKU IN ({$pPlaceholders})
                        FOR UPDATE";
            $lockStmt = $this->db->prepare($lockSql);
            $lockStmt->execute(array_merge([$maKeHoach], $skuList));
            $planItemRows = $lockStmt->fetchAll(PDO::FETCH_ASSOC);

            $conLaiMap = [];
            foreach ($planItemRows as $r) {
                $conLaiMap[$r['maSKU']] = (float)$r['soLuongConLai'];
            }

            foreach ($items as $it) {
                $sku = $it['maSKU'];
                $qty = (float)$it['soLuongGiao'];
                $maxAvail = $conLaiMap[$sku] ?? 0.0;

                if ($qty <= 0) {
                    throw new Exception("Số lượng giao cho {$sku} phải lớn hơn 0.");
                }

                if ($qty > $maxAvail) {
                    throw new Exception("Số lượng nhập vượt quá hạn mức còn lại của kế hoạch sản xuất (Tối đa: {$maxAvail})");
                }
            }

            // 3. Sinh mã lệnh
            $maLenhSX = $this->generateMaLenh();

            // 4. Thêm bản ghi lenh_san_xuat
            $insertHeaderSql = "INSERT INTO lenh_san_xuat
                                (maLenhSX, maKeHoach, maXuong, maNhanVienLap, ngayLap, ngayBatDau, ngayHoanThanh, trangThai, ghiChu)
                                VALUES
                                (:maLenhSX, :maKeHoach, :maXuong, :maNhanVienLap, NOW(), :ngayBatDau, :ngayHoanThanh, :trangThai, :ghiChu)";
            $stmtHeader = $this->db->prepare($insertHeaderSql);
            $stmtHeader->execute([
                'maLenhSX'       => $maLenhSX,
                'maKeHoach'      => $maKeHoach,
                'maXuong'        => $orderData['maXuong'],
                'maNhanVienLap'  => $maNhanVien,
                'ngayBatDau'     => $orderData['ngayBatDau'],
                'ngayHoanThanh'  => $orderData['ngayHoanThanh'],
                'trangThai'      => $status,
                'ghiChu'         => $orderData['ghiChu'] ?? '',
            ]);

            // 5. Thêm chi tiết thành phẩm giao lệnh
            $insertDetailSql = "INSERT INTO chi_tiet_lenh_san_xuat (maLenhSX, maSKU, soLuongGiao)
                                VALUES (:maLenhSX, :maSKU, :soLuongGiao)";
            $stmtDetail = $this->db->prepare($insertDetailSql);

            // Bảng cập nhật số lượng đã giao trong kế hoạch
            $updatePlanDetailSql = "UPDATE chi_tiet_ke_hoach_san_xuat
                                    SET soLuongDaGiaoLenh = soLuongDaGiaoLenh + :qty
                                    WHERE maKeHoach = :maKeHoach AND maSKU = :maSKU";
            $stmtUpdatePlan = $this->db->prepare($updatePlanDetailSql);

            $bomTotal = []; // Tổng hợp NVL cần cấp

            foreach ($items as $it) {
                $stmtDetail->execute([
                    'maLenhSX'    => $maLenhSX,
                    'maSKU'       => $it['maSKU'],
                    'soLuongGiao' => $it['soLuongGiao'],
                ]);

                // Nếu là phát lệnh chính thức (DA_PHAT) thì cộng số đã giao vào kế hoạch
                if ($status === 'DA_PHAT') {
                    $stmtUpdatePlan->execute([
                        'qty'       => $it['soLuongGiao'],
                        'maKeHoach' => $maKeHoach,
                        'maSKU'     => $it['maSKU'],
                    ]);

                    // Tính BOM NVL cần cấp
                    $boms = $this->getBOM($it['maSKU']);
                    foreach ($boms as $b) {
                        $nvlSku = $b['maNVL'];
                        $need = (float)$it['soLuongGiao'] * (float)$b['soLuongDinhMuc'];
                        if (!isset($bomTotal[$nvlSku])) {
                            $bomTotal[$nvlSku] = 0.0;
                        }
                        $bomTotal[$nvlSku] += $need;
                    }
                }
            }

            // 6. Nếu phát lệnh (DA_PHAT), thêm các dòng NVL cần cấp để kích hoạt giữ chỗ BR-06
            if ($status === 'DA_PHAT' && !empty($bomTotal)) {
                $insertNVLSql = "INSERT INTO nvl_can_cap_lenh_sx (maLenhSX, maSKU, soLuongCanCap, soLuongDaPhanBo, trangThai)
                                 VALUES (:maLenhSX, :maSKU, :soLuongCanCap, 0.000, 'CHO_PHAN_BO')";
                $stmtNVL = $this->db->prepare($insertNVLSql);

                foreach ($bomTotal as $nvlSku => $needQty) {
                    $stmtNVL->execute([
                        'maLenhSX'       => $maLenhSX,
                        'maSKU'          => $nvlSku,
                        'soLuongCanCap'  => $needQty,
                    ]);
                }
            }

            // 7. Ghi nhật ký hệ thống
            NhatKyService::ghi(
                $maTaiKhoan,
                'lenh-san-xuat',
                ($status === 'DA_PHAT' ? 'PHAT_LENH' : 'LUU_BAN_NHAP'),
                "Tạo lệnh sản xuất {$maLenhSX} cho kế hoạch {$maKeHoach} trạng thái {$status}.",
                null,
                $orderData
            );

            if (!$hasOuterTx) {
                $this->db->commit();
            }

            return $maLenhSX;
        } catch (\Throwable $e) {
            if (!$hasOuterTx && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}

