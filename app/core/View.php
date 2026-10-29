<?php

namespace App\Core;

require_once __DIR__ . '/helpers.php';

/**
 * Lớp xử lý Render View và nhúng Layout / Partials.
 */
class View
{
    private static string $viewsPath;

    public static function init(): void
    {
        self::$viewsPath = dirname(__DIR__) . '/views';
    }

    /**
     * Render một view bên trong layout.
     *
     * @param string $view Đường dẫn view (vd: 'auth/login', 'dashboard/index')
     * @param array $data Dữ liệu truyền sang view
     * @param string|false $layout Tên layout trong views/layouts (vd: 'main', 'auth', false)
     */
    public static function render(string $view, array $data = [], string|false $layout = 'main'): void
    {
        self::init();

        $viewFile = self::$viewsPath . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("Không tìm thấy tệp view: {$viewFile}");
        }

        // Đẩy các biến vào view
        extract($data);

        // Đọc nội dung view
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Nếu không dùng layout thì in thẳng nội dung view
        if ($layout === false) {
            echo $content;
            return;
        }

        // Nạp layout
        $layoutFile = self::$viewsPath . '/layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            throw new \RuntimeException("Không tìm thấy tệp layout: {$layoutFile}");
        }

        require $layoutFile;
    }

    /**
     * Nhúng một partial view (vd: 'page_header', 'status_badge').
     *
     * @param string $partial Tên partial trong app/views/partials/
     * @param array $data Dữ liệu truyền vào partial
     */
    public static function partial(string $partial, array $data = []): void
    {
        self::init();
        $partialFile = self::$viewsPath . '/partials/' . $partial . '.php';

        if (file_exists($partialFile)) {
            extract($data);
            require $partialFile;
        } else {
            echo "<!-- Partial [{$partial}] không tìm thấy -->";
        }
    }
}
