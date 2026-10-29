<?php

namespace App\Core;

/**
 * Xử lý bảo vệ chống tấn công CSRF (Cross-Site Request Forgery).
 */
class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    /**
     * Sinh token mới hoặc lấy token hiện có trong session.
     */
    public static function generateToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Lấy token hiện tại.
     */
    public static function getToken(): string
    {
        return self::generateToken();
    }

    /**
     * Kiểm tra tính hợp lệ của token gửi lên từ form hoặc header.
     */
    public static function validateToken(?string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $sessionToken = $_SESSION[self::SESSION_KEY] ?? null;

        if (!$sessionToken || !$token) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    /**
     * Tạo thẻ input hidden dùng trong form HTML.
     */
    public static function field(): string
    {
        $token = htmlspecialchars(self::generateToken(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }
}
