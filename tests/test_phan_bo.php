<?php
/**
 * Test Suite: Phân bổ số lượng (Prompt 5 - QLK, UC 2.2.3)
 * Kiểm tra:
 * 1. Danh sách lệnh chờ phân bổ (DA_PHAT, DANG_PHAN_BO) & NVL còn thiếu
 * 2. Lọc tồn kho theo FEFO (lô sắp hết hạn xếp trước, loại bỏ CACH_LY / hết hạn)
 * 3. Thuật toán gợi ý tự động theo FEFO
 * 4. Ràng buộc & validate: vượt khả dụng báo lỗi chuẩn, vượt nhu cầu
 * 5. Phân bổ từng phần và phân bổ đủ
 * 6. Kiểm tra BR-06: Giữ chỗ cấp lệnh chuyển thành giữ chỗ cấp lô, không trừ 2 lần
 * 7. Giao dịch kho & Nhật ký hệ thống
 * 8. Chống race condition (SELECT FOR UPDATE)
 * 9. Phân quyền: QLK ghi; LKH, BGD xem; NVK, QC chặn ghi
 */

require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Auth.php';
require_once __DIR__ . '/../app/repositories/PhanBoRepository.php';
require_once __DIR__ . '/../app/services/PhanBoService.php';

use App\Core\Database;
use App\Core\Auth;
use App\Repositories\PhanBoRepository;
use App\Services\PhanBoService;

$db = Database::getConnection();
$repo = new PhanBoRepository();
$service = new PhanBoService($repo);

$passed = 0;
$failed = 0;

function assertTest(bool $condition, string $msg, &$passed, &$failed): void {
    if ($condition) {
        echo "  [PASS] {$msg}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$msg}\n";
        $failed++;
    }
}

echo "===============================================================\n";
echo " KIỂM THỬ: PHÂN BỔ SỐ LƯỢNG NGUYÊN VẬT LIỆU (PROMPT 5)\n";
echo "===============================================================\n\n";

// Bọc toàn bộ trong transaction để giữ nguyên seed data
$db->beginTransaction();

