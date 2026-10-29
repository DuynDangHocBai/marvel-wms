<?php
/**
 * CLI Test Script: tests/test_kiem_ke.php
 * Kiểm thử toàn diện chức năng Cập nhật chênh lệch kiểm kê (UC 2.6.3).
 * Đảm bảo:
 * 1. Chạy trong transaction và rollback toàn bộ, không thay đổi dữ liệu seed.
 * 2. Chạy 2 lần liên tiếp phải PASS 100% cả 2.
 * Cách chạy: php tests/test_kiem_ke.php
 */

define('TEST_RUNNER', true);
require_once dirname(__DIR__) . '/public/index.php';

use App\Core\Database;
use App\Core\Auth;
use App\Services\KiemKeService;
use App\Repositories\KiemKeRepository;

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
echo " KIỂM THỬ: CẬP NHẬT CHÊNH LỆCH KIỂM KÊ (UC 2.6.3)\n";
echo "===============================================================\n\n";

$svc = new KiemKeService();
$repo = new KiemKeRepository();

// -------------------------------------------------------------
// NHÓM 1: KIỂM THỬ CÔNG THỨC & LOGIC NGHIỆP VỤ (IN-MEMORY)
// -------------------------------------------------------------
echo "1. Kiểm tra logic tính chênh lệch & các test case mẫu:\n";

// Case 1: BBKK-2026-001 (thừa 128.000 đ, thiếu 66.000 đ, tổng 194.000 đ, ròng 62.000 đ)
$record001 = [
    'maBienBan' => 'BBKK-2026-001',
    'chiTiet' => [
        ['id' => 1, 'maSKU' => 'BK-QB-001', 'soLuongSoSach' => 120, 'soLuongThucTe' => 124, 'donGia' => 32000],
        ['id' => 2, 'maSKU' => 'BK-BG-005', 'soLuongSoSach' => 75,  'soLuongThucTe' => 72,  'donGia' => 22000],
        ['id' => 3, 'maSKU' => 'BK-CP-007', 'soLuongSoSach' => 28,  'soLuongThucTe' => 28,  'donGia' => 49000]
    ]
];
$res001 = $svc->calculate($record001);
assertCheck($res001['valid'] === true, 'BBKK-2026-001: Dữ liệu tính hợp lệ');
assertCheck((float)$res001['chiTiet'][0]['soLuongChenhLech'] === 4.0, 'BK-QB-001: Thừa số lượng +4');
assertCheck((float)$res001['chiTiet'][0]['giaTriChenhLech'] === 128000.0, 'BK-QB-001: Thừa giá trị 128.000 đ');
assertCheck($res001['chiTiet'][0]['phanLoai'] === 'THUA', 'BK-QB-001: Phân loại THUA');

assertCheck((float)$res001['chiTiet'][1]['soLuongChenhLech'] === -3.0, 'BK-BG-005: Thiếu số lượng -3');
assertCheck((float)$res001['chiTiet'][1]['giaTriChenhLech'] === -66000.0, 'BK-BG-005: Thiếu giá trị -66.000 đ');
assertCheck($res001['chiTiet'][1]['phanLoai'] === 'THIEU', 'BK-BG-005: Phân loại THIEU');

assertCheck((float)$res001['chiTiet'][2]['soLuongChenhLech'] === 0.0, 'BK-CP-007: Khớp số lượng 0');
assertCheck($res001['chiTiet'][2]['phanLoai'] === 'KHOP', 'BK-CP-007: Phân loại KHOP');

assertCheck((float)$res001['tongThua'] === 128000.0, 'BBKK-2026-001: Tổng giá trị thừa = 128.000 đ');
assertCheck((float)$res001['tongThieu'] === 66000.0, 'BBKK-2026-001: Tổng giá trị thiếu = 66.000 đ');
assertCheck((float)$res001['tongChenhLech'] === 194000.0, 'BBKK-2026-001: Tổng độ lớn chênh lệch = 194.000 đ');
assertCheck((float)$res001['giaTriRong'] === 62000.0, 'BBKK-2026-001: Chênh lệch ròng = 62.000 đ');

// Case 2: BBKK-2026-005 (không chênh lệch)
$record005 = [
    'maBienBan' => 'BBKK-2026-005',
    'chiTiet' => [
        ['id' => 10, 'maSKU' => 'BK-QB-001', 'soLuongSoSach' => 98, 'soLuongThucTe' => 98, 'donGia' => 32000],
        ['id' => 11, 'maSKU' => 'BK-BG-005', 'soLuongSoSach' => 75, 'soLuongThucTe' => 75, 'donGia' => 22000]
    ]
];
$res005 = $svc->calculate($record005);
assertCheck($res005['valid'] === true, 'BBKK-2026-005: Không chênh lệch hợp lệ');
assertCheck((float)$res005['tongChenhLech'] === 0.0, 'BBKK-2026-005: Tổng độ lớn chênh lệch = 0');
assertCheck($res005['isKhongChenhLech'] === true, 'BBKK-2026-005: Nhận diện cờ Không chênh lệch (luồng 5.1)');

