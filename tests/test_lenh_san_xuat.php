<?php
/**
 * CLI Test Script: tests/test_lenh_san_xuat.php
 * Kiểm thử toàn diện chức năng Tạo lệnh sản xuất (Prompt 4 - LKH).
 * Đảm bảo:
 * 1. Chạy trong transaction và rollback toàn bộ, không thay đổi dữ liệu seed.
 * 2. Chạy 2 lần liên tiếp phải PASS 100% cả 2.
 * Cách chạy: php tests/test_lenh_san_xuat.php
 */

define('TEST_RUNNER', true);
require_once dirname(__DIR__) . '/public/index.php';

use App\Core\Database;
use App\Services\LenhSanXuatService;
use App\Repositories\LenhSanXuatRepository;

$db = Database::getConnection();
$passed = 0;
$failed = 0;

function assertCheck(bool $condition, string $testName, string $detail = ''): void
{
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$testName}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$testName} " . ($detail ? "({$detail})" : "") . "\n";
        $failed++;
    }
}

echo "===============================================================\n";
echo " KIỂM THỬ: TẠO LỆNH SẢN XUẤT THEO BOM & GIỮ CHỖ (PROMPT 4)\n";
echo "===============================================================\n\n";

$repo = new LenhSanXuatRepository();
$svc = new LenhSanXuatService($repo);

// -------------------------------------------------------------
// NHÓM 1: KIỂM THỬ TRUY VẤN KẾ HOẠCH & TỒN KHẢ DỤNG
// -------------------------------------------------------------
echo "1. Kiểm tra đọc danh sách kế hoạch đã duyệt & số lượng còn lại:\n";

$plans = $repo->getApprovedPlans();
assertCheck(count($plans) >= 2, 'Lấy được danh sách kế hoạch đã duyệt còn hạn mức (KHSX001, KHSX004...)');

// KHSX001 phải còn 800 hộp SP001 chưa giao
$p001 = $repo->getPlanDetail('KHSX001');
assertCheck($p001 !== null && $p001['trangThai'] === 'DA_DUYET', 'KHSX001 đã duyệt');
assertCheck((float)$p001['chiTiet'][0]['soLuongConLai'] === 800.0, 'KHSX001 còn lại 800 hộp SP001 chưa giao');

// KHSX002 chưa duyệt (CHO_DUYET) không được xuất hiện trong danh sách tạo lệnh
$p002Allowed = false;
foreach ($plans as $p) {
    if ($p['maKeHoach'] === 'KHSX002') $p002Allowed = true;
}
assertCheck(!$p002Allowed, 'Kế hoạch chưa duyệt (KHSX002) bị chặn không cho chọn');

// -------------------------------------------------------------
// NHÓM 2: KIỂM THỬ TÍNH NHU CẦU NVL (BOM) & ĐỦ/THIẾU
// -------------------------------------------------------------
echo "\n2. Kiểm tra tính BOM và đối chiếu tồn khả dụng (vw_ton_kho_kha_dung):\n";

// Case ĐỦ: KHSX001 giao 800 hộp SP001 (NVL001 cần 16kg <= 30kg, NVL002 cần 64kg <= 80kg, NVL004 cần 24kg <= 30kg)
$bom001 = $svc->calculateNVLNeeds([
    ['maSKU' => 'SP001', 'soLuongGiao' => 800]
]);
assertCheck($bom001['canIssue'] === true, 'KHSX001 (800 SP001): Toàn bộ NVL ĐỦ, cho phép phát lệnh');
assertCheck(count($bom001['nvlList']) === 3, 'Cần 3 loại NVL cho bánh quy bơ (NVL001, NVL002, NVL004)');

$nvl001 = null;
foreach ($bom001['nvlList'] as $n) {
    if ($n['maSKU'] === 'NVL001') $nvl001 = $n;
}
assertCheck($nvl001 !== null && (float)$nvl001['soLuongCanCap'] === 16.0, 'NVL001 cần cấp đúng 16 kg');
assertCheck($nvl001['trangThai'] === 'DU', 'NVL001 trạng thái ĐỦ');

// Case THIẾU: KHSX004 giao 800 SP002 + 300 SP003 (Thiếu NVL005 và NVL006)
$bom004 = $svc->calculateNVLNeeds([
    ['maSKU' => 'SP002', 'soLuongGiao' => 800],
    ['maSKU' => 'SP003', 'soLuongGiao' => 300]
]);
assertCheck($bom004['canIssue'] === false, 'KHSX004: Có NVL thiếu, KHÓA nút phát lệnh sản xuất');
assertCheck(
    str_starts_with($bom004['canhBaoThieu'] ?? '', \App\Config\Messages::LSX_EXC_9_1_INSUFFICIENT),
    'Đúng thông báo Exc 9.1: ' . \App\Config\Messages::LSX_EXC_9_1_INSUFFICIENT
);

