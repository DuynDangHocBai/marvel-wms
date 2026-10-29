<?php

namespace App\Services;

use App\Config\Messages;
use App\Repositories\DeNghiMuaRepository;

/**
 * Service: Xử lý nghiệp vụ tính toán và thẩm định đề nghị mua NVL (Prompt 3 - LKH)
 */
class DeNghiMuaService
{
    private DeNghiMuaRepository $repo;

    public function __construct(DeNghiMuaRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Tính toán bảng nhu cầu NVL từ Kế hoạch sản xuất đã duyệt theo công thức:
     * nhuCauSX = SUM(soLuongSanXuat * soLuongDinhMuc)
     * soLuongDeXuat = max(0, nhuCauSX + tonToiThieu - tonKhaDung - hangDangVe)
     */
    public function calculateProposalFromPlan(string $maKeHoach): array
    {
        $plan = $this->repo->getPlanHeader($maKeHoach);

        if (!$plan) {
            return [
                'valid'  => false,
                'errors' => ["Không tìm thấy kế hoạch sản xuất {$maKeHoach}."],
                'items'  => [],
            ];
        }

        if ($plan['trangThai'] !== 'DA_DUYET') {
            return [
                'valid'  => false,
                'errors' => ["Kế hoạch sản xuất {$maKeHoach} chưa được phê duyệt (Trạng thái: {$plan['trangThai']})."],
                'items'  => [],
            ];
        }

        $items = $this->repo->getNVLDemandForPlan($maKeHoach);
        foreach ($items as &$it) {
            $it['soLuongMua'] = (float)$it['soLuongDeXuat'];
            $it['thanhTien'] = (float)$it['soLuongMua'] * (float)($it['giaNhap'] ?? 0);
            if ((float)$it['soLuongDeXuat'] <= 0) {
                $it['canhBaoTonDu'] = Messages::DNM_EXC_5_1_NOT_NEEDED;
            }
        }
        unset($it);

        return [
            'valid'  => true,
            'errors' => [],
            'plan'   => $plan,
            'items'  => $items,
        ];
    }

    /**
     * Thẩm định 4 tiêu chí hợp lệ của form lập đề nghị mua:
     * 1. Kế hoạch phải ở trạng thái DA_DUYET
     * 2. Ngày cần hàng >= hôm nay
     * 3. Số lượng mua > 0 cho ít nhất một mặt hàng
     * 4. NVL ngoài kế hoạch bắt buộc phải có lý do
     */
    public function validateProposalForm(array $formData): array
    {
        $errors = [];

        // 1. Kiểm tra kế hoạch
        $maKeHoach = $formData['maKeHoach'] ?? '';
        if (!empty($maKeHoach)) {
            $plan = $this->repo->getPlanHeader($maKeHoach);
            if (!$plan) {
                $errors[] = "Không tìm thấy kế hoạch sản xuất {$maKeHoach}.";
            } elseif ($plan['trangThai'] !== 'DA_DUYET') {
                $errors[] = "Kế hoạch sản xuất {$maKeHoach} chưa được phê duyệt.";
            }
        }

        // 2. Kiểm tra ngày cần hàng
        $ngayCanHang = $formData['ngayCanHang'] ?? '';
        $dateInvalid = false;
        if (empty($ngayCanHang)) {
            $dateInvalid = true;
        } else {
            $canDate = strtotime($ngayCanHang);
            $currDate = strtotime(date('Y-m-d'));
            if ($canDate < $currDate) {
                $dateInvalid = true;
            }
        }

        // 3. Kiểm tra danh sách mặt hàng
        $items = $formData['items'] ?? [];
        $hasInvalidQty = empty($items);
        $validCount = 0;
        $hasNonPlanWithoutReason = false;
        $globalReason = trim($formData['lyDo'] ?? '');

        foreach ($items as $it) {
            $qty = isset($it['soLuongMua']) ? (float)$it['soLuongMua'] : 0.0;
            if ($qty <= 0) {
                $hasInvalidQty = true;
                continue;
            }

            $validCount++;

            // Kiểm tra hàng ngoài kế hoạch
            if (!empty($it['ngoaiKeHoach'])) {
                $lineReason = trim($it['lyDoRieng'] ?? '');
                if (empty($lineReason) && empty($globalReason)) {
                    $hasNonPlanWithoutReason = true;
                }
            }
        }

        if ($validCount === 0) {
            $hasInvalidQty = true;
        }

        // Exc 8.1: Thiếu thông tin bắt buộc hoặc dữ liệu không hợp lệ
        if ($dateInvalid || $hasInvalidQty) {
            $errors[] = Messages::DNM_EXC_8_1_INVALID;
        }

        if ($hasNonPlanWithoutReason) {
            $errors[] = 'Vui lòng nhập lý do giải trình khi đề xuất mua nguyên vật liệu ngoài kế hoạch.';
        }

        return [
            'valid'  => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Hủy đề nghị mua NVL: LKH tự hủy khi đề nghị còn ở trạng thái CHO_DUYET.
     * Chuyển trạng thái sang DA_HUY (thay vì TU_CHOI dành cho BGD từ chối).
     */
    public function cancelProposal(string $maDeNghi, string $maNhanVien, string $lyDoHuy = '', ?int $maTaiKhoan = null): array
    {
        $proposal = $this->repo->getProposalDetail($maDeNghi);
        if (!$proposal) {
            return [
                'success' => false,
                'error'   => "Không tìm thấy đề nghị mua {$maDeNghi}.",
            ];
        }

        if ($proposal['trangThai'] !== 'CHO_DUYET') {
            return [
                'success' => false,
                'error'   => "Không thể hủy đề nghị {$maDeNghi} vì không ở trạng thái Chờ duyệt (Trạng thái hiện tại: {$proposal['trangThai']}).",
            ];
        }

        $ok = $this->repo->cancelProposal($maDeNghi, $maNhanVien, $lyDoHuy, $maTaiKhoan);
        return [
            'success' => $ok,
            'error'   => $ok ? null : "Lỗi khi cập nhật trạng thái hủy đề nghị {$maDeNghi}.",
        ];
    }
}

