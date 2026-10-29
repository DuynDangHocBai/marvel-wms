<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;

/**
 * Controller hiển thị trang lỗi 403 (Forbidden) và 404 (Not Found).
 */
class ErrorController extends Controller
{
    /**
     * Lỗi 403 - Không có quyền truy cập.
     */
    public function forbidden(): void
    {
        http_response_code(403);
        $layout = Auth::check() ? 'main' : 'auth';

        $this->render('errors/403', [
            'pageTitle'          => '403 - Quyền truy cập bị từ chối',
            'breadcrumbSection'  => 'Hệ thống bảo mật',
        ], $layout);
    }

    /**
     * Lỗi 404 - Không tìm thấy trang.
     */
    public function notFound(string $requestedRoute = ''): void
    {
        http_response_code(404);
        $layout = Auth::check() ? 'main' : 'auth';

        $this->render('errors/404', [
            'pageTitle'          => '404 - Trang không tồn tại',
            'breadcrumbSection'  => 'Hệ thống',
            'requestedRoute'     => $requestedRoute,
        ], $layout);
    }
}

