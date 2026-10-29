<?php

namespace App\Services;

use App\Repositories\PhanBoRepository;

/**
 * Service: Xử lý nghiệp vụ phân bổ số lượng nguyên vật liệu theo FEFO (Prompt 5 - QLK)
 */
class PhanBoService
{
    private PhanBoRepository $repo;

    public function __construct(PhanBoRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Validate dữ liệu form phân bổ số lượng
     */
    public function validateAllocation(string $maLenhSX, string $maKhoNguon, array $allocations): array
    {
        $errors = [];

        if (empty($maLenhSX)) {
            $errors[] = 'Vui lòng chọn lệnh sản xuất cần phân bổ.';
        } else {
            $order = $this->repo->getOrderHeader($maLenhSX);
            if (!$order) {
                $errors[] = "Không tìm thấy lệnh sản xuất {$maLenhSX}.";
            } elseif (!in_array($order['trangThai'], ['DA_PHAT', 'DANG_PHAN_BO'])) {
                $errors[] = "Lệnh sản xuất {$maLenhSX} không ở trạng thái có thể phân bổ (Phải là DA_PHAT hoặc DANG_PHAN_BO).";
            }
        }

        if (empty($maKhoNguon)) {
            $errors[] = 'Vui lòng chọn kho nguồn xuất nguyên vật liệu.';
        }

        if (empty($allocations)) {
            $errors[] = 'Chưa chọn lô hàng và số lượng phân bổ nào.';
            return ['valid' => false, 'errors' => $errors];
        }

        $validCount = 0;
        $skuAllocTotals = [];

        foreach ($allocations as $idx => $item) {
            $maLo = $item['maLo'] ?? '';
            $maViTri = $item['maViTri'] ?? '';
            $maSKU = $item['maSKU'] ?? '';
            $qty = isset($item['soLuongPhanBo']) ? (float)$item['soLuongPhanBo'] : 0.0;

            if ($qty < 0) {
                $errors[] = "Số lượng phân bổ cho lô {$maLo} không được nhỏ hơn 0.";
                continue;
            }

            if ($qty == 0) {
                continue;
            }

            $validCount++;

            // Kiểm tra tồn kho của lô
            $tk = $this->repo->getTonKhoRow($maLo, $maViTri);
            if (!$tk) {
                $errors[] = "Không tìm thấy tồn kho cho lô {$maLo} tại vị trí {$maViTri}.";
                continue;
            }

            $available = (float)$tk['tonKhaDungLo'];
            if ($qty > $available) {
                $errors[] = "Số lượng phân bổ vượt quá số lượng tồn khả dụng.";
            }

            if (!isset($skuAllocTotals[$maSKU])) {
                $skuAllocTotals[$maSKU] = 0.0;
            }
            $skuAllocTotals[$maSKU] += $qty;
        }

        if ($validCount === 0) {
            $errors[] = 'Vui lòng nhập số lượng phân bổ lớn hơn 0 cho ít nhất một lô hàng.';
        }

        // Kiểm tra không vượt quá nhu cầu còn lại của lệnh sản xuất
        if (!empty($maLenhSX)) {
            $orderNeeds = $this->repo->getNVLNeedsForOrder($maLenhSX);
            $needsBySku = [];
            foreach ($orderNeeds as $n) {
                $needsBySku[$n['maSKU']] = (float)$n['soLuongConLai'];
            }

            foreach ($skuAllocTotals as $sku => $totalQty) {
                $remain = $needsBySku[$sku] ?? 0.0;
                if ($totalQty > $remain) {
                    $errors[] = "Tổng số lượng phân bổ của mặt hàng {$sku} ({$totalQty}) vượt quá nhu cầu cần cấp còn lại ({$remain}).";
                }
            }
        }

        return [
            'valid'  => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Thuật toán gợi ý phân bổ tự động theo FEFO (First Expired, First Out)
     * Ưu tiên các lô có ngày hết hạn sớm nhất, bỏ qua lô hết hạn / CACH_LY / KHONG_DAT
     */
    public function suggestFEFO(string $maLenhSX, ?string $maKhoNguon = null): array
    {
        $needs = $this->repo->getNVLNeedsForOrder($maLenhSX);
        $suggestions = [];

        foreach ($needs as $nvl) {
            $remainNeed = (float)$nvl['soLuongConLai'];
            if ($remainNeed <= 0) {
                continue;
            }

            // Lấy danh sách lô khả dụng (đã được repo sắp xếp FEFO ASC)
            $lots = $this->repo->getAvailableLotsForSKU($nvl['maSKU'], $maKhoNguon);

            foreach ($lots as $lot) {
                $lotAvailable = (float)$lot['tonKhaDungLo'];
                if ($lotAvailable <= 0) {
                    continue;
                }

                $alloc = min($remainNeed, $lotAvailable);
                if ($alloc > 0) {
                    $suggestions[] = [
                        'maLo'          => $lot['maLo'],
                        'maViTri'       => $lot['maViTri'],
                        'maSKU'         => $nvl['maSKU'],
                        'tenHang'       => $nvl['tenHang'],
                        'donViTinh'     => $nvl['donViTinh'],
                        'ngayHetHan'    => $lot['ngayHetHan'],
                        'tonKhaDungLo'  => $lotAvailable,
                        'soLuongPhanBo' => $alloc,
                    ];
                    $remainNeed -= $alloc;
                }

                if ($remainNeed <= 0) {
                    break;
                }
            }
        }

        return $suggestions;
    }
}

