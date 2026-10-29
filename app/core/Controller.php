<?php

namespace App\Core;

/**
 * Controller cơ sở cung cấp các phương thức tiện ích cho các Controller con.
 */
abstract class Controller
{
    /**
     * Render view với dữ liệu và layout chỉ định.
     */
    protected function render(string $view, array $data = [], string|false $layout = 'main'): void
    {
        View::render($view, $data, $layout);
    }

    /**
     * Trả về phản hồi dạng JSON.
     */
    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Chuyển hướng trình duyệt.
     */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Kiểm tra phương thức HTTP POST.
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Kiểm tra phương thức HTTP GET.
     */
    protected function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Lấy giá trị từ $_POST.
     */
    protected function post(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    /**
     * Lấy giá trị từ $_GET.
     */
    protected function get(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }

    /**
     * Kiểm tra CSRF token cho các thao tác POST.
     */
    protected function validateCsrf(): bool
    {
        $token = $this->post('csrf_token') ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!Csrf::validateToken($token)) {
            Flash::error('Yêu cầu không hợp lệ hoặc phiên bảo mật đã hết hạn (CSRF error).');
            return false;
        }
        return true;
    }
}
