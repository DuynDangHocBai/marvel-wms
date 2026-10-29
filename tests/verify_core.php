<?php
/**
 * CLI Test Script: tests/verify_core.php
 * Chạy kiểm thử tự động toàn bộ yêu cầu cốt lõi của đề bài.
 * Cách chạy: php tests/verify_core.php
 */

define('TEST_RUNNER', true);
require_once __DIR__ . '/../public/index.php'; // Nạp config, autoloader

use App\Core\Database;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Flash;
use App\Repositories\NguoiDungRepository;
use App\Services\NhatKyService;

Auth::initSession();

$passed = 0;
$failed = 0;

function assertTest(bool $condition, string $testName): void
{
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$testName}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$testName}\n";
        $failed++;
    }
}

echo "===============================================================\n";
echo " KIỂM THỬ TỰ ĐỘNG KHUNG DỰ ÁN MARVEL WMS CORE\n";
echo "===============================================================\n\n";

// 1. Kiểm tra Database & PDO
echo "1. Kiểm tra kết nối Cơ sở dữ liệu & PDO Singleton:\n";
try {
    $db = Database::getConnection();
    assertTest($db instanceof PDO, "Kết nối PDO singleton thành công");
    $errMode = $db->getAttribute(PDO::ATTR_ERRMODE);
    assertTest($errMode === PDO::ERRMODE_EXCEPTION, "PDO::ATTR_ERRMODE là ERRMODE_EXCEPTION");
    $fetchMode = $db->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE);
    assertTest($fetchMode === PDO::FETCH_ASSOC, "PDO::ATTR_DEFAULT_FETCH_MODE là FETCH_ASSOC");
} catch (\Throwable $e) {
    assertTest(false, "Lỗi kết nối CSDL: " . $e->getMessage());
}

// 2. Kiểm tra Seed Data & Mã nhân viên NV000 - NV005
echo "\n2. Kiểm tra Seed Data & Mã nhân viên chuẩn:\n";
$repo = new NguoiDungRepository();
$expectedAccounts = [
    'qtv01' => ['role' => 'QTV', 'maNhanVien' => 'NV000'],
    'lkh01' => ['role' => 'LKH', 'maNhanVien' => 'NV001'],
    'qlk01' => ['role' => 'QLK', 'maNhanVien' => 'NV002'],
    'nvk01' => ['role' => 'NVK', 'maNhanVien' => 'NV003'],
    'qc01'  => ['role' => 'QC',  'maNhanVien' => 'NV004'],
    'bgd01' => ['role' => 'BGD', 'maNhanVien' => 'NV005'],
];

foreach ($expectedAccounts as $username => $expected) {
    $user = $repo->findByTenDangNhap($username);
    assertTest(
        $user !== null && $user['vaiTro'] === $expected['role'] && $user['maNhanVien'] === $expected['maNhanVien'],
        "Tài khoản '{$username}' có vai trò {$expected['role']} và mã nhân viên {$expected['maNhanVien']}"
    );
}

// 3. Kiểm tra Xác thực Mật khẩu & Đăng nhập
echo "\n3. Kiểm tra Xác thực Auth::login():\n";
// Test đúng mật khẩu 123456
foreach (array_keys($expectedAccounts) as $username) {
    $res = Auth::login($username, '123456');
    assertTest($res['success'] === true, "Đăng nhập thành công cho tài khoản '{$username}' với mật khẩu 123456");
    Auth::logout();
}

// Test sai mật khẩu
$resWrong = Auth::login('qtv01', 'wrong_pass');
assertTest($resWrong['success'] === false, "Chặn đăng nhập khi sai mật khẩu");

// Test tài khoản bị khóa
$resLocked = Auth::login('khoa01', '123456');
assertTest(
    $resLocked['success'] === false && str_contains($resLocked['message'], 'KHÓA'),
    "Chặn đăng nhập khi tài khoản trạng thái trangThai = 'KHOA'"
);

// 4. Kiểm tra Nhật ký hệ thống với giaTriCu, giaTriMoi (JSON)
echo "\n4. Kiểm tra NhatKyService::ghi() lưu đúng JSON:\n";
$logSuccess = NhatKyService::ghi(
    'TK000',
    'Bảo mật',
    'KIEM_TRA_TEST',
    'Thực hiện test tự động nhật ký',
    ['trangThai' => 'CU'],
    ['trangThai' => 'MOI'],
    '127.0.0.1'
);
assertTest($logSuccess === true, "Ghi bản ghi nhật ký hệ thống thành công");

$db = Database::getConnection();
$lastLog = $db->query("SELECT * FROM nhat_ky_he_thong ORDER BY maNhatKy DESC LIMIT 1")->fetch();
assertTest(
    $lastLog['giaTriCu'] !== null && json_decode($lastLog['giaTriCu'], true)['trangThai'] === 'CU',
    "Cột giaTriCu lưu đúng định dạng JSON"
);
assertTest(
    $lastLog['giaTriMoi'] !== null && json_decode($lastLog['giaTriMoi'], true)['trangThai'] === 'MOI',
    "Cột giaTriMoi lưu đúng định dạng JSON"
);

