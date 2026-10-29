<?php

namespace App\Services;

use App\Config\Messages;
use App\Repositories\LenhSanXuatRepository;

/**
 * Service xử lý logic Tạo lệnh sản xuất & Tính toán nhu cầu NVL theo BOM (Prompt 4 - LKH).
 */
class LenhSanXuatService
{
    private LenhSanXuatRepository $repo;

    public function __construct(?LenhSanXuatRepository $repo = null)
    {
        $this->repo = $repo ?? new LenhSanXuatRepository();
    }

    /**
     * Xác định nhu cầu nguyên vật liệu theo BOM và đối chiếu tồn khả dụng (vw_ton_kho_kha_dung).
     */
    public function calculateNVLNeeds(array $itemsToProduce): array
    {
        $bomTotal = [];

        foreach ($itemsToProduce as $it) {
            $sku = $it['maSKU'] ?? '';
            $qty = (float)($it['soLuongGiao'] ?? 0.0);

            if ($qty <= 0) {
                continue;
            }

            $boms = $this->repo->getBOM($sku);
            foreach ($boms as $b) {
                $nvlSku = $b['maNVL'];
                $unit = $b['donViTinh'];
                $name = $b['tenNVL'];
                $dinhMuc = (float)$b['soLuongDinhMuc'];
                $need = $qty * $dinhMuc;

                if (!isset($bomTotal[$nvlSku])) {
                    $bomTotal[$nvlSku] = [
                        'maSKU'         => $nvlSku,
                        'tenNVL'        => $name,
                        'donViTinh'     => $unit,
                        'soLuongCanCap' => 0.0,
                    ];
                }
                $bomTotal[$nvlSku]['soLuongCanCap'] += $need;
            }
        }

        $nvlList = [];
        $hasMissing = false;

        foreach ($bomTotal as $nvlSku => $data) {
            $canCap = round($data['soLuongCanCap'], 3);
            $tonKhaDung = round($this->repo->getTonKhaDung($nvlSku), 3);

            if ($canCap <= $tonKhaDung) {
                $trangThai = 'DU';
                $thieu = 0.0;
            } else {
                $trangThai = 'THIEU';
                $thieu = round($canCap - $tonKhaDung, 3);
                $hasMissing = true;
            }

            $nvlList[] = [
                'maSKU'         => $nvlSku,
                'tenNVL'        => $data['tenNVL'],
                'tenHang'       => $data['tenNVL'],
                'donViTinh'     => $data['donViTinh'],
                'soLuongCanCap' => $canCap,
                'soLuongCan'    => $canCap,
                'tonKhaDung'    => $tonKhaDung,
                'soLuongThieu'  => $thieu,
                'trangThai'     => $trangThai,
            ];
        }

        $missingDetails = [];
        if ($hasMissing) {
            foreach ($nvlList as $item) {
                if ($item['trangThai'] === 'THIEU') {
                    $missingDetails[] = "{$item['tenNVL']}: " . formatSo($item['soLuongThieu']) . " {$item['donViTinh']}";
                }
            }
        }
        $canhBaoThieu = !empty($missingDetails)
            ? Messages::LSX_EXC_9_1_INSUFFICIENT . implode(', ', $missingDetails)
            : '';

        return [
            'canIssue'      => !$hasMissing,
            'hasMissing'    => $hasMissing,
            'nvlList'       => $nvlList,
            'nvlNeeds'      => $nvlList,
            'canhBaoThieu'  => $canhBaoThieu,
        ];
    }

    /**
     * Kiểm tra tính hợp lệ của dữ liệu form tạo lệnh sản xuất.
     */
    public function validateOrderForm(array $formData): array
    {
        $errors = [];
        $maKeHoach = trim((string)($formData['maKeHoach'] ?? ''));

        if (empty($maKeHoach)) {
            $errors[] = 'Vui lòng chọn kế hoạch sản xuất.';
            return ['valid' => false, 'errors' => $errors, 'items' => []];
        }

        $plan = $this->repo->getPlanDetail($maKeHoach);
        if (!$plan || $plan['trangThai'] !== 'DA_DUYET') {
            $errors[] = 'Kế hoạch sản xuất không tồn tại hoặc chưa được Ban Giám Đốc phê duyệt.';
            return ['valid' => false, 'errors' => $errors, 'items' => []];
        }

        $planRemainMap = [];
        foreach ($plan['chiTiet'] as $ct) {
            $planRemainMap[$ct['maSKU']] = (float)$ct['soLuongConLai'];
        }

        $rawItems = $formData['items'] ?? [];
        if (!is_array($rawItems) || empty($rawItems)) {
            $errors[] = 'Vui lòng nhập ít nhất một sản phẩm cần giao lệnh sản xuất.';
            return ['valid' => false, 'errors' => $errors, 'items' => []];
        }

        $validItems = [];

        foreach ($rawItems as $it) {
            $sku = $it['maSKU'] ?? '';
            $qty = (float)($it['soLuongGiao'] ?? 0);

            if ($qty <= 0) {
                $errors[] = "Số lượng giao sản xuất cho sản phẩm {$sku} phải lớn hơn 0.";
                continue;
            }

            $maxAvail = $planRemainMap[$sku] ?? 0.0;
            if ($qty > $maxAvail) {
                $errors[] = "Số lượng nhập vượt quá hạn mức còn lại của kế hoạch sản xuất (Tối đa: {$maxAvail})";
                continue;
            }

            $validItems[] = [
                'maSKU'       => $sku,
                'soLuongGiao' => $qty,
            ];
        }

        return [
            'valid'  => (count($errors) === 0),
            'errors' => $errors,
            'items'  => $validItems,
        ];
    }
}