$nvl006 = null;
foreach ($bom004['nvlList'] as $n) {
    if ($n['maSKU'] === 'NVL006') $nvl006 = $n;
}
assertCheck($nvl006 !== null && (float)$nvl006['soLuongCanCap'] === 48.0, 'NVL006 cần 48 kg');
assertCheck((float)$nvl006['soLuongThieu'] === 28.0, 'NVL006 thiếu 28 kg (Cần 48 - Khả dụng 20)');
assertCheck($nvl006['trangThai'] === 'THIEU', 'NVL006 hiển thị trạng thái THIẾU');

// -------------------------------------------------------------
// NHÓM 3: VALIDATE NGHIỆP VỤ & THÔNG BÁO LỖI
// -------------------------------------------------------------
echo "\n3. Kiểm tra các trường hợp ngoại lệ & validate giao diện:\n";

// Vượt số lượng còn lại
$valOver = $svc->validateOrderForm([
    'maKeHoach' => 'KHSX001',
    'items' => [
        ['maSKU' => 'SP001', 'soLuongGiao' => 850, 'maXuong' => 'XSX001']
    ]
]);
assertCheck($valOver['valid'] === false, 'Chặn khi số lượng giao vượt hạn mức kế hoạch');
$errStr = implode(' ', $valOver['errors']);
assertCheck(
    str_contains($errStr, 'vượt quá hạn mức còn lại') && str_contains($errStr, '800'),
    'Đúng thông báo: "Số lượng nhập vượt quá hạn mức còn lại của kế hoạch sản xuất (Tối đa: X)"'
);

// Số lượng <= 0
$valZero = $svc->validateOrderForm([
    'maKeHoach' => 'KHSX001',
    'items' => [
        ['maSKU' => 'SP001', 'soLuongGiao' => 0, 'maXuong' => 'XSX001']
    ]
]);
assertCheck($valZero['valid'] === false, 'Chặn số lượng giao = 0');

// -------------------------------------------------------------
// NHÓM 4: PHÁT LỆNH, GIỮ CHỖ BR-06 & CHIA 2 XƯỞNG (TRANSACTION)
// -------------------------------------------------------------
echo "\n4. Kiểm tra phát lệnh thành công, chia 2 xưởng, giữ chỗ BR-06 & Rollback:\n";