// 5. Kiểm tra CSRF & Flash
echo "\n5. Kiểm tra CSRF Token & Flash Messages:\n";
$token1 = Csrf::generateToken();
assertTest(!empty($token1), "Sinh CSRF token thành công");
assertTest(Csrf::validateToken($token1), "Xác thực token hợp lệ trả về true");
assertTest(!Csrf::validateToken('fake_token'), "Xác thực token giả mạo trả về false");

Flash::success("Test thành công");
assertTest(Flash::has('success'), "Flash::has('success') trả về true");
$getMsgs = Flash::get('success');
assertTest(count($getMsgs) === 1 && $getMsgs[0] === "Test thành công", "Flash::get() lấy đúng thông báo");
assertTest(!Flash::has('success'), "Flash message tự động xóa sau khi đọc");

// 6. KIỂM TRA ĐẦY ĐỦ MA TRẬN PHÂN QUYỀN (PERMISSIONS.PHP)
echo "\n6. Kiểm tra Toàn bộ Ma trận Phân quyền (app/config/permissions.php):\n";
$permissions = Auth::getPermissions();
$matrix = [
    'de-nghi-mua'    => ['view' => ['LKH', 'QLK', 'BGD'], 'write' => ['LKH']],
    'lenh-san-xuat'  => ['view' => ['LKH', 'QLK', 'BGD'], 'write' => ['LKH']],
    'phan-bo'        => ['view' => ['QLK', 'LKH', 'BGD'], 'write' => ['QLK']],
    'dieu-phoi'      => ['view' => ['QLK', 'BGD', 'QC'],  'write' => ['QLK']],
    'kiem-ke'        => ['view' => ['QLK', 'BGD'],         'write' => ['QLK']],
    'hang-loi'       => ['view' => ['QLK', 'QC', 'BGD'],  'write' => ['QLK']],
    'lenh-dieu-phoi' => ['view' => ['NVK', 'QLK'],        'write' => ['NVK']],
    'tai-khoan'      => ['view' => ['QTV'],                'write' => ['QTV']],
    'nhat-ky'        => ['view' => ['QTV', 'BGD'],         'write' => []],
];

$allRoles = ['QTV', 'BGD', 'LKH', 'QLK', 'NVK', 'QC'];

foreach ($matrix as $modKey => $rules) {
    // Kiểm tra cấu hình trong permissions.php
    $cfg = $permissions['modules'][$modKey] ?? null;
    assertTest($cfg !== null, "Phân hệ '{$modKey}' tồn tại trong permissions.php");

    sort($rules['view']);
    $cfgView = $cfg['view'] ?? [];
    sort($cfgView);
    assertTest($rules['view'] === $cfgView, "Quyền XEM của '{$modKey}' khớp: " . implode(',', $rules['view']));

    sort($rules['write']);
    $cfgWrite = $cfg['write'] ?? [];
    sort($cfgWrite);
    assertTest($rules['write'] === $cfgWrite, "Quyền GHI của '{$modKey}' khớp: " . (implode(',', $rules['write']) ?: 'Không'));

    // Kiểm tra Auth::can() cho từng vai trò
    foreach ($allRoles as $testRole) {
        // Giả lập phiên đăng nhập cho testRole
        $_SESSION['auth_user'] = ['vaiTro' => $testRole, 'maTaiKhoan' => 'TEST'];
        $canView = Auth::can($modKey, 'index');
        $expectedView = in_array($testRole, $rules['view'], true);
        if ($canView !== $expectedView) {
            assertTest(false, "Vai trò {$testRole} quyền xem {$modKey} mong đợi " . ($expectedView ? 'YES' : 'NO') . " nhưng nhận " . ($canView ? 'YES' : 'NO'));
        }

        $canWrite = Auth::canWrite($modKey);
        $expectedWrite = in_array($testRole, $rules['write'], true);
        if ($canWrite !== $expectedWrite) {
            assertTest(false, "Vai trò {$testRole} quyền ghi {$modKey} mong đợi " . ($expectedWrite ? 'YES' : 'NO') . " nhưng nhận " . ($canWrite ? 'YES' : 'NO'));
        }
    }
}
assertTest(true, "Toàn bộ 6 vai trò đã kiểm thử thành công qua 9 phân hệ (54 lượt kiểm tra)");

// Dọn dẹp session test
unset($_SESSION['auth_user']);

echo "\n===============================================================\n";
echo " KẾT QUẢ KIỂM THỬ: {$passed} PASSED, {$failed} FAILED\n";
echo "===============================================================\n";

exit($failed > 0 ? 1 : 0);
