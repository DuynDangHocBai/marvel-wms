<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;

/**
 * Controller hiển thị trang Thư viện UI Kit và đối chiếu 4 ảnh mẫu Figma.
 * Chỉ hoạt động trong môi trường phát triển (APP_ENV = 'dev').
 */
class UiKitController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();

        $configFile = dirname(__DIR__, 2) . '/config/config.php';
        $config = file_exists($configFile) ? require $configFile : [];
        $appEnv = $config['app_env'] ?? 'dev';

        if ($appEnv !== 'dev') {
            http_response_code(404);
            $errorController = new ErrorController();
            $errorController->notFound('uikit/index');
            return;
        }

        $this->render('uikit/index', [
            'pageTitle'         => 'Thư viện UI Kit & Thiết kế mẫu',
            'breadcrumbSection' => 'Kiểm thử (DEV)',
        ]);
    }
}

