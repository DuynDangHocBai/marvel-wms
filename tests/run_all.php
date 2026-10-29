<?php
/**
 * Master Test Runner: tests/run_all.php
 * Chạy toàn bộ các test suite của hệ thống Marvel WMS:
 * - Kiểm tra cú pháp (lint_syntax.php)
 * - Kiểm thử Core MVC & Security (verify_core.php)
 * - Kiểm thử nghiệp vụ 6 chức năng (kiem_ke, hang_loi, lenh_san_xuat, phan_bo, dieu_phoi, de_nghi_mua)
 * - Kiểm thử HTTP Web Endpoints & Authorization trên cổng 81
 * - Kiểm tra tính toàn vẹn của dữ liệu sau kiểm thử (Zero changes to seed)
 */

$startTime = microtime(true);

echo "======================================================================\n";
echo "   HỆ THỐNG QUẢN LÝ KHO MARVEL WMS - MASTER TEST SUITE RUNNER         \n";
echo "======================================================================\n";
echo "Thời gian bắt đầu: " . date('Y-m-d H:i:s') . "\n";
echo "Môi trường: PHP " . PHP_VERSION . " | MySQL 127.0.0.1:3306\n\n";

$phpBin = 'C:\\xampp\\php\\php.exe';

$suites = [
    [
        'name' => '1. CÚ PHÁP TẤT CẢ FILE PHP (php -l)',
        'file' => 'tests/lint_syntax.php',
    ],
    [
        'name' => '2. CORE MVC, ROUTER & SECURITY',
        'file' => 'tests/verify_core.php',
    ],
    [
        'name' => '3. CẬP NHẬT CHÊNH LỆCH KIỂM KÊ (UC 2.6.3)',
        'file' => 'tests/test_kiem_ke.php',
    ],
    [
        'name' => '4. ĐỀ XUẤT XỬ LÝ HÀNG LỖI HỎNG (UC 2.4.3)',
        'file' => 'tests/test_hang_loi.php',
    ],
    [
        'name' => '5. TẠO LỆNH SẢN XUẤT BÁNH KÈO THEO BOM (UC 2.1.3)',
        'file' => 'tests/test_lenh_san_xuat.php',
    ],
    [
        'name' => '6. PHÂN BỔ SỐ LƯỢNG NGUYÊN VẬT LIỆU (UC 2.2.3)',
        'file' => 'tests/test_phan_bo.php',
    ],
    [
        'name' => '7. ĐIỀU PHỐI NHẬP KHO & LỆNH ĐIỀU PHỐI (UC 2.5.3)',
        'file' => 'tests/test_dieu_phoi.php',
    ],
    [
        'name' => '8. LẬP ĐỀ NGHỊ MUA NGUYÊN VẬT LIỆU (UC 2.3.3)',
        'file' => 'tests/test_de_nghi_mua.php',
    ],
    [
        'name' => '9. HTTP ENDPOINTS & PHÂN QUYỀN KIỂM KÊ, HÀNG LỖI (PORT 81)',
        'file' => 'tests/test_http_endpoints.php',
    ],
    [
        'name' => '10. HTTP ENDPOINTS & PHÂN QUYỀN LỆNH SẢN XUẤT (PORT 81)',
        'file' => 'tests/test_lenh_web.php',
    ],
    [
        'name' => '11. HTTP ENDPOINTS & PHÂN QUYỀN PHÂN BỔ SỐ LƯỢNG (PORT 81)',
        'file' => 'tests/test_phan_bo_web.php',
    ],
    [
        'name' => '12. HTTP ENDPOINTS & PHÂN QUYỀN ĐIỀU PHỐI & NVK (PORT 81)',
        'file' => 'tests/test_dieu_phoi_web.php',
    ],
    [
        'name' => '13. HTTP ENDPOINTS & PHÂN QUYỀN ĐỀ NGHỊ MUA NVL (PORT 81)',
        'file' => 'tests/test_de_nghi_mua_web.php',
    ],
];

$totalSuites = count($suites);
$passedSuites = 0;
$failedSuites = 0;
$suiteResults = [];

