<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use PDO;

/**
 * Controller hiển thị Bảng điều khiển (Dashboard) theo 6 vai trò.
 */
class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();

        $user = Auth::user();
        $role = $user['vaiTro'] ?? 'NVK';

        // Lấy 4 thẻ KPI số liệu thực tế từ DB theo vai trò
        $kpiCards = $this->getKpiCardsByRole($role);

        // Lấy danh sách lối tắt tới các chức năng mà vai trò hiện tại được phép
        $shortcuts = $this->getShortcutsForRole();

        $this->render('dashboard/index', [
            'pageTitle'         => 'Bảng điều khiển WMS',
            'breadcrumbSection' => 'Kho vận',
            'user'              => $user,
            'role'              => $role,
            'kpiCards'          => $kpiCards,
            'shortcuts'         => $shortcuts,
        ]);
    }

    /**
     * Đếm số lượng an toàn từ bảng cơ sở dữ liệu. Nếu bảng chưa tồn tại trả về 0.
     */
    private function countTableSafely(string $table, string $where = '', array $params = []): int
    {
        try {
            $db = Database::getConnection();

            // Kiểm tra bảng có tồn tại trong database hiện tại không qua information_schema
            $checkStmt = $db->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :table");
            $checkStmt->execute(['table' => $table]);
            if (!$checkStmt->fetchColumn()) {
                return 0;
            }

            $sql = "SELECT COUNT(*) FROM `{$table}`";
            if (!empty($where)) {
                $sql .= " WHERE {$where}";
            }

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Trả về 4 thẻ KPI dựa trên các bảng thực tế trong domain model.
     */
    private function getKpiCardsByRole(string $role): array
    {
        return match ($role) {
            'QTV' => [
                [
                    'label' => 'Tổng số tài khoản',
                    'icon'  => 'bi-people',
                    'value' => $this->countTableSafely('tai_khoan_nguoi_dung'),
                    'unit'  => 'TK',
                    'desc'  => 'Tài khoản người dùng trong hệ thống',
                    'tint'  => 'tint-blue',
                ],
                [
                    'label' => 'Tài khoản hoạt động',
                    'icon'  => 'bi-person-check',
                    'value' => $this->countTableSafely('tai_khoan_nguoi_dung', "trangThai = 'HOAT_DONG'"),
                    'unit'  => 'TK',
                    'desc'  => 'Đang hoạt động bình thường',
                    'tint'  => 'tint-green',
                ],
                [
                    'label' => 'Tổng số nhân viên',
                    'icon'  => 'bi-person-badge',
                    'value' => $this->countTableSafely('nhan_vien'),
                    'unit'  => 'NV',
                    'desc'  => 'Đã phân bổ theo 5 bộ phận',
                    'tint'  => 'tint-purple',
                ],
                [
                    'label' => 'Nhật ký hệ thống',
                    'icon'  => 'bi-journal-text',
                    'value' => $this->countTableSafely('nhat_ky_he_thong'),
                    'unit'  => 'bản ghi',
                    'desc'  => 'Tổng lịch sử hoạt động ghi nhận',
                    'tint'  => 'tint-orange',
                ],
            ],

            'BGD' => [
                [
                    'label' => 'Đề nghị mua NVL',
                    'icon'  => 'bi-cart-check',
                    'value' => $this->countTableSafely('de_nghi_mua_nvl'),
                    'unit'  => 'chứng từ',
                    'desc'  => 'Đề nghị mua NVL từ bộ phận LKH',
                    'tint'  => 'tint-blue',
                ],
                [
                    'label' => 'Chênh lệch kiểm kê',
                    'icon'  => 'bi-clipboard-check',
                    'value' => $this->countTableSafely('bien_ban_kiem_ke'),
                    'unit'  => 'biên bản',
                    'desc'  => 'Hồ sơ kiểm kê kho (> 10 triệu BGD duyệt)',
                    'tint'  => 'tint-orange',
                ],
                [
                    'label' => 'Đề xuất hàng lỗi hỏng',
                    'icon'  => 'bi-exclamation-triangle',
                    'value' => $this->countTableSafely('de_xuat_xu_ly_hang_loi'),
                    'unit'  => 'phương án',
                    'desc'  => 'Phương án xử lý hàng hỏng cần duyệt',
                    'tint'  => 'tint-purple',
                ],
                [
                    'label' => 'Nhật ký hệ thống',
                    'icon'  => 'bi-journal-check',
                    'value' => $this->countTableSafely('nhat_ky_he_thong'),
                    'unit'  => 'hoạt động',
                    'desc'  => 'Ghi nhận giám sát điều hành',
                    'tint'  => 'tint-green',
                ],
            ],

            'LKH' => [
                [
                    'label' => 'Đề nghị mua NVL',
                    'icon'  => 'bi-cart-plus',
                    'value' => $this->countTableSafely('de_nghi_mua_nvl'),
                    'unit'  => 'phiếu',
                    'desc'  => 'Chứng từ đề nghị mua nguyên vật liệu',
                    'tint'  => 'tint-blue',
                ],
                [
                    'label' => 'Lệnh sản xuất',
                    'icon'  => 'bi-clipboard-data',
                    'value' => $this->countTableSafely('lenh_san_xuat'),
                    'unit'  => 'lệnh',
                    'desc'  => 'Lệnh sản xuất bánh kẹo theo BOM',
                    'tint'  => 'tint-green',
                ],
                [
                    'label' => 'Phiếu phân bổ tồn',
                    'icon'  => 'bi-pie-chart',
                    'value' => $this->countTableSafely('phieu_phan_bo'),
                    'unit'  => 'phiếu',
                    'desc'  => 'Phân bổ nguyên vật liệu theo định mức',
                    'tint'  => 'tint-purple',
                ],
                [
                    'label' => 'Mặt hàng theo dõi',
                    'icon'  => 'bi-box',
                    'value' => $this->countTableSafely('mat_hang'),
                    'unit'  => 'SKU',
                    'desc'  => 'Danh mục thành phẩm và nguyên vật liệu',
                    'tint'  => 'tint-orange',
                ],
            ],

            'QLK' => [
                [
                    'label' => 'Yêu cầu nhập kho',
                    'icon'  => 'bi-arrow-left-right',
                    'value' => $this->countTableSafely('yeu_cau_nhap_kho'),
                    'unit'  => 'yêu cầu',
                    'desc'  => 'Phiếu chờ điều phối vị trí lưu trữ',
                    'tint'  => 'tint-blue',
                ],
                [
                    'label' => 'Phân bổ số lượng',
                    'icon'  => 'bi-pie-chart',
                    'value' => $this->countTableSafely('phieu_phan_bo'),
                    'unit'  => 'phiếu',
                    'desc'  => 'Phân bổ tồn kho khả dụng',
                    'tint'  => 'tint-green',
                ],
                [
                    'label' => 'Chênh lệch kiểm kê',
                    'icon'  => 'bi-clipboard-check',
                    'value' => $this->countTableSafely('bien_ban_kiem_ke'),
                    'unit'  => 'biên bản',
                    'desc'  => 'Biên bản kiểm kê cần điều chỉnh',
                    'tint'  => 'tint-orange',
                ],
                [
                    'label' => 'Đề xuất hàng lỗi hỏng',
                    'icon'  => 'bi-exclamation-triangle',
                    'value' => $this->countTableSafely('de_xuat_xu_ly_hang_loi'),
                    'unit'  => 'đề xuất',
                    'desc'  => 'Xử lý hàng cận hạn hoặc hư hỏng',
                    'tint'  => 'tint-purple',
                ],
            ],

            'NVK' => [
                [
                    'label' => 'Lệnh điều phối được giao',
                    'icon'  => 'bi-card-checklist',
                    'value' => $this->countTableSafely('lenh_dieu_phoi'),
                    'unit'  => 'nhiệm vụ',
                    'desc'  => 'Nhiệm vụ bốc xếp, cất hàng vào ô kệ',
                    'tint'  => 'tint-blue',
                ],
                [
                    'label' => 'Phiếu nhập kho',
                    'icon'  => 'bi-box-arrow-in-down',
                    'value' => $this->countTableSafely('phieu_nhap_kho'),
                    'unit'  => 'phiếu',
                    'desc'  => 'Kiểm đếm thực tế hàng hóa',
                    'tint'  => 'tint-green',
                ],
                [
                    'label' => 'Phiếu xuất kho',
                    'icon'  => 'bi-box-arrow-up-right',
                    'value' => $this->countTableSafely('phieu_xuat_kho'),
                    'unit'  => 'phiếu',
                    'desc'  => 'Xuất kho phục vụ sản xuất/giao hàng',
                    'tint'  => 'tint-purple',
                ],
                [
                    'label' => 'Lô hàng trong kho',
                    'icon'  => 'bi-layers',
                    'value' => $this->countTableSafely('lo_hang'),
                    'unit'  => 'lô',
                    'desc'  => 'Lô hàng được quản lý theo FEFO',
                    'tint'  => 'tint-orange',
                ],
            ],

            'QC' => [
                [
                    'label' => 'Lô hàng chờ kiểm KCS',
                    'icon'  => 'bi-search',
                    'value' => $this->countTableSafely('lo_hang', "trangThai = 'CHO_KIEM'"),
                    'unit'  => 'lô',
                    'desc'  => 'Lô NVL mới nhập chờ đồng kiểm',
                    'tint'  => 'tint-blue',
                ],
                [
                    'label' => 'Biên bản kiểm tra',
                    'icon'  => 'bi-clipboard2-check',
                    'value' => $this->countTableSafely('bien_ban_kiem_tra'),
                    'unit'  => 'biên bản',
                    'desc'  => 'Biên bản kiểm định chất lượng',
                    'tint'  => 'tint-green',
                ],
                [
                    'label' => 'Hàng không đạt chuẩn',
                    'icon'  => 'bi-x-octagon',
                    'value' => $this->countTableSafely('lo_hang', "trangThai = 'KHONG_DAT'"),
                    'unit'  => 'lô',
                    'desc'  => 'Lô hàng bị từ chối nhập kho',
                    'tint'  => 'tint-orange',
                ],
                [
                    'label' => 'Đề xuất hàng lỗi hỏng',
                    'icon'  => 'bi-exclamation-triangle',
                    'value' => $this->countTableSafely('de_xuat_xu_ly_hang_loi'),
                    'unit'  => 'đề xuất',
                    'desc'  => 'Tham gia đánh giá chất lượng',
                    'tint'  => 'tint-purple',
                ],
            ],

            default => [
                ['label' => 'Dữ liệu 1', 'icon' => 'bi-circle', 'value' => 0, 'unit' => '—', 'desc' => 'Chưa có', 'tint' => 'tint-blue'],
                ['label' => 'Dữ liệu 2', 'icon' => 'bi-circle', 'value' => 0, 'unit' => '—', 'desc' => 'Chưa có', 'tint' => 'tint-green'],
                ['label' => 'Dữ liệu 3', 'icon' => 'bi-circle', 'value' => 0, 'unit' => '—', 'desc' => 'Chưa có', 'tint' => 'tint-purple'],
                ['label' => 'Dữ liệu 4', 'icon' => 'bi-circle', 'value' => 0, 'unit' => '—', 'desc' => 'Chưa có', 'tint' => 'tint-orange'],
            ],
        };
    }

    /**
     * Lấy danh sách chức năng mà vai trò hiện tại được phép truy cập.
     */
    private function getShortcutsForRole(): array
    {
        $perms = Auth::getPermissions();
        $modules = $perms['modules'] ?? [];

        $shortcuts = [];
        foreach ($modules as $key => $mod) {
            if (Auth::can($key, 'index')) {
                $shortcuts[] = [
                    'key'         => $key,
                    'name'        => $mod['name'],
                    'description' => $mod['description'] ?? '',
                    'icon'        => $mod['icon'] ?? 'bi-app',
                    'url'         => $mod['default_url'] ?? "?route={$key}/index",
                    'canWrite'    => Auth::canWrite($key),
                ];
            }
        }

        return $shortcuts;
    }
}
