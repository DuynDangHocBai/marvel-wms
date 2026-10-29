<?php
/**
 * Test Suite: Lập đề nghị mua nguyên vật liệu (Prompt 3 - LKH, UC 2.3.3)
 * Kiểm tra:
 * 1. Đọc danh sách kế hoạch sản xuất đã duyệt (DA_DUYET)
 * 2. Công thức tính toán nhu cầu NVL chuẩn xác:
 *    nhuCauSX = SUM(soLuongSanXuat * soLuongDinhMuc)
 *    soLuongDeXuat = max(0, nhuCauSX + tonToiThieu - tonKhaDung - hangDangVe)
 * 3. Test case KHSX004:
 *    NVL001 = 5 kg
 *    NVL005 = 16 kg
 *    NVL006 = 38 kg
 * 4. Kiểm tra hợp lệ:
 *    - Chặn kế hoạch chưa duyệt (KHSX002)
 *    - Chặn ngày cần hàng trong quá khứ
 *    - Chặn số lượng mua <= 0 hoặc rỗng
 *    - Bắt buộc nhập lý do khi có NVL ngoài kế hoạch (ngoaiKeHoach = 1)
 * 5. Lưu đề nghị thành công: mã DNM-YYYYMMDD-NNNN, trạng thái CHO_DUYET, tính tổng tiền dự toán
 * 6. Hủy đề nghị khi còn CHO_DUYET
 * 7. Phân quyền: LKH có quyền GHI; QLK và BGD chỉ xem; QLK vào create bị chặn 403
 */

require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Auth.php';
require_once __DIR__ . '/../app/repositories/DeNghiMuaRepository.php';
require_once __DIR__ . '/../app/services/DeNghiMuaService.php';

use App\Core\Database;
use App\Core\Auth;
use App\Config\Messages;
use App\Repositories\DeNghiMuaRepository;
use App\Services\DeNghiMuaService;

$db = Database::getConnection();
$repo = new DeNghiMuaRepository();
$service = new DeNghiMuaService($repo);

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
echo " KIỂM THỬ: LẬP ĐỀ NGHỊ MUA NGUYÊN VẬT LIỆU (PROMPT 3)\n";
echo "===============================================================\n\n";

// Bọc toàn bộ trong transaction để giữ nguyên seed data
$db->beginTransaction();

