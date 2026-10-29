<?php
/**
 * Test Suite: Điều phối nhập kho (Prompt 6 - QLK, NVK, UC 2.5.3)
 * Kiểm tra:
 * 1. Đọc danh sách phiếu nhập kho chờ điều phối (CHO_DIEU_PHOI)
 * 2. Case YCN26100004: Chặn lô không đạt QC (CACH_LY / CHO_KIEM)
 * 3. Case YCN26100008: Chặn lô đã hết hạn sử dụng
 * 4. Case YCN26100010: Chặn kho đang bảo trì (K009)
 * 5. Case YCN26100003: Chặn khi kho không đủ sức chứa
 * 6. Case YCN26100005: Chặn chọn vị trí đang bảo trì
 * 7. Thuật toán gợi ý vị trí lưu kho tự động
 * 8. Case YCN26100001: Điều phối thành công chia 2 vị trí (100 + 100)
 * 9. Cập nhật sucChuaDaHenNhap của kho và vị trí
 * 10. Chức năng NVK: Xem lệnh được giao và xác nhận "Đã nhận lệnh"
 * 11. Phân quyền: QLK ghi dieu-phoi; BGD, QC xem; NVK xác nhận lenh-dieu-phoi
 */

require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Auth.php';
require_once __DIR__ . '/../app/repositories/DieuPhoiRepository.php';
require_once __DIR__ . '/../app/services/DieuPhoiService.php';

use App\Core\Database;
use App\Core\Auth;
use App\Config\Messages;
use App\Repositories\DieuPhoiRepository;
use App\Services\DieuPhoiService;

$db = Database::getConnection();
$repo = new DieuPhoiRepository();
$service = new DieuPhoiService($repo);

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
echo " KIỂM THỬ: ĐIỀU PHỐI NHẬP KHO & LỆNH ĐIỀU PHỐI (PROMPT 6)\n";
echo "===============================================================\n\n";

// Bọc toàn bộ trong transaction để giữ nguyên seed data
$db->beginTransaction();