// Case 3: BBKK-2026-003 (thiếu số lượng thực tế)
$record003 = [
    'maBienBan' => 'BBKK-2026-003',
    'chiTiet' => [
        ['id' => 6, 'maSKU' => 'BK-SC-004', 'soLuongSoSach' => 45, 'soLuongThucTe' => null, 'donGia' => 42000],
        ['id' => 7, 'maSKU' => 'BK-BL-003', 'soLuongSoSach' => 60, 'soLuongThucTe' => 60,   'donGia' => 28000]
    ]
];
$res003 = $svc->calculate($record003);
assertCheck($res003['valid'] === false, 'BBKK-2026-003: Chặn khi thiếu số lượng thực tế');
$errStr003 = implode(' ', $res003['errors']);
assertCheck(str_contains($errStr003, 'chưa có số lượng thực tế'), 'BBKK-2026-003: Đúng thông báo thiếu số thực tế');

// Case 4: BBKK-2026-004 (thiếu sổ sách / đơn giá)
$record004 = [
    'maBienBan' => 'BBKK-2026-004',
    'chiTiet' => [
        ['id' => 8, 'maSKU' => 'BK-KD-002', 'soLuongSoSach' => null, 'soLuongThucTe' => 85, 'donGia' => 15000],
        ['id' => 9, 'maSKU' => 'BK-KS-006', 'soLuongSoSach' => 45,   'soLuongThucTe' => 40, 'donGia' => null]
    ]
];
$res004 = $svc->calculate($record004);
assertCheck($res004['valid'] === false, 'BBKK-2026-004: Chặn khi thiếu sổ sách hoặc đơn giá');
$errStr004 = implode(' ', $res004['errors']);
assertCheck(mb_stripos($errStr004, 'không đủ thông tin để tính chênh lệch') !== false, 'BBKK-2026-004: Đúng thông báo không đủ thông tin');

// -------------------------------------------------------------
// NHÓM 2: KIỂM THỬ TRUY VẤN REPOSITORY TỪ CSDL SEED
// -------------------------------------------------------------
echo "\n2. Kiểm tra Repository đọc CSDL seed thực tế:\n";

$allBB = $repo->getAll();
assertCheck(count($allBB) >= 5, 'Lấy được danh sách biên bản kiểm kê từ CSDL (>= 5)');

$bb1 = $repo->findByMa('BBKK-2026-001');
assertCheck($bb1 !== null, 'Tìm thấy biên bản BBKK-2026-001');
assertCheck(count($bb1['chiTiet'] ?? []) === 3, 'BBKK-2026-001 có đủ 3 dòng chi tiết');

$bb3 = $repo->findByMa('BBKK-2026-003');
assertCheck($bb3 !== null && $bb3['trangThai'] === 'DANG_KIEM_KE', 'BBKK-2026-003 ở trạng thái DANG_KIEM_KE');

// -------------------------------------------------------------
// NHÓM 3: KIỂM THỬ GIAO DỊCH DATABASE (TRANSACTION & ROLLBACK)
// -------------------------------------------------------------
echo "\n3. Kiểm tra lưu chênh lệch, phân quyền duyệt & Rollback an toàn:\n";

// Đếm số lượng tồn kho trước khi test để đảm bảo không bị sửa đổi
$tonKhoTruoc = $db->query("SELECT COUNT(*) AS cnt, SUM(soLuongThucTe) AS total FROM ton_kho")->fetch();

// Test Case A: Lưu thất bại nếu biên bản không ở DANG_KIEM_KE (BBKK-2026-001 đã DA_DUYET)
try {
    $db->beginTransaction();
    $repo->saveCalculated('BBKK-2026-001', $res001, 'NV002', 'TK003');
    $db->rollBack();
    assertCheck(false, 'Chặn cập nhật biên bản không ở trạng thái DANG_KIEM_KE');
} catch (\Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    assertCheck(true, 'Chặn cập nhật biên bản không ở trạng thái DANG_KIEM_KE: ' . $e->getMessage());
}

