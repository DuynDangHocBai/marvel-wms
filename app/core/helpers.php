<?php

// Tải cấu hình thông báo tập trung của hệ thống
require_once dirname(__DIR__) . '/config/messages.php';

if (!function_exists('formatSo')) {
    /**
     * Định dạng số thống nhất kiểu vi-VN ở mọi trang:
     * - Tiền tệ ($isCurrency = true): dạng 109.000 đ
     * - Số lượng ($isCurrency = false): tối đa 3 chữ số thập phân, bỏ số 0 thừa (25 thay vì 25.00, 5,5 thay vì 5.500)
     *
     * @param float|int|string|null $val Giá trị cần định dạng
     * @param bool $isCurrency Có phải tiền tệ hay không
     * @return string
     */
    function formatSo(float|int|string|null $val, bool $isCurrency = false): string
    {
        if ($val === null || $val === '') {
            return $isCurrency ? '0 đ' : '0';
        }

        $num = (float)$val;

        if ($isCurrency) {
            return number_format(round($num), 0, ',', '.') . ' đ';
        }

        // Định dạng tối đa 3 chữ số thập phân
        $formatted = number_format($num, 3, ',', '.');
        if (str_contains($formatted, ',')) {
            $formatted = rtrim(rtrim($formatted, '0'), ',');
        }

        return $formatted;
    }
}