foreach ($suites as $idx => $s) {
    $num = $idx + 1;
    echo "----------------------------------------------------------------------\n";
    echo ">> [SUITE {$num}/{$totalSuites}] {$s['name']}...\n";
    echo "----------------------------------------------------------------------\n";

    $cmd = "\"{$phpBin}\" " . escapeshellarg($s['file']) . " 2>&1";
    $output = [];
    $ret = 0;
    exec($cmd, $output, $ret);

    $outStr = implode("\n", $output);
    echo $outStr . "\n\n";

    if ($ret === 0 && !str_contains($outStr, 'FAIL') && !str_contains($outStr, 'Fatal error') && !str_contains($outStr, 'Parse error')) {
        $passedSuites++;
        $suiteResults[] = ['name' => $s['name'], 'status' => 'PASS'];
    } else {
        // Kiểm tra xem có trường hợp chữ FAIL chỉ xuất hiện trong câu in tổng kết 0 FAILED không
        if ($ret === 0 && (str_contains($outStr, '0 FAILED') || str_contains($outStr, '0 failed')) && !str_contains($outStr, '[FAIL]')) {
            $passedSuites++;
            $suiteResults[] = ['name' => $s['name'], 'status' => 'PASS'];
        } else {
            $failedSuites++;
            $suiteResults[] = ['name' => $s['name'], 'status' => 'FAIL'];
        }
    }
}

// Kiểm tra toàn vẹn dữ liệu database seed sau tất cả các test
echo "======================================================================\n";
echo " KIỂM TRA TÍNH TOÀN VẸN DỮ LIỆU SEED (ZERO SIDE-EFFECT VERIFICATION)  \n";
echo "======================================================================\n";

try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=marvel_wms;charset=utf8mb4", 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $stmt = $pdo->query("SELECT maSKU, tonKhaDung FROM vw_ton_kho_kha_dung WHERE maSKU IN ('NVL001', 'NVL005', 'NVL006')");
    $stock = [];
    while ($r = $stmt->fetch()) {
        $stock[$r['maSKU']] = (float)$r['tonKhaDung'];
    }

    $c1 = ($stock['NVL001'] ?? 0) == 30.0;
    $c2 = ($stock['NVL005'] ?? 0) == 12.0;
    $c3 = ($stock['NVL006'] ?? 0) == 20.0;

    echo "Tồn khả dụng NVL001 = " . ($stock['NVL001'] ?? 'null') . " kg (kỳ vọng 30.0 kg): " . ($c1 ? "[PASS]" : "[FAIL]") . "\n";
    echo "Tồn khả dụng NVL005 = " . ($stock['NVL005'] ?? 'null') . " kg (kỳ vọng 12.0 kg): " . ($c2 ? "[PASS]" : "[FAIL]") . "\n";
    echo "Tồn khả dụng NVL006 = " . ($stock['NVL006'] ?? 'null') . " kg (kỳ vọng 20.0 kg): " . ($c3 ? "[PASS]" : "[FAIL]") . "\n";

    $dbIntegrityPass = ($c1 && $c2 && $c3);
} catch (\Throwable $e) {
    echo "[FAIL] Lỗi kiểm tra database: " . $e->getMessage() . "\n";
    $dbIntegrityPass = false;
}

$elapsed = round(microtime(true) - $startTime, 2);

echo "\n======================================================================\n";
echo "                     BÁO CÁO TỔNG HỢP KIỂM THỬ                        \n";
echo "======================================================================\n";
foreach ($suiteResults as $r) {
    printf("  %-65s [%s]\n", $r['name'], $r['status']);
}
echo "----------------------------------------------------------------------\n";
echo "TỔNG SỐ TEST SUITES: {$totalSuites}\n";
echo "THÀNH CÔNG: {$passedSuites} suites\n";
echo "THẤT BẠI:   {$failedSuites} suites\n";
echo "TOÀN VẸN SEED DỮ LIỆU: " . ($dbIntegrityPass ? "ĐẠT (Dữ liệu không đổi)" : "KHÔNG ĐẠT") . "\n";
echo "THỜI GIAN THỰC THI: {$elapsed} giây\n";
echo "======================================================================\n";

if ($failedSuites === 0 && $dbIntegrityPass) {
    echo "\n>>> KẾT QUẢ CUỐI CÙNG: 100% TẤT CẢ CÁC BÀI TEST ĐỀU ĐẠT (ALL PASSED) <<<\n\n";
    exit(0);
} else {
    echo "\n>>> CẢNH BÁO: CÓ BÀI TEST THẤT BẠI HOẶC DỮ LIỆU BỊ BIẾN ĐỔI! <<<\n\n";
    exit(1);
}