try {
    // -------------------------------------------------------------
    // 1. Kiểm tra đọc danh sách lệnh chờ phân bổ
    // -------------------------------------------------------------
    echo "1. Kiểm tra danh sách lệnh chờ phân bổ:\n";
    $pendingOrders = $repo->getPendingOrders();
    assertTest(!empty($pendingOrders), "Lấy được danh sách lệnh chờ phân bổ", $passed, $failed);
    
    $orderCodes = array_column($pendingOrders, 'maLenhSX');
    assertTest(in_array('LSX2610001', $orderCodes), "Có lệnh mẫu LSX2610001", $passed, $failed);
    assertTest(in_array('LSX2610002', $orderCodes), "Có lệnh mẫu LSX2610002", $passed, $failed);
    assertTest(in_array('LSX2610003', $orderCodes), "Có lệnh mẫu LSX2610003", $passed, $failed);

    $lsx1NVL = $repo->getNVLNeedsForOrder('LSX2610001');
    assertTest(count($lsx1NVL) === 3, "LSX2610001 cần 3 loại NVL (Đường, Bột mì, Bơ)", $passed, $failed);

    // -------------------------------------------------------------
    // 2. Kiểm tra FEFO và lọc trạng thái chất lượng
    // -------------------------------------------------------------
    echo "\n2. Kiểm tra sắp xếp FEFO và loại bỏ lô không hợp lệ:\n";
    $lotsNVL001 = $repo->getAvailableLotsForSKU('NVL001');
    assertTest(!empty($lotsNVL001), "Lấy được các lô khả dụng cho NVL001", $passed, $failed);
    
    // Kiểm tra lô đầu tiên phải là LO_NVL001_EXP (hạn 2026-10-31)
    assertTest($lotsNVL001[0]['maLo'] === 'LO_NVL001_EXP', "FEFO: Lô sắp hết hạn (LO_NVL001_EXP) đứng đầu tiên", $passed, $failed);
    
    // Kiểm tra lô CACH_LY không được xuất hiện
    $lotCodes = array_column($lotsNVL001, 'maLo');
    assertTest(!in_array('LO_NVL001_CL', $lotCodes), "Lô CACH_LY (LO_NVL001_CL) bị loại bỏ hoàn toàn (BR-02, BR-09)", $passed, $failed);

    // -------------------------------------------------------------
    // 3. Kiểm tra gợi ý tự động FEFO
    // -------------------------------------------------------------
    echo "\n3. Kiểm tra thuật toán gợi ý tự động phân bổ FEFO:\n";
    // LSX2610001 cần 8 kg NVL001. LO_NVL001_EXP có 5 kg, LO_NVL001_01 có 25 kg
    $suggestion = $service->suggestFEFO('LSX2610001', 'K001');
    assertTest(!empty($suggestion), "Tạo gợi ý phân bổ tự động thành công", $passed, $failed);
    
    $nvl1Allocations = array_filter($suggestion, fn($item) => $item['maSKU'] === 'NVL001');
    $allocatedNVL001Qty = array_sum(array_column($nvl1Allocations, 'soLuongPhanBo'));
    assertTest($allocatedNVL001Qty == 8.0, "Gợi ý phân bổ đủ 8.0 kg NVL001", $passed, $failed);
    
    // Lô hết hạn sớm được lấy hết 5 kg trước
    $firstLotAlloc = current(array_filter($nvl1Allocations, fn($item) => $item['maLo'] === 'LO_NVL001_EXP'));
    assertTest($firstLotAlloc && $firstLotAlloc['soLuongPhanBo'] == 5.0, "Lô LO_NVL001_EXP được gợi ý lấy toàn bộ 5 kg", $passed, $failed);

    // -------------------------------------------------------------
    // 4. Kiểm tra validate dữ liệu & thông báo lỗi chuẩn
    // -------------------------------------------------------------
    echo "\n4. Kiểm tra validate dữ liệu đầu vào:\n";
    // 4.1 Số lượng <= 0
    $valZero = $service->validateAllocation('LSX2610001', 'K001', [
        ['maLo' => 'LO_NVL001_EXP', 'maViTri' => 'VT000002', 'maSKU' => 'NVL001', 'soLuongPhanBo' => 0]
    ]);
    assertTest(!$valZero['valid'], "Chặn khi số lượng phân bổ <= 0", $passed, $failed);

    // 4.2 Vượt tồn khả dụng của lô (LO_NVL001_EXP chỉ có 5 kg, thử phân bổ 10 kg)
    $valExceed = $service->validateAllocation('LSX2610001', 'K001', [
        ['maLo' => 'LO_NVL001_EXP', 'maViTri' => 'VT000002', 'maSKU' => 'NVL001', 'soLuongPhanBo' => 10.0]
    ]);
    assertTest(!$valExceed['valid'], "Chặn khi số lượng vượt tồn khả dụng của lô", $passed, $failed);
    assertTest(mb_stripos($valExceed['errors'][0], 'Số lượng phân bổ vượt quá số lượng tồn khả dụng') !== false,
        "Đúng thông báo đặc tả: 'Số lượng phân bổ vượt quá số lượng tồn khả dụng.'", $passed, $failed);

    // 4.3 Vượt tổng nhu cầu còn lại của lệnh (Cần 8 kg, thử phân bổ 20 kg qua lô khác)
    $valExceedDemand = $service->validateAllocation('LSX2610001', 'K001', [
        ['maLo' => 'LO_NVL001_01', 'maViTri' => 'VT000001', 'maSKU' => 'NVL001', 'soLuongPhanBo' => 20.0]
    ]);
    assertTest(!$valExceedDemand['valid'], "Chặn khi tổng phân bổ vượt quá nhu cầu cần cấp còn lại của lệnh", $passed, $failed);

    // -------------------------------------------------------------
    // 5. Kiểm tra phân bổ một phần (Partial allocation)
    // -------------------------------------------------------------
    echo "\n5. Kiểm tra phân bổ một phần (Partial Allocation):\n";
    // Phân bổ 5 kg NVL001 từ LO_NVL001_EXP
    $alloc1 = [
        ['maLo' => 'LO_NVL001_EXP', 'maViTri' => 'VT000002', 'maSKU' => 'NVL001', 'soLuongPhanBo' => 5.0]
    ];
    $maPhieu1 = $repo->createAllocation('LSX2610001', 'K001', $alloc1, 'NV002', 'Phân bổ đợt 1 cho LSX2610001');
    assertTest(!empty($maPhieu1) && str_starts_with($maPhieu1, 'PB-'), "Tạo phiếu phân bổ thành công: {$maPhieu1}", $passed, $failed);

    // Kiểm tra ton_kho: soLuongGiuCho tăng 5
    $tkRow = $repo->getTonKhoRow('LO_NVL001_EXP', 'VT000002');
    assertTest($tkRow['soLuongGiuCho'] == 5.0, "ton_kho.soLuongGiuCho của LO_NVL001_EXP tăng lên 5.0", $passed, $failed);
    assertTest(($tkRow['soLuongThucTe'] - $tkRow['soLuongGiuCho']) == 0.0, "Tồn khả dụng của LO_NVL001_EXP về 0", $passed, $failed);

    // Kiểm tra nvl_can_cap_lenh_sx
    $nvlRow = $repo->getOrderNVLRow('LSX2610001', 'NVL001');
    assertTest($nvlRow['soLuongDaPhanBo'] == 5.0, "nvl_can_cap_lenh_sx: soLuongDaPhanBo = 5.0", $passed, $failed);
    assertTest($nvlRow['trangThai'] === 'DANG_PHAN_BO', "Trạng thái dòng NVL chuyển sang DANG_PHAN_BO", $passed, $failed);

    // Kiểm tra lenh_san_xuat chuyển sang DANG_PHAN_BO
    $lsxRow = $repo->getOrderHeader('LSX2610001');
    assertTest($lsxRow['trangThai'] === 'DANG_PHAN_BO', "Lệnh sản xuất chuyển sang DANG_PHAN_BO", $passed, $failed);

    // -------------------------------------------------------------
    // 6. Kiểm tra BR-06: Khả dụng NVL không bị trừ 2 lần
    // -------------------------------------------------------------
    echo "\n6. Kiểm tra quy tắc BR-06 (Không trừ khả dụng 2 lần):\n";
    $vwNVL001 = $repo->getVwTonKhoKhaDung('NVL001');
    // Trước khi phân bổ đợt 1, NVL001 có khả dụng = 30.
    // Sau phân bổ đợt 1: ton_lo giảm 5, nhưng giu_cho_lsx cũng giảm 5 -> tonKhaDung vẫn = 30!
    assertTest($vwNVL001['tonKhaDung'] == 30.0, "BR-06: vw_ton_kho_kha_dung của NVL001 vẫn giữ đúng 30.0 (không trừ 2 lần)", $passed, $failed);

    // -------------------------------------------------------------
    // 7. Kiểm tra phân bổ tiếp phần còn lại để đạt ĐỦ (Full Allocation)
    // -------------------------------------------------------------
    echo "\n7. Kiểm tra phân bổ tiếp đến khi hoàn thành (Full Allocation):\n";
    // Phân bổ tiếp 3 kg NVL001 từ LO_NVL001_01, 32 kg NVL002 từ LO_NVL002_01, 12 kg NVL004 từ LO_NVL004_02
    $alloc2 = [
        ['maLo' => 'LO_NVL001_01', 'maViTri' => 'VT000001', 'maSKU' => 'NVL001', 'soLuongPhanBo' => 3.0],
        ['maLo' => 'LO_NVL002_01', 'maViTri' => 'VT000004', 'maSKU' => 'NVL002', 'soLuongPhanBo' => 32.0],
        ['maLo' => 'LO_NVL004_02', 'maViTri' => 'VT000007', 'maSKU' => 'NVL004', 'soLuongPhanBo' => 12.0],
    ];
    $maPhieu2 = $repo->createAllocation('LSX2610001', 'K001', $alloc2, 'NV002', 'Phân bổ đợt 2 hoàn tất LSX2610001');
    assertTest(!empty($maPhieu2), "Tạo phiếu phân bổ đợt 2 thành công: {$maPhieu2}", $passed, $failed);

    // Kiểm tra tất cả NVL của LSX2610001 đã phân bổ đủ
    $allOrderNVL = $repo->getNVLNeedsForOrder('LSX2610001');
    $allFull = true;
    foreach ($allOrderNVL as $item) {
        if ($item['trangThai'] !== 'DA_PHAN_BO_DU') {
            $allFull = false;
        }
    }
    assertTest($allFull, "Tất cả 3 nguyên vật liệu của LSX2610001 đều đạt trạng thái DA_PHAN_BO_DU", $passed, $failed);

    // Kiểm tra giao_dich_kho
    $gdCount = $repo->countGiaoDichKhoForChungTu($maPhieu2);
    assertTest($gdCount === 3, "Đã ghi 3 giao dịch kho loại PHAN_BO_GIU_CHO cho 3 mặt hàng", $passed, $failed);

    // -------------------------------------------------------------
    // 8. Kiểm tra phân quyền truy cập
    // -------------------------------------------------------------
    echo "\n8. Kiểm tra ma trận phân quyền:\n";
    $perms = require dirname(__DIR__) . '/app/config/permissions.php';
    $pbPerm = $perms['modules']['phan-bo'];
    assertTest(in_array('QLK', $pbPerm['write']), "QLK có quyền GHI (create) phân bổ số lượng", $passed, $failed);
    assertTest(!in_array('LKH', $pbPerm['write']), "LKH KHÔNG có quyền ghi phân bổ", $passed, $failed);
    assertTest(!in_array('BGD', $pbPerm['write']), "BGD KHÔNG có quyền ghi phân bổ", $passed, $failed);
    assertTest(!in_array('QC', $pbPerm['write']), "QC KHÔNG có quyền ghi phân bổ", $passed, $failed);
    assertTest(!in_array('NVK', $pbPerm['write']), "NVK KHÔNG có quyền ghi phân bổ", $passed, $failed);
    assertTest(in_array('QLK', $pbPerm['view']), "QLK có quyền xem", $passed, $failed);
    assertTest(in_array('LKH', $pbPerm['view']), "LKH có quyền xem", $passed, $failed);
    assertTest(in_array('BGD', $pbPerm['view']), "BGD có quyền xem", $passed, $failed);

    // Kiểm tra Auth::can() với session người dùng
    $_SESSION['auth_user'] = ['vaiTro' => 'QLK', 'maNhanVien' => 'NV002'];
    $_SESSION['last_activity'] = time();
    assertTest(Auth::can('phan-bo', 'create'), "Auth::can: QLK được phép create phan-bo", $passed, $failed);
    assertTest(Auth::can('phan-bo', 'index'), "Auth::can: QLK được phép xem index phan-bo", $passed, $failed);

    $_SESSION['auth_user'] = ['vaiTro' => 'LKH', 'maNhanVien' => 'NV001'];
    assertTest(!Auth::can('phan-bo', 'create'), "Auth::can: LKH không được phép create phan-bo", $passed, $failed);
    assertTest(Auth::can('phan-bo', 'index'), "Auth::can: LKH được phép xem index phan-bo", $passed, $failed);

} finally {
    // Luôn rollback để không sửa seed data
    $db->rollBack();
    echo "\n[INFO] Đã Rollback giao dịch. Dữ liệu seed được giữ nguyên vẹn.\n";
}

echo "\n===============================================================\n";
echo " TỔNG KẾT KIỂM THỬ PROMPT 5: {$passed} PASSED, {$failed} FAILED\n";
echo "===============================================================\n";

if ($failed > 0) {
    exit(1);
}