try {
    // -------------------------------------------------------------
    // 1. Kiểm tra danh sách phiếu nhập kho chờ điều phối
    // -------------------------------------------------------------
    echo "1. Kiểm tra danh sách phiếu nhập kho chờ điều phối:\n";
    $pendingList = $repo->getPendingInboundRequests();
    assertTest(!empty($pendingList), "Lấy được danh sách phiếu nhập CHO_DIEU_PHOI", $passed, $failed);
    $reqCodes = array_column($pendingList, 'maPhieuNhap');
    assertTest(in_array('YCN26100002', $reqCodes), "Có phiếu YCN26100002", $passed, $failed);
    assertTest(in_array('YCN26100003', $reqCodes), "Có phiếu YCN26100003", $passed, $failed);

    // -------------------------------------------------------------
    // 2. Kiểm tra Case YCN26100004: Chặn khi không đạt QC
    // -------------------------------------------------------------
    echo "\n2. Kiểm tra Case YCN26100004: Lô không đạt QC:\n";
    $evalQC = $service->evaluateInboundRequest('YCN26100004');
    assertTest(!$evalQC['valid'], "YCN26100004 bị chặn điều phối", $passed, $failed);
    assertTest(mb_stripos($evalQC['errors'][0], 'kiểm tra chất lượng') !== false || mb_stripos($evalQC['errors'][0], 'QC') !== false || mb_stripos($evalQC['errors'][0], 'cách ly') !== false,
        "Nêu rõ lý do: Lô chưa đạt kiểm tra chất lượng QA/QC", $passed, $failed);

    // -------------------------------------------------------------
    // 3. Kiểm tra Case YCN26100008: Chặn khi lô hết hạn
    // -------------------------------------------------------------
    echo "\n3. Kiểm tra Case YCN26100008: Lô hết hạn sử dụng:\n";
    $evalExp = $service->evaluateInboundRequest('YCN26100008');
    assertTest(!$evalExp['valid'], "YCN26100008 bị chặn do hết hạn sử dụng", $passed, $failed);
    $expErrorStr = implode(' ', $evalExp['errors']);
    assertTest(mb_stripos($expErrorStr, 'hết hạn') !== false,
        "Nêu rõ lý do: Lô hàng đã hết hạn sử dụng", $passed, $failed);

    // -------------------------------------------------------------
    // 4. Kiểm tra Case YCN26100010: Chặn khi kho bảo trì (Alt 5.1)
    // -------------------------------------------------------------
    echo "\n4. Kiểm tra Case YCN26100010: Kho đang bảo trì (K009):\n";
    $evalWhMaint = $service->evaluateInboundRequest('YCN26100010');
    assertTest(!$evalWhMaint['valid'], "YCN26100010 bị chặn do kho K009 bảo trì", $passed, $failed);
    assertTest(in_array(Messages::DP_ALT_5_1_NO_WH, $evalWhMaint['errors']),
        "Đúng thông báo Alt 5.1: " . Messages::DP_ALT_5_1_NO_WH, $passed, $failed);

    // -------------------------------------------------------------
    // 5. Kiểm tra Case YCN26100003: Chặn khi kho không đủ sức chứa (Alt 9.1)
    // -------------------------------------------------------------
    echo "\n5. Kiểm tra Case YCN26100003: Kho không đủ sức chứa:\n";
    $evalCap = $service->evaluateInboundRequest('YCN26100003');
    assertTest(!$evalCap['valid'], "YCN26100003 bị chặn do không đủ sức chứa", $passed, $failed);
    assertTest(in_array(Messages::DP_ALT_9_1_CAPACITY, $evalCap['errors']),
        "Đúng thông báo Alt 9.1: " . Messages::DP_ALT_9_1_CAPACITY, $passed, $failed);

    // -------------------------------------------------------------
    // 5.1. Kiểm tra Alt 5.1, Alt 10.1, Alt 12.1 theo đặc tả:
    // -------------------------------------------------------------
    echo "\n5.1. Kiểm tra Alt 5.1, Alt 10.1, Alt 12.1:\n";
    // Alt 5.1: Không có kho tiếp nhận phù hợp
    $evalAlt51 = $service->evaluateInboundRequest('YCN26100002', 'K_KHONG_TON_TAI');
    assertTest(!$evalAlt51['valid'] && in_array(Messages::DP_ALT_5_1_NO_WH, $evalAlt51['errors']),
        "Đúng thông báo Alt 5.1 khi kho không tồn tại: " . Messages::DP_ALT_5_1_NO_WH, $passed, $failed);

    // Alt 10.1: Không có vị trí lưu trữ phù hợp (K005 là kho thành phẩm, không có ô chứa NVL)
    $evalAlt101 = $service->suggestPositions('YCN26100002', 'K005');
    assertTest(!$evalAlt101['valid'] && in_array(Messages::DP_ALT_10_1_NO_BIN, $evalAlt101['errors']),
        "Đúng thông báo Alt 10.1: " . Messages::DP_ALT_10_1_NO_BIN, $passed, $failed);

    // Alt 12.1: Vị trí lưu trữ không phù hợp (VT000008 sức chứa chỉ còn 20 kg nhưng điều phối 60 kg)
    $invalidLines = [
        ['maLo' => 'LO_NVL002_02', 'maSKU' => 'NVL002', 'maViTri' => 'VT000008', 'soLuongDieuPhoi' => 60.0],
    ];
    $evalAlt121 = $service->validateDispatchPlan('YCN26100009', 'K002', $invalidLines, 'NV0001');
    assertTest(!$evalAlt121['valid'] && in_array(Messages::DP_ALT_12_1_BIN_UNSUITABLE, $evalAlt121['errors']),
        "Đúng thông báo Alt 12.1: " . Messages::DP_ALT_12_1_BIN_UNSUITABLE, $passed, $failed);

    // -------------------------------------------------------------
    // 6. Kiểm tra Case YCN26100005: Loại bỏ vị trí bảo trì
    // -------------------------------------------------------------
    echo "\n6. Kiểm tra vị trí lưu kho & loại bỏ vị trí bảo trì:\n";
    $availPositionsK004 = $repo->getAvailablePositionsForWarehouse('K004');
    $maintPos = array_filter($availPositionsK004, fn($p) => $p['trangThai'] === 'BAO_TRI');
    assertTest(empty($maintPos), "Vị trí trạng thái BAO_TRI bị loại bỏ khỏi danh sách lựa chọn", $passed, $failed);

    // -------------------------------------------------------------
    // 7. Thuật toán gợi ý vị trí lưu kho
    // -------------------------------------------------------------
    echo "\n7. Kiểm tra thuật toán gợi ý vị trí lưu kho tự động:\n";
    // Mở tạm YCN26100001 thành CHO_DIEU_PHOI trong transaction test
    $db->prepare("UPDATE phieu_nhap_kho SET trangThai = 'CHO_DIEU_PHOI' WHERE maPhieuNhap = 'YCN26100001'")->execute();
    
    $suggest = $service->suggestPositions('YCN26100001');
    assertTest(!empty($suggest) && !empty($suggest['positions']), "Tạo gợi ý vị trí cho YCN26100001 thành công", $passed, $failed);
    $totalSuggested = array_sum(array_column($suggest['positions'], 'soLuongDieuPhoi'));
    assertTest($totalSuggested == 200.0, "Tổng số lượng gợi ý đủ 200 thùng bánh quy", $passed, $failed);

    // -------------------------------------------------------------
    // 8. Thực hiện điều phối Case YCN26100001: chia 2 vị trí (100 + 100)
    // -------------------------------------------------------------
    echo "\n8. Thực hiện điều phối YCN26100001 chia 2 vị trí (100 + 100):\n";
    $allocLines = [
        ['maLo' => 'LO_BK_QB_000', 'maSKU' => 'BK-QB-001', 'maViTri' => 'VT000022', 'soLuongDieuPhoi' => 100.0],
        ['maLo' => 'LO_BK_QB_000', 'maSKU' => 'BK-QB-001', 'maViTri' => 'VT000023', 'soLuongDieuPhoi' => 100.0],
    ];

    // Lấy sucChuaDaHenNhap ban đầu của K006
    $whBefore = $repo->getWarehouse('K006');
    $henNhapWhBefore = (float)$whBefore['sucChuaDaHenNhap'];

    $maLDP = $repo->createDispatch('YCN26100001', 'K006', $allocLines, 'NV002', 'NV0001', 'Điều phối ca sáng 200 thùng bánh quy');
    assertTest(!empty($maLDP) && str_starts_with($maLDP, 'LDP-'), "Phát lệnh điều phối thành công với mã: {$maLDP}", $passed, $failed);

    // Kiểm tra trạng thái phieu_nhap_kho chuyển thành DA_DIEU_PHOI
    $pnRow = $repo->getInboundRequest('YCN26100001');
    assertTest($pnRow['trangThai'] === 'DA_DIEU_PHOI', "Phiếu nhập kho chuyển sang DA_DIEU_PHOI", $passed, $failed);

    // Kiểm tra lệnh điều phối
    $ldpRow = $repo->getDispatchHeader($maLDP);
    assertTest($ldpRow['trangThai'] === 'CHO_THUC_HIEN', "Lệnh điều phối ở trạng thái CHO_THUC_HIEN", $passed, $failed);
    assertTest($ldpRow['maNhanVienThucHien'] === 'NV0001', "Giao cho nhân viên kho NV0001", $passed, $failed);

    // Kiểm tra cập nhật sucChuaDaHenNhap
    $whAfter = $repo->getWarehouse('K006');
    $henNhapWhAfter = (float)$whAfter['sucChuaDaHenNhap'];
    assertTest($henNhapWhAfter > $henNhapWhBefore, "Cộng sucChuaDaHenNhap của kho tiếp nhận K006", $passed, $failed);

    // -------------------------------------------------------------
    // 9. Kiểm tra NVK: Xem lệnh được giao & Xác nhận "Đã nhận lệnh"
    // -------------------------------------------------------------
    echo "\n9. Kiểm tra luồng thao tác NVK (Xác nhận đã nhận lệnh):\n";
    $nvkOrders = $repo->getAssignedOrdersForWorker('NV0001');
    assertTest(!empty($nvkOrders), "NVK (NV0001) xem được danh sách lệnh điều phối được giao", $passed, $failed);
    $nvkOrderIds = array_column($nvkOrders, 'maLenhDieuPhoi');
    assertTest(in_array($maLDP, $nvkOrderIds), "Lệnh {$maLDP} xuất hiện trong danh sách của NV0001", $passed, $failed);

    // NVK bấm xác nhận đã nhận lệnh
    $confirmResult = $repo->confirmReceiptByWorker($maLDP, 'NV0001');
    assertTest($confirmResult, "NVK xác nhận 'Đã nhận lệnh' thành công", $passed, $failed);

    $ldpAfterConfirm = $repo->getDispatchHeader($maLDP);
    assertTest($ldpAfterConfirm['trangThai'] === 'DANG_THUC_HIEN', "Trạng thái lệnh chuyển sang DANG_THUC_HIEN", $passed, $failed);

    // -------------------------------------------------------------
    // 10. Kiểm tra phân quyền truy cập
    // -------------------------------------------------------------
    echo "\n10. Kiểm tra ma trận phân quyền:\n";
    $perms = require dirname(__DIR__) . '/app/config/permissions.php';
    $dpPerm = $perms['modules']['dieu-phoi'];
    $ldpPerm = $perms['modules']['lenh-dieu-phoi'];

    assertTest(in_array('QLK', $dpPerm['write']), "QLK có quyền GHI điều phối nhập kho", $passed, $failed);
    assertTest(!in_array('QC', $dpPerm['write']) && !in_array('BGD', $dpPerm['write']), "QC và BGD không có quyền ghi điều phối", $passed, $failed);
    assertTest(in_array('QC', $dpPerm['view']) && in_array('BGD', $dpPerm['view']) && in_array('QLK', $dpPerm['view']), "QC, BGD, QLK có quyền xem điều phối", $passed, $failed);

    assertTest(in_array('NVK', $ldpPerm['write']), "NVK có quyền GHI (xác nhận) lệnh điều phối", $passed, $failed);
    assertTest(in_array('NVK', $ldpPerm['view']) && in_array('QLK', $ldpPerm['view']), "NVK và QLK có quyền xem lệnh điều phối", $passed, $failed);

} finally {
    // Luôn rollback để không sửa seed data
    $db->rollBack();
    echo "\n[INFO] Đã Rollback giao dịch. Dữ liệu seed được giữ nguyên vẹn.\n";
}

echo "\n===============================================================\n";
echo " TỔNG KẾT KIỂM THỬ PROMPT 6: {$passed} PASSED, {$failed} FAILED\n";
echo "===============================================================\n";

if ($failed > 0) {
    exit(1);
}
