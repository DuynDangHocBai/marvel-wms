<?php

namespace App\Core;

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\UiKitController;
use App\Controllers\ErrorController;
use App\Controllers\DeNghiMuaController;
use App\Controllers\LenhSanXuatController;
use App\Controllers\PhanBoController;
use App\Controllers\DieuPhoiController;
use App\Controllers\KiemKeController;
use App\Controllers\HangLoiController;
use App\Controllers\LenhDieuPhoiController;
use App\Controllers\TaiKhoanController;
use App\Controllers\NhatKyController;

/**
 * Router điều hướng request dựa trên mảng whitelist cố định.
 * Không ghép nối động chuỗi URL thành tên class để phòng tránh lỗ hổng bảo mật.
 */
class Router
{
    /**
     * Mảng Whitelist định tuyến URL -> [Tên Controller, Tên Action]
     */
    private static array $routes = [
        // Xác thực
        'auth/login'            => [AuthController::class, 'login'],
        'auth/logout'           => [AuthController::class, 'logout'],

        // Bảng điều khiển
        'dashboard/index'       => [DashboardController::class, 'index'],

        // UI Kit & Thiết kế mẫu (chỉ cho dev)
        'uikit/index'           => [UiKitController::class, 'index'],

        // Xử lý lỗi
        'error/forbidden'       => [ErrorController::class, 'forbidden'],
        'error/not-found'       => [ErrorController::class, 'notFound'],

        // Phân hệ Kế hoạch
        'de-nghi-mua/index'     => [DeNghiMuaController::class, 'index'],
        'de-nghi-mua/create'    => [DeNghiMuaController::class, 'create'],
        'de-nghi-mua/detail'    => [DeNghiMuaController::class, 'detail'],
        'de-nghi-mua/cancel'    => [DeNghiMuaController::class, 'cancel'],
        'lenh-san-xuat/index'   => [LenhSanXuatController::class, 'index'],
        'lenh-san-xuat/create'  => [LenhSanXuatController::class, 'create'],
        'lenh-san-xuat/detail'  => [LenhSanXuatController::class, 'detail'],

        // Phân hệ Kho
        'phan-bo/index'         => [PhanBoController::class, 'index'],
        'phan-bo/create'        => [PhanBoController::class, 'create'],
        'phan-bo/detail'        => [PhanBoController::class, 'detail'],
        'dieu-phoi/index'       => [DieuPhoiController::class, 'index'],
        'dieu-phoi/create'      => [DieuPhoiController::class, 'create'],
        'dieu-phoi/detail'      => [DieuPhoiController::class, 'detail'],
        'kiem-ke/index'         => [KiemKeController::class, 'index'],
        'kiem-ke/create'        => [KiemKeController::class, 'create'],
        'kiem-ke/update'        => [KiemKeController::class, 'update'],
        'kiem-ke/detail'        => [KiemKeController::class, 'detail'],
        'hang-loi/index'        => [HangLoiController::class, 'index'],
        'hang-loi/create'       => [HangLoiController::class, 'create'],
        'hang-loi/detail'       => [HangLoiController::class, 'detail'],
        'lenh-dieu-phoi/index'   => [LenhDieuPhoiController::class, 'index'],
        'lenh-dieu-phoi/detail'  => [LenhDieuPhoiController::class, 'detail'],
        'lenh-dieu-phoi/confirm' => [LenhDieuPhoiController::class, 'confirm'],

        // Phân hệ Hệ thống
        'tai-khoan/index'       => [TaiKhoanController::class, 'index'],
        'nhat-ky/index'         => [NhatKyController::class, 'index'],
    ];

    /**
     * Xử lý và điều hướng request hiện tại.
     */
    public static function dispatch(): void
    {
        Auth::initSession();

        // Lấy tham số route từ query string (?route=module/action)
        $route = $_GET['route'] ?? '';
        $route = trim($route, '/');

        // Route mặc định nếu không truyền
        if (empty($route)) {
            $route = Auth::check() ? 'dashboard/index' : 'auth/login';
        }

        // Kiểm tra trong whitelist
        if (!isset(self::$routes[$route])) {
            http_response_code(404);
            $errorController = new ErrorController();
            $errorController->notFound($route);
            return;
        }

        [$controllerClass, $action] = self::$routes[$route];

        if (!class_exists($controllerClass)) {
            http_response_code(404);
            $errorController = new ErrorController();
            $errorController->notFound($route);
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $action)) {
            http_response_code(404);
            $errorController = new ErrorController();
            $errorController->notFound($route);
            return;
        }

        // Gọi action
        $controller->$action();
    }
}