// Test Case B: Tạo biên bản tạm DANG_KIEM_KE với chênh lệch <= 10.000.000 đ -> trạng thái CHO_QLK_DUYET
$db->beginTransaction();
try {
    $testMaBB = 'BBKK-TEST-QLK';
    $db->prepare("INSERT INTO bien_ban_kiem_ke (maBienBan, ngayKiemKe, maKho, maNhanVienLap, trangThai)
                  VALUES (?, CURDATE(), 'K005', 'NV002', 'DANG_KIEM_KE')")->execute([$testMaBB]);
    $db->prepare("INSERT INTO chi_tiet_kiem_ke (maBienBan, maSKU, soLuongSoSach, soLuongThucTe, donGia)
                  VALUES (?, 'BK-QB-001', 100, 105, 32000)")->execute([$testMaBB]);

    $bbData = $repo->findByMa($testMaBB);
    $calcResult = $svc->calculate($bbData);
    assertCheck($calcResult['valid'] === true, 'Test QLK: Dữ liệu tính hợp lệ');

    $saveOk = $repo->saveCalculated($testMaBB, $calcResult, 'NV002', 'TK003');
    assertCheck($saveOk === true, 'Test QLK: Lưu thành công');

    $savedBB = $repo->findByMa($testMaBB);
    assertCheck($savedBB['trangThai'] === 'CHO_QLK_DUYET', 'BR-01/BR-08: Chênh lệch <= 10tr chuyển sang CHO_QLK_DUYET');
    assertCheck((float)$savedBB['tongGiaTriChenhLech'] === 160000.0, 'Đã cập nhật tongGiaTriChenhLech = 160.000 đ');

    // Kiểm tra nhật ký hệ thống đã ghi
    $log = $db->query("SELECT * FROM nhat_ky_he_thong WHERE chucNang='kiem-ke' ORDER BY maNhatKy DESC LIMIT 1")->fetch();
    assertCheck($log !== false && $log['hanhDong'] === 'CAP_NHAT_CHENH_LECH', 'Đã ghi nhat_ky_he_thong kèm giaTriCu và giaTriMoi');
    assertCheck(!empty($log['giaTriCu']) && !empty($log['giaTriMoi']), 'Nhật ký chứa đầy đủ JSON giaTriCu và giaTriMoi');
} finally {
    $db->rollBack();
}

// Test Case C: Tạo biên bản tạm DANG_KIEM_KE với chênh lệch > 10.000.000 đ -> trạng thái CHO_BGD_DUYET
$db->beginTransaction();
try {
    $testMaBB = 'BBKK-TEST-BGD';
    $db->prepare("INSERT INTO bien_ban_kiem_ke (maBienBan, ngayKiemKe, maKho, maNhanVienLap, trangThai)
                  VALUES (?, CURDATE(), 'K005', 'NV002', 'DANG_KIEM_KE')")->execute([$testMaBB]);
    // 500 hộp x 32000 = 16.000.000 đ (> 10tr)
    $db->prepare("INSERT INTO chi_tiet_kiem_ke (maBienBan, maSKU, soLuongSoSach, soLuongThucTe, donGia)
                  VALUES (?, 'BK-QB-001', 1000, 1500, 32000)")->execute([$testMaBB]);

    $bbData = $repo->findByMa($testMaBB);
    $calcResult = $svc->calculate($bbData);

    $saveOk = $repo->saveCalculated($testMaBB, $calcResult, 'NV002', 'TK003');
    assertCheck($saveOk === true, 'Test BGD: Lưu thành công');

    $savedBB = $repo->findByMa($testMaBB);
    assertCheck($savedBB['trangThai'] === 'CHO_BGD_DUYET', 'BR-01/BR-08: Chênh lệch > 10tr chuyển sang CHO_BGD_DUYET');
    assertCheck((float)$savedBB['tongGiaTriChenhLech'] === 16000000.0, 'Đã cập nhật tongGiaTriChenhLech = 16.000.000 đ');
} finally {
    $db->rollBack();
}

// Xác minh bảng ton_kho không bị chạm vào
$tonKhoSau = $db->query("SELECT COUNT(*) AS cnt, SUM(soLuongThucTe) AS total FROM ton_kho")->fetch();
assertCheck(
    $tonKhoTruoc['cnt'] === $tonKhoSau['cnt'] && $tonKhoTruoc['total'] === $tonKhoSau['total'],
    'RÀNG BUỘC PHẠM VI: Bảng ton_kho hoàn toàn nguyên vẹn 100% (không bị điều chỉnh)'
);

// -------------------------------------------------------------
// NHÓM 4: KIỂM THỬ PHÂN QUYỀN
// -------------------------------------------------------------
echo "\n4. Kiểm tra phân quyền truy cập:\n";
$perms = require dirname(__DIR__) . '/app/config/permissions.php';
$kkPerm = $perms['modules']['kiem-ke'];
assertCheck(in_array('QLK', $kkPerm['write']) && !in_array('BGD', $kkPerm['write']), 'Quyền GHI kiem-ke: QLK được phép, BGD không được phép');
assertCheck(in_array('QLK', $kkPerm['view']) && in_array('BGD', $kkPerm['view']), 'Quyền XEM kiem-ke: Cả QLK và BGD đều được phép');
assertCheck(!in_array('NVK', $kkPerm['view']) && !in_array('QC', $kkPerm['view']), 'Các vai trò khác (NVK, QC) không có quyền xem/ghi');

echo "\n===============================================================\n";
echo " TỔNG KẾT KIỂM THỬ: {$passed} PASSED, {$failed} FAILED\n";
echo "===============================================================\n";

if ($failed > 0) {
    exit(1);
}
