<?php
/**
 * MARVEL WMS - Front Controller duy nhất
 * Điều phối mọi request qua ?route=module/action
 */

// 1. Nạp tệp cấu hình
$configFile = dirname(__DIR__) . '/config/config.php';
if (!file_exists($configFile)) {
    $configFile = dirname(__DIR__) . '/config/config.example.php';
}
$config = require $configFile;

// 2. Thiết lập chế độ báo lỗi theo APP_ENV
$appEnv = $config['app_env'] ?? 'dev';
if ($appEnv === 'dev') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

// 3. Múi giờ chuẩn Việt Nam (GMT+7)
date_default_timezone_set('Asia/Ho_Chi_Minh');

// 4. Đăng ký Autoloader cho namespace App\
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// 5. Nạp hàm helper dùng chung
require_once dirname(__DIR__) . '/app/core/helpers.php';

// 6. Khởi chạy router (nếu không phải đang chạy trong môi trường test script)
if (!defined('TEST_RUNNER')) {
    App\Core\Router::dispatch();
}
