<?php

namespace App\Services;

use App\Config\Messages;
use App\Repositories\DieuPhoiRepository;

/**
 * Service: Xử lý nghiệp vụ kiểm tra điều kiện và gợi ý vị trí điều phối nhập kho (Prompt 6 - QLK, NVK)
 */
class DieuPhoiService
{
    private DieuPhoiRepository $repo;

    public function __construct(DieuPhoiRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Đánh giá tính khả thi khi điều phối phiếu nhập kho:
     * - Kiểm tra trạng thái phiếu nhập (CHO_DIEU_PHOI)
     * - Kiểm tra chất lượng lô hàng (DAT, không CACH_LY / CHO_KIEM) (BR-03, BR-09)
     * - Kiểm tra hạn sử dụng của lô hàng (chưa hết hạn)
     * - Kiểm tra trạng thái kho tiếp nhận (HOAT_DONG, không BAO_TRI)
     * - Kiểm tra sức chứa khả dụng của kho tiếp nhận
     */
    public function evaluateInboundRequest(string $maPhieuNhap, ?string $targetKho = null): array
    {
        $errors = [];

        if (empty(trim($maPhieuNhap))) {
            return [
                'valid'  => false,
                'errors' => [Messages::DP_ALT_3_1_EMPTY_REQ],
            ];
        }

        $req = $this->repo->getInboundRequest($maPhieuNhap);

        if (!$req) {
            return [
                'valid'  => false,
                'errors' => [Messages::DP_EXC_4_1_LOAD_FAILED],
            ];
        }

        if ($req['trangThai'] !== 'CHO_DIEU_PHOI') {
            $errors[] = Messages::DP_EXC_16_2_ALREADY_DISPATCHED;
        }

        $khoId = $targetKho ?: $req['maKho'];
        $wh = $this->repo->getWarehouse($khoId);

        if (!$wh || $wh['trangThai'] !== 'HOAT_DONG') {
            $errors[] = Messages::DP_ALT_5_1_NO_WH;
        }

        // Kiểm tra từng dòng hàng hóa và số lô
        $items = $req['items'] ?? [];
        $tongSoLuong = 0.0;
        $tongTheTich = 0.0;

        foreach ($items as $it) {
            $tongSoLuong += (float)$it['soLuong'];
            $theTich = (float)($it['tongTheTich'] > 0 ? $it['tongTheTich'] : $it['soLuong']);
            $tongTheTich += $theTich;

            // Kiểm tra trạng thái kiểm định QA/QC
            $qcStatus = $it['trangThaiChatLuong'] ?? '';
            $lotStatus = $it['trangThaiLo'] ?? '';
            if ($qcStatus !== 'DAT' || $lotStatus === 'TAM_KHOA') {
                $errors[] = sprintf(Messages::DP_QC_REJECTED, $it['maLo']);
            }

            // Kiểm tra hạn sử dụng (giả định ngày mốc mô phỏng 2026-10-28 hoặc CURDATE)
            if (!empty($it['ngayHetHan'])) {
                $expDate = strtotime($it['ngayHetHan']);
                $currDate = strtotime(date('Y-m-d'));
                if ($expDate < $currDate) {
                    $errors[] = "Lô hàng {$it['maLo']} đã hết hạn sử dụng (Ngày hết hạn: " . date('d/m/Y', $expDate) . ").";
                }
            }
        }

        // Kiểm tra sức chứa của kho tiếp nhận (Alt 9.1)
        if ($wh && $wh['trangThai'] === 'HOAT_DONG') {
            $availableCapacity = (float)$wh['sucChuaKhaDung'];
            if ($tongTheTich > $availableCapacity) {
                $errors[] = Messages::DP_ALT_9_1_CAPACITY;
            } else {
                // Kiểm tra các vị trí khả dụng trong kho cho từng mặt hàng (Alt 10.1 & 12.1)
                foreach ($items as $it) {
                    $availPositions = $this->repo->getAvailablePositionsForWarehouse($khoId, $it['loaiHang'] ?? null);
                    if (empty($availPositions)) {
                        $errors[] = Messages::DP_ALT_10_1_NO_BIN;
                        break;
                    }
                    $totalPosCap = array_sum(array_column($availPositions, 'sucChuaKhaDung'));
                    if ($totalPosCap < (float)$it['soLuong']) {
                        $errors[] = Messages::DP_ALT_9_1_CAPACITY;
                        break;
                    }
                }
            }
        }

        return [
            'valid'       => empty($errors),
            'errors'      => $errors,
            'request'     => $req,
            'warehouse'   => $wh,
            'tongSoLuong' => $tongSoLuong,
            'tongTheTich' => $tongTheTich,
        ];
    }

    /**
     * Thuật toán gợi ý vị trí lưu kho tự động:
     * - Tìm các vị trí trong kho có cùng loại hàng lưu trữ và trạng thái HOAT_DONG
     * - Sắp xếp theo sức chứa khả dụng giảm dần
     * - Tự động chia nhỏ số lượng vào các vị trí nếu 1 vị trí không đủ sức chứa
     */
    public function suggestPositions(string $maPhieuNhap, ?string $targetKho = null): array
    {
        $eval = $this->evaluateInboundRequest($maPhieuNhap, $targetKho);
        if (!$eval['valid']) {
            return [
                'valid'     => false,
                'errors'    => $eval['errors'],
                'positions' => [],
            ];
        }

        $req = $eval['request'];
        $khoId = $targetKho ?: $req['maKho'];
        $items = $req['items'] ?? [];

        $suggestedPositions = [];

        foreach ($items as $item) {
            $sku = $item['maSKU'];
            $loaiHang = $item['loaiHang'] ?? null;
            $neededQty = (float)$item['soLuong'];

            // Lấy danh sách vị trí khả dụng
            $availPositions = $this->repo->getAvailablePositionsForWarehouse($khoId, $loaiHang);

            if (empty($availPositions)) {
                return [
                    'valid'     => false,
                    'errors'    => [Messages::DP_ALT_10_1_NO_BIN],
                    'positions' => [],
                ];
            }

            $remainingToAllocate = $neededQty;

            foreach ($availPositions as $pos) {
                if ($remainingToAllocate <= 0) break;

                $cap = (float)$pos['sucChuaKhaDung'];
                if ($cap <= 0) continue;

                $allocQty = min($remainingToAllocate, $cap);

                $suggestedPositions[] = [
                    'maLo'            => $item['maLo'],
                    'maSKU'           => $sku,
                    'tenHang'         => $item['tenHang'],
                    'donViTinh'       => $item['donViTinh'],
                    'maViTri'         => $pos['maViTri'],
                    'maKe'            => $pos['maKe'],
                    'tang'            => $pos['tang'],
                    'oChua'           => $pos['oChua'],
                    'sucChuaKhaDung'  => $cap,
                    'soLuongDieuPhoi' => $allocQty,
                ];

                $remainingToAllocate -= $allocQty;
            }

            if ($remainingToAllocate > 0) {
                return [
                    'valid'     => false,
                    'errors'    => [Messages::DP_ALT_12_1_BIN_UNSUITABLE],
                    'positions' => [],
                ];
            }
        }

        return [
            'valid'     => true,
            'errors'    => [],
            'positions' => $suggestedPositions,
        ];
    }

    /**
     * Validate kế hoạch điều phối trước khi lưu
     */
    public function validateDispatchPlan(string $maPhieuNhap, string $targetKho, array $allocLines, string $maNhanVienThucHien): array
    {
        $eval = $this->evaluateInboundRequest($maPhieuNhap, $targetKho);
        if (!$eval['valid']) {
            return $eval;
        }

        $errors = [];

        if (empty($maNhanVienThucHien)) {
            $errors[] = 'Vui lòng chọn nhân viên kho (NVK) phụ trách thực hiện lệnh điều phối.';
        }

        if (empty($allocLines)) {
            $errors[] = 'Chưa chọn vị trí và số lượng điều phối nào.';
            return ['valid' => false, 'errors' => $errors];
        }

        $req = $eval['request'];
        $items = $req['items'] ?? [];
        $expectedTotalQty = (float)$req['tongSoLuong'];

        $actualTotalQty = 0.0;
        foreach ($allocLines as $line) {
            $qty = (float)($line['soLuongDieuPhoi'] ?? 0);
            if ($qty <= 0) {
                $errors[] = "Số lượng điều phối tại vị trí {$line['maViTri']} phải lớn hơn 0.";
                continue;
            }
            $actualTotalQty += $qty;
        }

        if (abs($actualTotalQty - $expectedTotalQty) > 0.001) {
            $errors[] = "Tổng số lượng điều phối ({$actualTotalQty}) không khớp với tổng số lượng phiếu nhập ({$expectedTotalQty}).";
        }

        // Kiểm tra tính phù hợp của các vị trí lưu trữ được chọn (Alt 12.1)
        $availPositions = $this->repo->getAvailablePositionsForWarehouse($targetKho);
        $availMap = array_column($availPositions, null, 'maViTri');

        foreach ($allocLines as $line) {
            $vtId = $line['maViTri'] ?? '';
            $qty = (float)($line['soLuongDieuPhoi'] ?? 0);
            if (!isset($availMap[$vtId]) || $qty > (float)$availMap[$vtId]['sucChuaKhaDung']) {
                $errors[] = Messages::DP_ALT_12_1_BIN_UNSUITABLE;
                break;
            }
        }

        return [
            'valid'  => empty($errors),
            'errors' => $errors,
        ];
    }
}
