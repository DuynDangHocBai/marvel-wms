<?php
/**
 * NGUỒN PHÂN QUYỀN DUY NHẤT (Single Source of Truth)
 * Quy định ma trận quyền XEM và GHI cho từng phân hệ/chức năng theo vai trò người dùng.
 * 6 Vai trò: QTV, BGD, LKH, QLK, NVK, QC
 */

return [
    // Định nghĩa các nhóm phân hệ cho Sidebar menu
    'groups' => [
        'KE_HOACH' => [
            'label' => 'KẾ HOẠCH',
            'icon'  => 'bi-calendar4-week',
        ],
        'KHO' => [
            'label' => 'KHO',
            'icon'  => 'bi-box-seam',
        ],
        'HE_THONG' => [
            'label' => 'HỆ THỐNG',
            'icon'  => 'bi-gear',
        ],
    ],

    // Danh sách hành động thuộc nhóm XEM và GHI
    'action_types' => [
        'view'  => ['index', 'detail', 'show'],
        'write' => ['create', 'store', 'update', 'cancel', 'delete', 'confirm', 'edit'],
    ],

    // Ma trận phân quyền theo từng module
    'modules' => [
        // 1. Lập đề nghị mua NVL
        'de-nghi-mua' => [
            'name'        => 'Lập đề nghị mua NVL',
            'description' => 'Tạo và theo dõi các đề nghị mua nguyên vật liệu phục vụ sản xuất',
            'group'       => 'KE_HOACH',
            'icon'        => 'bi-cart-plus',
            'default_url' => '?route=de-nghi-mua/index',
            'view'        => ['LKH', 'QLK', 'BGD'],
            'write'       => ['LKH'],
        ],

        // 2. Tạo lệnh sản xuất
        'lenh-san-xuat' => [
            'name'        => 'Tạo lệnh sản xuất',
            'description' => 'Lập kế hoạch và phát hành lệnh sản xuất bánh kẹo theo BOM',
            'group'       => 'KE_HOACH',
            'icon'        => 'bi-clipboard-data',
            'default_url' => '?route=lenh-san-xuat/index',
            'view'        => ['LKH', 'QLK', 'BGD'],
            'write'       => ['LKH'],
        ],

        // 3. Phân bổ số lượng
        'phan-bo' => [
            'name'        => 'Phân bổ số lượng',
            'description' => 'Phân bổ số lượng nguyên vật liệu và thành phẩm lưu kho',
            'group'       => 'KHO',
            'icon'        => 'bi-pie-chart',
            'default_url' => '?route=phan-bo/index',
            'view'        => ['QLK', 'LKH', 'BGD'],
            'write'       => ['QLK'],
        ],

        // 4. Điều phối nhập kho
        'dieu-phoi' => [
            'name'        => 'Điều phối nhập kho',
            'description' => 'Phân bổ kho, khu vực và vị trí lưu trữ cho hàng hóa đã kiểm tra đạt',
            'group'       => 'KHO',
            'icon'        => 'bi-arrow-left-right',
            'default_url' => '?route=dieu-phoi/index',
            'view'        => ['QLK', 'BGD', 'QC'],
            'write'       => ['QLK'],
        ],

        // 5. Cập nhật chênh lệch kiểm kê
        'kiem-ke' => [
            'name'        => 'Cập nhật chênh lệch kiểm kê',
            'description' => 'Ghi nhận và xử lý chênh lệch số lượng kiểm kê thực tế so với sổ sách',
            'group'       => 'KHO',
            'icon'        => 'bi-clipboard-check',
            'default_url' => '?route=kiem-ke/index',
            'view'        => ['QLK', 'BGD'],
            'write'       => ['QLK'],
        ],

        // 6. Đề xuất xử lý hàng lỗi hỏng
        'hang-loi' => [
            'name'        => 'Đề xuất xử lý hàng lỗi hỏng',
            'description' => 'Lập phương án xử lý hàng hỏng, hàng cận hạn và chờ phê duyệt',
            'group'       => 'KHO',
            'icon'        => 'bi-exclamation-triangle',
            'default_url' => '?route=hang-loi/index',
            'view'        => ['QLK', 'QC', 'BGD'],
            'write'       => ['QLK'],
        ],

        // 7. Lệnh điều phối được giao (Dành riêng cho NVK và QLK giám sát)
        'lenh-dieu-phoi' => [
            'name'        => 'Lệnh điều phối được giao',
            'description' => 'Danh sách nhiệm vụ vận chuyển, bốc dỡ và xếp dỡ hàng vào ô kệ',
            'group'       => 'KHO',
            'icon'        => 'bi-card-checklist',
            'default_url' => '?route=lenh-dieu-phoi/index',
            'view'        => ['NVK', 'QLK'],
            'write'       => ['NVK'], // NVK xác nhận nhận lệnh
        ],

        // 8. Quản lý tài khoản (Chỉ QTV)
        'tai-khoan' => [
            'name'        => 'Quản lý tài khoản',
            'description' => 'Cấp phát, phân quyền và khóa tài khoản người dùng hệ thống',
            'group'       => 'HE_THONG',
            'icon'        => 'bi-people',
            'default_url' => '?route=tai-khoan/index',
            'view'        => ['QTV'],
            'write'       => ['QTV'],
        ],

        // 9. Nhật ký hệ thống (QTV và BGD xem)
        'nhat-ky' => [
            'name'        => 'Nhật ký hệ thống',
            'description' => 'Theo dõi lịch sử truy cập, thay đổi dữ liệu và nhật ký bảo mật',
            'group'       => 'HE_THONG',
            'icon'        => 'bi-journal-text',
            'default_url' => '?route=nhat-ky/index',
            'view'        => ['QTV', 'BGD'],
            'write'       => [], // Nhật ký chỉ đọc, sinh tự động từ Service
        ],
    ],
];
