<?php

namespace App\Services;

/**
 * Service xử lý tính toán và kiểm tra tính hợp lệ của biên bản kiểm kê (UC 2.6.3).
 * Quy tắc:
 * - soLuongChenhLech = thucTe - soSach
 * - giaTriChenhLech = chenhLech * donGia
 * - phanLoai: THUA (> 0), THIEU (< 0), KHOP (== 0)
 * - tongThua = tổng các dòng chênh lệch dương
 * - tongThieu = tổng giá trị tuyệt đối các dòng chênh lệch âm
 * - tongChenhLech = tongThua + tongThieu (tổng độ lớn chênh lệch)
 * - giaTriRong = tongThua - tongThieu
 */
class KiemKeService
{
    /**
     * Tính toán chênh lệch và kiểm tra tính đầy đủ của số liệu biên bản.
     */
    public function calculate(array $record): array
    {
        $maBienBan = $record['maBienBan'] ?? '';
        $errors = [];
        $chiTiet = $record['chiTiet'] ?? [];

        $tongThua = 0.0;
        $tongThieu = 0.0;
        $soDongThua = 0;
        $soDongThieu = 0;
        $soDongKhop = 0;
        $processed = [];

        if (empty($chiTiet)) {
            $errors[] = "Biên bản kiểm kê {$maBienBan} không có mặt hàng nào để tính toán.";
            return [
                'valid'             => false,
                'errors'            => $errors,
                'chiTiet'           => [],
                'tongThua'          => 0.0,
                'tongThieu'         => 0.0,
                'tongChenhLech'     => 0.0,
                'giaTriRong'        => 0.0,
                'soDongThua'        => 0,
                'soDongThieu'       => 0,
                'soDongKhop'        => 0,
                'isKhongChenhLech'  => false,
                'maBienBan'         => $maBienBan,
            ];
        }

        foreach ($chiTiet as $line) {
            $sku = $line['maSKU'] ?? 'N/A';
            $actual = $line['soLuongThucTe'] ?? null;
            $book = $line['soLuongSoSach'] ?? null;
            $price = $line['donGia'] ?? null;

            // 1. Kiểm tra số lượng thực tế
            if ($actual === null || $actual === '' || !is_numeric($actual) || (float)$actual < 0) {
                $errors[] = "Biên bản kiểm kê chưa có số lượng thực tế: {$sku}.";
                continue;
            }

            // 2. Kiểm tra số lượng sổ sách và đơn giá
            if ($book === null || $book === '' || !is_numeric($book) || (float)$book < 0
                || $price === null || $price === '' || !is_numeric($price) || (float)$price < 0) {
                $errors[] = "Không đủ thông tin để tính chênh lệch: {$sku} (số lượng sổ sách hoặc đơn giá).";
                continue;
            }

            $diff = (float)$actual - (float)$book;
            $value = $diff * (float)$price;

            if ($diff > 0.00001) {
                $phanLoai = 'THUA';
                $tongThua += $value;
                $soDongThua++;
            } elseif ($diff < -0.00001) {
                $phanLoai = 'THIEU';
                $tongThieu += abs($value);
                $soDongThieu++;
            } else {
                $phanLoai = 'KHOP';
                $diff = 0.0;
                $value = 0.0;
                $soDongKhop++;
            }

            $line['soLuongChenhLech'] = $diff;
            $line['giaTriChenhLech']  = $value;
            $line['phanLoai']         = $phanLoai;
            $processed[]              = $line;
        }

        $isValid = (count($errors) === 0);
        $tongChenhLech = $tongThua + $tongThieu;
        $giaTriRong = $tongThua - $tongThieu;
        $isKhongChenhLech = ($isValid && abs($tongChenhLech) < 0.00001);

        return [
            'valid'             => $isValid,
            'errors'            => $errors,
            'chiTiet'           => $processed,
            'tongThua'          => round($tongThua, 2),
            'tongThieu'         => round($tongThieu, 2),
            'tongChenhLech'     => round($tongChenhLech, 2),
            'giaTriRong'        => round($giaTriRong, 2),
            'soDongThua'        => $soDongThua,
            'soDongThieu'       => $soDongThieu,
            'soDongKhop'        => $soDongKhop,
            'isKhongChenhLech'  => $isKhongChenhLech,
            'maBienBan'         => $maBienBan,
        ];
    }
}

