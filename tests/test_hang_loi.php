<?php
/**
 * CLI Test Script: tests/test_hang_loi.php
 * Kiểm thử toàn diện chức năng Đề xuất xử lý hàng lỗi hỏng (UC 2.4.3).
 * Đảm bảo:
 * 1. Chạy trong transaction và rollback toàn bộ, không thay đổi dữ liệu seed.
 * 2. Chạy 2 lần liên tiếp phải PASS 100% cả 2.
 * Cách chạy: php tests/test_hang_loi.php
 */

define('TEST_RUNNER', true);
require_once dirname(__DIR__) . '/public/index.php';

use App\Core\Database;
use App\Core\Auth;
use App\Services\HangLoiService;
use App\Repositories\HangLoiRepository;

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
echo " KIỂM THỬ: ĐỀ XUẤT XỬ LÝ HÀNG LỖI HỎNG (UC 2.4.3)\n";
echo "===============================================================\n\n";

$repo = new HangLoiRepository();
$svc = new HangLoiService($repo);

// -------------------------------------------------------------
// NHÓM 1: KIỂM THỬ ĐỌC DỮ LIỆU SEED & TÍNH SỐ LƯỢNG CÒN LẠI ĐỘNG
// -------------------------------------------------------------
echo "1. Kiểm tra đọc dữ liệu seed & tính số lượng còn lại động:\n";

$danhSachLoi = $repo->getDanhSachLoi();
assertCheck(count($danhSachLoi) >= 7, 'Đọc được toàn bộ danh sách hàng lỗi từ CSDL (HL001..HL007)');

// Kiểm tra HL001 và HL002 đã trừ đề xuất có sẵn DXLH-20261028-0001 (HL001 trừ 2, HL002 trừ 3)
$hl001 = null;
$hl002 = null;
foreach ($danhSachLoi as $item) {
    if ($item['maHangLoi'] === 'HL001') $hl001 = $item;
    if ($item['maHangLoi'] === 'HL002') $hl002 = $item;
}
assertCheck($hl001 !== null && (float)$hl001['soLuongConLai'] === 16.0, 'HL001: 18 lỗi - 2 đã đề xuất = 16 còn lại');
assertCheck($hl002 !== null && (float)$hl002['soLuongConLai'] === 22.0, 'HL002: 25 lỗi - 3 đã đề xuất = 22 còn lại');

// -------------------------------------------------------------
// NHÓM 2: KIỂM THỬ VALIDATE & TỔNG HỢP GIÁ TRỊ (TEST CASES)
// -------------------------------------------------------------
echo "\n2. Kiểm tra validate và tổng hợp giá trị:\n";

// Case chuẩn: HL001 x 2 hộp + HL002 x 3 gói, Tiêu hủy -> tổng 109.000 đ
$inputChuan = [
    'selected'  => ['HL001', 'HL002'],
    'qty'       => ['HL001' => '2', 'HL002' => '3'],
    'phuongAn'  => 'TIEU_HUY',
    'lyDo'      => 'Bánh vỡ vụn và rách seal bao bì không đạt tiêu chuẩn QC',
];
$valChuan = $svc->validate($inputChuan);
assertCheck($valChuan['valid'] === true, 'Test case chuẩn: Dữ liệu hợp lệ');
assertCheck((float)$valChuan['tongGiaTri'] === 109000.0, 'HL001 (2 x 32k) + HL002 (3 x 15k) = 109.000 đ');
assertCheck(count($valChuan['chiTiet']) === 2, 'Chọn đủ 2 mặt hàng');
assertCheck($valChuan['chiTiet'][0]['soLo'] === 'LO_BK_QB_001' || !empty($valChuan['chiTiet'][0]['soLo']), 'Snapshot số lô sản xuất');
assertCheck(!empty($valChuan['chiTiet'][0]['ngayHetHan']), 'Snapshot hạn sử dụng');

// Case vượt số lượng lỗi còn lại (HL001 còn 16, nhập 99)
$inputVuot = $inputChuan;
$inputVuot['qty']['HL001'] = '99';
$valVuot = $svc->validate($inputVuot);
assertCheck($valVuot['valid'] === false, 'Chặn khi số lượng xử lý vượt số lỗi còn lại');
$errVuot = implode(' ', $valVuot['errors']);
assertCheck(str_contains($errVuot, \App\Config\Messages::HL_EXC_6_1_OVER_QTY), 'Đúng thông báo Exc 6.1: ' . \App\Config\Messages::HL_EXC_6_1_OVER_QTY);

// Case thiếu lý do xử lý
$inputThieuLyDo = $inputChuan;
$inputThieuLyDo['lyDo'] = '';
$valThieuLyDo = $svc->validate($inputThieuLyDo);
assertCheck($valThieuLyDo['valid'] === false, 'Chặn khi thiếu lý do xử lý');
$errLyDo = implode(' ', $valThieuLyDo['errors']);
assertCheck(str_contains($errLyDo, 'nhập lý do'), 'Đúng thông báo thiếu lý do xử lý');