try {
    // -------------------------------------------------------------
    // 1. Kiểm tra danh sách kế hoạch đã duyệt
    // -------------------------------------------------------------
    echo "1. Kiểm tra danh sách kế hoạch sản xuất đã duyệt:\n";
    $approvedPlans = $repo->getApprovedPlans();
    assertTest(!empty($approvedPlans), "Lấy được danh sách kế hoạch DA_DUYET", $passed, $failed);
    $planIds = array_column($approvedPlans, 'maKeHoach');
    assertTest(in_array('KHSX004', $planIds), "Kế hoạch KHSX004 có trong danh sách đã duyệt", $passed, $failed);
    assertTest(!in_array('KHSX002', $planIds), "Kế hoạch chưa duyệt (KHSX002) bị loại bỏ khỏi danh sách chọn", $passed, $failed);

    // -------------------------------------------------------------
    // 2. Kiểm tra công thức tính và Test Case KHSX004
    // -------------------------------------------------------------
    echo "\n2. Kiểm tra công thức tính nhu cầu mua & Test Case KHSX004:\n";
    $calcResult = $service->calculateProposalFromPlan('KHSX004');
    assertTest(!empty($calcResult['items']), "Tính toán nhu cầu NVL cho KHSX004 thành công", $passed, $failed);
    
    $itemsBySku = [];
    foreach ($calcResult['items'] as $it) {
        $itemsBySku[$it['maSKU']] = $it;
    }

    // Kiểm tra NVL001 (Đường): nhuCauSX = 25, tonKhaDung = 30, tonToiThieu = 20, hangDangVe = 10 -> soLuongDeXuat = 5
    assertTest(isset($itemsBySku['NVL001']), "Có mặt hàng NVL001 trong danh sách tính", $passed, $failed);
    assertTest($itemsBySku['NVL001']['nhuCauSX'] == 25.0, "NVL001: nhuCauSX = 25.0 kg", $passed, $failed);
    assertTest($itemsBySku['NVL001']['tonKhaDung'] == 30.0, "NVL001: tonKhaDung = 30.0 kg", $passed, $failed);
    assertTest($itemsBySku['NVL001']['tonToiThieu'] == 20.0, "NVL001: tonToiThieu = 20.0 kg", $passed, $failed);
    assertTest($itemsBySku['NVL001']['hangDangVe'] == 10.0, "NVL001: hangDangVe = 10.0 kg", $passed, $failed);
    assertTest($itemsBySku['NVL001']['soLuongDeXuat'] == 5.0, "NVL001: soLuongDeXuat chuẩn 5.0 kg", $passed, $failed);

    // Kiểm tra NVL005 (Sữa bột): nhuCauSX = 20, tonKhaDung = 12, tonToiThieu = 8, hangDangVe = 0 -> soLuongDeXuat = 16
    assertTest(isset($itemsBySku['NVL005']), "Có mặt hàng NVL005 trong danh sách tính", $passed, $failed);
    assertTest($itemsBySku['NVL005']['soLuongDeXuat'] == 16.0, "NVL005: soLuongDeXuat chuẩn 16.0 kg", $passed, $failed);

    // Kiểm tra NVL006 (Chocolate): nhuCauSX = 48, tonKhaDung = 20, tonToiThieu = 10, hangDangVe = 0 -> soLuongDeXuat = 38
    assertTest(isset($itemsBySku['NVL006']), "Có mặt hàng NVL006 trong danh sách tính", $passed, $failed);
    assertTest($itemsBySku['NVL006']['soLuongDeXuat'] == 38.0, "NVL006: soLuongDeXuat chuẩn 38.0 kg", $passed, $failed);

    // 2.2 Kiểm tra Case KHSX001 (Exc 5.1: NVL001 đề xuất = 0 -> có cảnh báo)
    echo "\n2.2. Kiểm tra Case KHSX001 (Exc 5.1: NVL001 đề xuất = 0 -> có cảnh báo):\n";
    $calcKHSX001 = $service->calculateProposalFromPlan('KHSX001');
    assertTest($calcKHSX001['valid'], "Tính toán KHSX001 thành công", $passed, $failed);
    $itemsKHSX001 = array_column($calcKHSX001['items'], null, 'maSKU');
    assertTest(isset($itemsKHSX001['NVL001']), "Có NVL001 trong bảng tính KHSX001", $passed, $failed);
    assertTest($itemsKHSX001['NVL001']['soLuongDeXuat'] <= 0, "NVL001: soLuongDeXuat <= 0 (chuẩn 0 kg)", $passed, $failed);
    assertTest(isset($itemsKHSX001['NVL001']['canhBaoTonDu']) && $itemsKHSX001['NVL001']['canhBaoTonDu'] === Messages::DNM_EXC_5_1_NOT_NEEDED,
        "Đúng thông báo Exc 5.1: " . Messages::DNM_EXC_5_1_NOT_NEEDED, $passed, $failed);

    // -------------------------------------------------------------
    // 3. Kiểm tra kiểm tra tính hợp lệ dữ liệu (Validation)
    // -------------------------------------------------------------
    echo "\n3. Kiểm tra các ràng buộc & tiêu chí hợp lệ:\n";
    // 3.1 Ngày cần hàng trong quá khứ
    $valPastDate = $service->validateProposalForm([
        'maKeHoach'   => 'KHSX004',
        'ngayCanHang' => '2020-01-01',
        'lyDo'        => 'Mua phục vụ sản xuất',
        'items'       => [['maSKU' => 'NVL001', 'soLuongMua' => 5]],
    ]);
    assertTest(!$valPastDate['valid'], "Chặn khi ngày cần hàng trong quá khứ", $passed, $failed);
    assertTest(in_array(Messages::DNM_EXC_8_1_INVALID, $valPastDate['errors']),
        "Đúng thông báo Exc 8.1: " . Messages::DNM_EXC_8_1_INVALID, $passed, $failed);

    // 3.2 Kế hoạch chưa được duyệt
    $valUnapproved = $service->validateProposalForm([
        'maKeHoach'   => 'KHSX002',
        'ngayCanHang' => date('Y-m-d', strtotime('+7 days')),
        'lyDo'        => 'Mua phục vụ sản xuất',
        'items'       => [['maSKU' => 'NVL001', 'soLuongMua' => 5]],
    ]);
    assertTest(!$valUnapproved['valid'], "Chặn khi chọn kế hoạch chưa duyệt", $passed, $failed);

    // 3.3 Danh sách mua rỗng hoặc toàn bộ số lượng = 0
    $valZeroQty = $service->validateProposalForm([
        'maKeHoach'   => 'KHSX004',
        'ngayCanHang' => date('Y-m-d', strtotime('+7 days')),
        'lyDo'        => 'Mua phục vụ sản xuất',
        'items'       => [['maSKU' => 'NVL001', 'soLuongMua' => 0]],
    ]);
    assertTest(!$valZeroQty['valid'], "Chặn khi số lượng mua <= 0", $passed, $failed);
    assertTest(in_array(Messages::DNM_EXC_8_1_INVALID, $valZeroQty['errors']),
        "Đúng thông báo Exc 8.1 khi SL mua <= 0", $passed, $failed);

    // 3.4 NVL ngoài kế hoạch bắt buộc có lý do
    $valNonPlanNoReason = $service->validateProposalForm([
        'maKeHoach'   => 'KHSX004',
        'ngayCanHang' => date('Y-m-d', strtotime('+7 days')),
        'lyDo'        => '',
        'items'       => [['maSKU' => 'NVL003', 'soLuongMua' => 10, 'ngoaiKeHoach' => 1, 'lyDoRieng' => '']],
    ]);
    assertTest(!$valNonPlanNoReason['valid'], "Bắt buộc ghi lý do khi đề nghị NVL ngoài kế hoạch", $passed, $failed);

    // -------------------------------------------------------------
    // 4. Lưu đề nghị mua NVL thành công
    // -------------------------------------------------------------
    echo "\n4. Lưu đề nghị mua NVL thành công & kiểm tra tiền dự toán:\n";
    $validFormData = [
        'maKeHoach'   => 'KHSX004',
        'ngayCanHang' => date('Y-m-d', strtotime('+10 days')),
        'lyDo'        => 'Mua nguyên vật liệu phục vụ sản xuất tháng 11 theo KHSX004',
        'items'       => [
            [
                'maSKU'         => 'NVL001',
                'nhuCauSX'      => 25.0,
                'tonKhaDung'    => 30.0,
                'tonToiThieu'   => 20.0,
                'hangDangVe'    => 10.0,
                'soLuongDeXuat' => 5.0,
                'soLuongMua'    => 5.0,
                'ngoaiKeHoach'  => 0,
            ],
            [
                'maSKU'         => 'NVL005',
                'nhuCauSX'      => 20.0,
                'tonKhaDung'    => 12.0,
                'tonToiThieu'   => 8.0,
                'hangDangVe'    => 0.0,
                'soLuongDeXuat' => 16.0,
                'soLuongMua'    => 16.0,
                'ngoaiKeHoach'  => 0,
            ],
            [
                'maSKU'         => 'NVL006',
                'nhuCauSX'      => 48.0,
                'tonKhaDung'    => 20.0,
                'tonToiThieu'   => 10.0,
                'hangDangVe'    => 0.0,
                'soLuongDeXuat' => 38.0,
                'soLuongMua'    => 38.0,
                'ngoaiKeHoach'  => 0,
            ],
        ],
    ];

    $maDNM = $repo->createProposal($validFormData, 'NV001');
    assertTest(!empty($maDNM) && str_starts_with($maDNM, 'DNM-'), "Tạo đề nghị mua thành công với mã: {$maDNM}", $passed, $failed);

    $dnmRow = $repo->getProposalDetail($maDNM);
    assertTest($dnmRow['trangThai'] === 'CHO_DUYET', "Đề nghị ở trạng thái CHO_DUYET", $passed, $failed);
    assertTest(count($dnmRow['items']) === 3, "Đủ 3 mặt hàng trong chi tiết đề nghị mua", $passed, $failed);
    assertTest((float)$dnmRow['tongTienDuKien'] > 0, "Tính được tổng tiền dự toán tự động: " . number_format($dnmRow['tongTienDuKien']) . " đ", $passed, $failed);

    // -------------------------------------------------------------
    // 5. Kiểm tra hủy đề nghị khi còn CHO_DUYET
    // -------------------------------------------------------------
    echo "\n5. Kiểm tra hủy đề nghị khi còn CHO_DUYET:\n";
    $cancelOk = $repo->cancelProposal($maDNM, 'NV001', 'Hủy do thay đổi kế hoạch ca kíp');
    assertTest($cancelOk, "Hủy đề nghị mua thành công", $passed, $failed);

    $dnmAfterCancel = $repo->getProposalDetail($maDNM);
    assertTest($dnmAfterCancel['trangThai'] === 'DA_HUY', "Trạng thái đề nghị chuyển sang DA_HUY (Đã hủy)", $passed, $failed);

    // Thử hủy đề nghị đã được duyệt (DNM-20261027-6F4851F9 có trạng thái DA_DUYET)
    $cancelFail = $repo->cancelProposal('DNM-20261027-6F4851F9', 'NV001', 'Thử hủy đề nghị đã duyệt');
    assertTest(!$cancelFail, "Chặn không cho hủy đề nghị đã duyệt (DA_DUYET)", $passed, $failed);

    // -------------------------------------------------------------
    // 6. Kiểm tra ma trận phân quyền
    // -------------------------------------------------------------
    echo "\n6. Kiểm tra ma trận phân quyền:\n";
    $perms = require dirname(__DIR__) . '/app/config/permissions.php';
    $dnmPerm = $perms['modules']['de-nghi-mua'];

    assertTest(in_array('LKH', $dnmPerm['write']), "LKH có quyền GHI đề nghị mua NVL", $passed, $failed);
    assertTest(!in_array('QLK', $dnmPerm['write']), "QLK KHÔNG có quyền ghi đề nghị mua", $passed, $failed);
    assertTest(!in_array('BGD', $dnmPerm['write']), "BGD KHÔNG có quyền ghi đề nghị mua", $passed, $failed);
    assertTest(in_array('LKH', $dnmPerm['view']) && in_array('QLK', $dnmPerm['view']) && in_array('BGD', $dnmPerm['view']),
        "LKH, QLK, BGD đều có quyền xem đề nghị mua", $passed, $failed);

    // Auth::can kiểm tra
    $_SESSION['auth_user'] = ['vaiTro' => 'LKH', 'maNhanVien' => 'NV001'];
    $_SESSION['last_activity'] = time();
    assertTest(Auth::can('de-nghi-mua', 'create'), "Auth::can: LKH được phép tạo đề nghị mua", $passed, $failed);

    $_SESSION['auth_user'] = ['vaiTro' => 'QLK', 'maNhanVien' => 'NV002'];
    assertTest(!Auth::can('de-nghi-mua', 'create'), "Auth::can: QLK bị chặn tạo đề nghị mua", $passed, $failed);

} finally {
    // Luôn rollback để không sửa seed data
    $db->rollBack();
    echo "\n[INFO] Đã Rollback giao dịch. Dữ liệu seed được giữ nguyên vẹn.\n";
}

echo "\n===============================================================\n";
echo " TỔNG KẾT KIỂM THỬ PROMPT 3: {$passed} PASSED, {$failed} FAILED\n";
echo "===============================================================\n";

if ($failed > 0) {
    exit(1);
}

