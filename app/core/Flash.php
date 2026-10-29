<?php

namespace App\Core;

/**
 * Quản lý Flash messages (thông báo hiển thị một lần trong session).
 */
class Flash
{
    private const SESSION_KEY = '_flash_messages';

    private static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set(string $type, string $message): void
    {
        self::startSession();
        $_SESSION[self::SESSION_KEY][$type][] = $message;
    }

    public static function success(string $message): void
    {
        self::set('success', $message);
    }

    public static function error(string $message): void
    {
        self::set('error', $message);
    }

    public static function warning(string $message): void
    {
        self::set('warning', $message);
    }

    public static function info(string $message): void
    {
        self::set('info', $message);
    }

    public static function has(string $type): bool
    {
        self::startSession();
        return !empty($_SESSION[self::SESSION_KEY][$type]);
    }

    /**
     * Lấy danh sách thông báo theo loại và xóa khỏi session.
     */
    public static function get(string $type): array
    {
        self::startSession();
        if (empty($_SESSION[self::SESSION_KEY][$type])) {
            return [];
        }
        $messages = $_SESSION[self::SESSION_KEY][$type];
        unset($_SESSION[self::SESSION_KEY][$type]);
        return $messages;
    }

    /**
     * Lấy toàn bộ thông báo và dọn dẹp.
     */
    public static function all(): array
    {
        self::startSession();
        $messages = $_SESSION[self::SESSION_KEY] ?? [];
        unset($_SESSION[self::SESSION_KEY]);
        return $messages;
    }
}
