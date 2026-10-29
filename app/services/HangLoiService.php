<?php

namespace App\Services;

use App\Config\Messages;
use App\Repositories\HangLoiRepository;

/**
 * Service xử lý nghiệp vụ Đề xuất xử lý hàng lỗi hỏng (UC 2.4.3).
 * Quy tắc:
 * - Phương án: TIEU_HUY (Tiêu hủy) hoặc TRA_NCC (Trả lại nhà cung cấp).
 * - Bắt buộc phương án và lý do (tối đa 2.000 ký tự).
 * - Phải chọn ít nhất 1 mặt hàng lỗi hỏng.
 * - Số lượng xử lý phải là số nguyên > 0 và <= số lượng lỗi còn lại.
 * - Trả lại NCC chỉ áp dụng cho hàng có nhà cung cấp (không áp dụng cho hàng khách trả về).
 * - Thành tiền = Số lượng xử lý * Đơn giá.
 * - Tổng giá trị = Tổng thành tiền của tất cả các dòng được chọn.
 */
class HangLoiService
{
    private HangLoiRepository $repo;

    public function __construct(?HangLoiRepository $repo = null)
    {
        $this->repo = $repo ?? new HangLoiRepository();
    }

    /**
     * Kiểm tra tính hợp lệ và tổng hợp giá trị đề xuất.
     */
    public function validate(array $input): array
    {
        $errors = [];
        $selected = $input['selected'] ?? [];
        if (!is_array($selected)) {
            $selected = [];
        }
        $selected = array_values(array_unique(array_filter($selected)));

        $qtyInput = $input['qty'] ?? [];
        if (!is_array($qtyInput)) {
            $qtyInput = [];
        }

        $phuongAn = trim((string)($input['phuongAn'] ?? ''));
        // Chuẩn hóa tên phương án nếu truyền tiếng Việt
        if ($phuongAn === 'Tiêu hủy') {
            $phuongAn = 'TIEU_HUY';
        } elseif ($phuongAn === 'Trả lại nhà cung cấp') {
            $phuongAn = 'TRA_NCC';
        }

        $lyDo = trim((string)($input['lyDo'] ?? ''));

        // 1. Kiểm tra danh sách chọn
        if (empty($selected)) {
            $errors[] = 'Vui lòng chọn ít nhất một mặt hàng lỗi, hỏng.';
        }

        // 2. Kiểm tra phương án xử lý
        if (!in_array($phuongAn, ['TIEU_HUY', 'TRA_NCC'], true)) {
            $errors[] = 'Vui lòng chọn phương án xử lý hợp lệ (Tiêu hủy hoặc Trả lại nhà cung cấp).';
        }

        // 3. Kiểm tra lý do xử lý
        if ($lyDo === '') {
            $errors[] = 'Vui lòng nhập lý do xử lý.';
        } elseif (mb_strlen($lyDo) > 2000) {
            $errors[] = 'Lý do xử lý quá dài (tối đa 2.000 ký tự).';
        }

        $details = [];
        $tongGiaTri = 0.0;

        foreach ($selected as $maLoi) {
            $item = $this->repo->findLoiById((string)$maLoi);
            if (!$item) {
                $errors[] = "Không tìm thấy thông tin hàng lỗi hỏng: {$maLoi}.";
                continue;
            }

            $tenHang = $item['tenHang'] ?? $maLoi;
            $rawQty = trim((string)($qtyInput[$maLoi] ?? ''));

            // Kiểm tra số lượng nhập
            if ($rawQty === '' || !is_numeric($rawQty) || (float)$rawQty <= 0) {
                $errors[] = "{$tenHang}: Số lượng cần xử lý phải là số lớn hơn 0.";
                continue;
            }

            $qty = (float)$rawQty;
            $soLuongConLai = (float)($item['soLuongConLai'] ?? 0.0);

            if ($qty > $soLuongConLai) {
                $errors[] = Messages::HL_EXC_6_1_OVER_QTY . "<br><small class='text-muted'>({$tenHang}: xử lý " . formatSo($qty) . " / còn " . formatSo($soLuongConLai) . ")</small>";
                continue;
            }

            // Kiểm tra ràng buộc Trả NCC: Không áp dụng cho hàng khách trả về
            if ($phuongAn === 'TRA_NCC') {
                $nguonGoc = $item['nguonGoc'] ?? '';
                if (stripos($nguonGoc, 'trả về') !== false || stripos($nguonGoc, 'khách') !== false) {
                    $errors[] = "{$tenHang}: Nguồn gốc từ khách hàng trả về, không có nhà cung cấp để trả lại.";
                    continue;
                }
            }

            $donGia = (float)($item['donGia'] ?? 0.0);
            $thanhTien = $qty * $donGia;
            $tongGiaTri += $thanhTien;

            $details[] = [
                'maHangLoi'       => $item['maHangLoi'],
                'maSKU'           => $item['maSKU'],
                'tenHang'         => $item['tenHang'],
                'donViTinh'       => $item['donViTinh'],
                'soLo'            => $item['soLo'] ?? 'N/A',
                'ngayHetHan'      => $item['ngayHetHan'] ?? '2099-12-31',
                'soLuongLoiHong'  => (float)$item['soLuongLoiHong'],
                'soLuongConLai'   => $soLuongConLai,
                'soLuongXuLy'     => $qty,
                'donGia'          => $donGia,
                'thanhTien'       => $thanhTien,
                'maChungTuNguon'  => $item['maChungTuNguon'] ?? '',
                'nguyenNhan'      => $item['nguyenNhan'] ?? '',
            ];
        }

        return [
            'valid'      => (count($errors) === 0),
            'errors'     => $errors,
            'chiTiet'    => $details,
            'tongGiaTri' => round($tongGiaTri, 2),
            'phuongAn'   => $phuongAn,
            'lyDo'       => $lyDo,
        ];
    }
}