// Case không chọn mặt hàng nào
$inputRong = $inputChuan;
$inputRong['selected'] = [];
$valRong = $svc->validate($inputRong);
assertCheck($valRong['valid'] === false, 'Chặn khi không chọn mặt hàng nào');
$errRong = implode(' ', $valRong['errors']);
assertCheck(str_contains($errRong, 'chọn ít nhất một mặt hàng'), 'Đúng thông báo yêu cầu chọn ít nhất 1 mặt hàng');

// Case sai phương án
$inputSaiPA = $inputChuan;
$inputSaiPA['phuongAn'] = 'BAN_THANH_LY';
$valSaiPA = $svc->validate($inputSaiPA);
assertCheck($valSaiPA['valid'] === false, 'Chặn phương án không hợp lệ');

// -------------------------------------------------------------
// NHÓM 3: KIỂM THỬ LƯU CSDL VỚI TRANSACTION & ROLLBACK AN TOÀN
// -------------------------------------------------------------
echo "\n3. Kiểm tra lưu đề xuất mới, sinh mã & Rollback an toàn:\n";

$db->beginTransaction();
try {
    // Lưu đề xuất mới với case chuẩn
    $maDeXuat = $repo->saveDeXuat($valChuan, 'NV002', 'TK003');
    assertCheck(!empty($maDeXuat), "Tạo thành công đề xuất mã: {$maDeXuat}");
    assertCheck((bool)preg_match('/^DXLH-\d{8}-\d{4}$/', $maDeXuat), 'Mã đề xuất đúng chuẩn DXLH-YYYYMMDD-NNNN');

    // Kiểm tra bản ghi trong de_xuat_xu_ly_hang_loi
    $dx = $repo->findDeXuatByMa($maDeXuat);
    assertCheck($dx !== null, 'Tìm thấy đề xuất vừa lưu trong CSDL');
    assertCheck($dx['trangThai'] === 'CHO_XU_LY', 'Trạng thái ban đầu là CHO_XU_LY');
    assertCheck((float)$dx['tongGiaTri'] === 109000.0, 'Tổng giá trị lưu đúng 109.000 đ');
    assertCheck(count($dx['chiTiet'] ?? []) === 2, 'Lưu đủ 2 dòng chi tiết');

    // Kiểm tra snapshot số lô & HSD
    assertCheck(!empty($dx['chiTiet'][0]['soLo']), 'Chi tiết đã snapshot số lô');
    assertCheck(!empty($dx['chiTiet'][0]['ngayHetHan']), 'Chi tiết đã snapshot hạn sử dụng');

    // Kiểm tra số lượng còn lại động sau khi thêm đề xuất mới (HL001 còn 16 - 2 = 14)
    $dsMoi = $repo->getDanhSachLoi();
    $hl001Moi = null;
    foreach ($dsMoi as $item) {
        if ($item['maHangLoi'] === 'HL001') $hl001Moi = $item;
    }
    assertCheck((float)$hl001Moi['soLuongConLai'] === 14.0, 'Số lượng còn lại động tự giảm xuống 14 ngay lập tức');

    // Kiểm tra nhật ký hệ thống
    $log = $db->query("SELECT * FROM nhat_ky_he_thong WHERE chucNang='hang-loi' ORDER BY maNhatKy DESC LIMIT 1")->fetch();
    assertCheck($log !== false && $log['hanhDong'] === 'TAO_DE_XUAT', 'Đã ghi nhat_ky_he_thong cho hành động TAO_DE_XUAT');
    assertCheck(!empty($log['giaTriMoi']), 'Nhật ký chứa snapshot JSON giaTriMoi');
} finally {
    $db->rollBack();
}

// Kiểm tra số lượng còn lại sau khi rollback đã khôi phục nguyên vẹn
$dsSauRollback = $repo->getDanhSachLoi();
$hl001Sau = null;
foreach ($dsSauRollback as $item) {
    if ($item['maHangLoi'] === 'HL001') $hl001Sau = $item;
}
assertCheck((float)$hl001Sau['soLuongConLai'] === 16.0, 'Sau Rollback: Số lượng còn lại phục hồi chuẩn 16 (Dữ liệu seed nguyên vẹn 100%)');

// -------------------------------------------------------------
// NHÓM 4: KIỂM THỬ PHÂN QUYỀN TRUY CẬP
// -------------------------------------------------------------
echo "\n4. Kiểm tra phân quyền truy cập:\n";
$perms = require dirname(__DIR__) . '/app/config/permissions.php';
$hlPerm = $perms['modules']['hang-loi'];
assertCheck(in_array('QLK', $hlPerm['write']) && !in_array('QC', $hlPerm['write']) && !in_array('BGD', $hlPerm['write']), 'Quyền GHI hang-loi: Chỉ QLK được ghi; QC và BGD không được ghi');
assertCheck(in_array('QLK', $hlPerm['view']) && in_array('QC', $hlPerm['view']) && in_array('BGD', $hlPerm['view']), 'Quyền XEM hang-loi: Cả QLK, QC, BGD đều được phép xem');
assertCheck(!in_array('NVK', $hlPerm['view']) && !in_array('LKH', $hlPerm['view']), 'Các vai trò khác (NVK, LKH) không có quyền xem/ghi');

echo "\n===============================================================\n";
echo " TỔNG KẾT KIỂM THỬ: {$passed} PASSED, {$failed} FAILED\n";
echo "===============================================================\n";

if ($failed > 0) {
    exit(1);
}