$db->beginTransaction();
try {
    // 1. Kiểm tra ton_kho và tồn khả dụng trước khi phát lệnh
    $tonKhoTruoc = $db->query("SELECT COUNT(*) AS cnt, SUM(soLuongThucTe) AS total FROM ton_kho")->fetch();
    $kdTruoc = $db->query("SELECT tonKhaDung FROM vw_ton_kho_kha_dung WHERE maSKU='NVL001'")->fetchColumn();
    assertCheck((float)$kdTruoc === 30.0, 'Tồn khả dụng NVL001 trước lệnh 1 là 30 kg');

    // 2. Phát lệnh 1: KHSX001 giao 400 hộp SP001 cho xưởng XSX001 (Đợt 1)
    $orderData1 = [
        'maKeHoach'     => 'KHSX001',
        'maXuong'       => 'XSX001',
        'ngayBatDau'    => date('Y-m-d', strtotime('+1 day')),
        'ngayHoanThanh' => date('Y-m-d', strtotime('+3 days')),
        'ghiChu'        => 'Giao đợt 1 cho Xưởng 1',
        'items'         => [
            ['maSKU' => 'SP001', 'soLuongGiao' => 400]
        ]
    ];
    $code1 = $repo->createOrder($orderData1, 'DA_PHAT', 'NV001', 'TK002');
    assertCheck(!empty($code1), "Lệnh 1 phát thành công với mã: {$code1}");

    // Kiểm tra ton_kho không đổi
    $tonKhoSau1 = $db->query("SELECT COUNT(*) AS cnt, SUM(soLuongThucTe) AS total FROM ton_kho")->fetch();
    assertCheck(
        $tonKhoTruoc['cnt'] === $tonKhoSau1['cnt'] && $tonKhoTruoc['total'] === $tonKhoSau1['total'],
        'BR-06: ton_kho tuyệt đối không đổi khi phát lệnh sản xuất'
    );

    // Kiểm tra tồn khả dụng NVL001 đã giảm đúng 8kg (400 hộp x 0.02kg = 8kg giữ chỗ)
    $kdSau1 = $db->query("SELECT tonKhaDung FROM vw_ton_kho_kha_dung WHERE maSKU='NVL001'")->fetchColumn();
    assertCheck((float)$kdSau1 === 22.0, 'BR-06: Tồn khả dụng NVL001 giảm đúng 8 kg (30 - 8 = 22 kg)');

    // KHSX001 đã cộng số đã giao = 400, còn lại = 400
    $planSau1 = $repo->getPlanDetail('KHSX001');
    assertCheck((float)$planSau1['chiTiet'][0]['soLuongDaGiaoLenh'] === 400.0, 'KHSX001: soLuongDaGiaoLenh tăng lên 400');
    assertCheck((float)$planSau1['chiTiet'][0]['soLuongConLai'] === 400.0, 'KHSX001: soLuongConLai giảm xuống 400');

    // 3. Phát lệnh 2 (Chia xưởng thứ hai): Giao nốt 400 hộp SP001 cho xưởng XSX002
    // Lệnh 2 kiểm tra tồn khả dụng: thấy NVL001 chỉ còn 22 kg (đã trừ giữ chỗ lệnh 1)
    $orderData2 = [
        'maKeHoach'     => 'KHSX001',
        'maXuong'       => 'XSX002',
        'ngayBatDau'    => date('Y-m-d', strtotime('+4 days')),
        'ngayHoanThanh' => date('Y-m-d', strtotime('+6 days')),
        'ghiChu'        => 'Giao đợt 2 cho Xưởng 2 (Chia 2 xưởng)',
        'items'         => [
            ['maSKU' => 'SP001', 'soLuongGiao' => 400]
        ]
    ];
    $code2 = $repo->createOrder($orderData2, 'DA_PHAT', 'NV001', 'TK002');
    assertCheck(!empty($code2), "Lệnh 2 (xưởng 2) phát thành công với mã: {$code2}");

    // Tồn khả dụng NVL001 tiếp tục giảm thêm 8kg (22 - 8 = 14 kg)
    $kdSau2 = $db->query("SELECT tonKhaDung FROM vw_ton_kho_kha_dung WHERE maSKU='NVL001'")->fetchColumn();
    assertCheck((float)$kdSau2 === 14.0, '2 lệnh liên tiếp: Tồn khả dụng NVL001 còn 14 kg (30 - 8 - 8 = 14 kg)');

    // KHSX001 đã giao đủ 800/800 hộp (còn lại 0)
    $planSau2 = $repo->getPlanDetail('KHSX001');
    assertCheck((float)$planSau2['chiTiet'][0]['soLuongConLai'] === 0.0, 'KHSX001: Đã giao đủ 800 hộp (còn lại 0)');

    // Kiểm tra nhat_ky_he_thong
    $log = $db->query("SELECT * FROM nhat_ky_he_thong WHERE chucNang='lenh-san-xuat' ORDER BY maNhatKy DESC LIMIT 1")->fetch();
    assertCheck($log !== false && $log['hanhDong'] === 'PHAT_LENH', 'Đã ghi nhat_ky_he_thong PHAT_LENH');
} finally {
    $db->rollBack();
}

// Kiểm tra phục hồi nguyên vẹn sau rollback
$kdCuoi = $db->query("SELECT tonKhaDung FROM vw_ton_kho_kha_dung WHERE maSKU='NVL001'")->fetchColumn();
assertCheck((float)$kdCuoi === 30.0, 'Sau Rollback: Tồn khả dụng NVL001 phục hồi chuẩn 30 kg (Seed nguyên vẹn)');

// -------------------------------------------------------------
// NHÓM 5: KIỂM THỬ PHÂN QUYỀN
// -------------------------------------------------------------
echo "\n5. Kiểm tra phân quyền truy cập:\n";
$perms = require dirname(__DIR__) . '/app/config/permissions.php';
$lsxPerm = $perms['modules']['lenh-san-xuat'];
assertCheck(in_array('LKH', $lsxPerm['write']), 'Quyền GHI lenh-san-xuat: LKH được phép');
assertCheck(!in_array('QLK', $lsxPerm['write']) && !in_array('BGD', $lsxPerm['write']), 'QLK và BGD không có quyền ghi');
assertCheck(in_array('LKH', $lsxPerm['view']) && in_array('QLK', $lsxPerm['view']) && in_array('BGD', $lsxPerm['view']), 'LKH, QLK, BGD đều có quyền xem');

echo "\n===============================================================\n";
echo " TỔNG KẾT KIỂM THỬ PROMPT 4: {$passed} PASSED, {$failed} FAILED\n";
echo "===============================================================\n";

if ($failed > 0) {
    exit(1);
}
