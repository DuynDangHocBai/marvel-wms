<?php
/**
 * Marvel WMS - File cấu hình mẫu
 * Sao chép thành config.php và điều chỉnh thông số cho phù hợp với môi trường.
 */

return [
    // Môi trường ứng dụng: 'dev' (phát triển) hoặc 'prod' (production)
    'app_env' => 'dev',

    // Tên ứng dụng hiển thị
    'app_name' => 'Hệ thống Quản lý kho Marvel Sweet',
    'app_version' => 'v1.0.0',

    // Đường dẫn cơ sở (Apache chạy cổng 81 theo AGENT_RULES.md)
    'base_url' => 'http://localhost:81/marvel-wms/public/',

    // Thời gian hết hạn phiên làm việc do không thao tác (giây) - 15 phút = 900s
    'session_lifetime' => 900,

    // Cấu hình cơ sở dữ liệu MySQL / MariaDB
    'db' => [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'dbname'   => 'marvel_wms',
        'username' => 'root',
        'password' => '',
        'charset'  => 'utf8mb4',
    ],
];
