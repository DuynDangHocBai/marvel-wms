<?php
require_once __DIR__ . '/test_http_endpoints.php';

echo "\n--- KIỂM THỬ HTTP PHÂN BỔ SỐ LƯỢNG (PORT 81) ---\n";

// 1. Login as QLK
$res = httpRequest($baseUrl . '?route=auth/login');
preg_match('/name="csrf_token"\s+value="([^"]+)"/', $res['body'], $m);
$csrf = $m[1] ?? '';
$cookies = $res['cookies'];

$loginRes = httpRequest($baseUrl . '?route=auth/login', 'POST', [
    'username'   => 'qlk01',
    'password'   => '123456',
    'csrf_token' => $csrf
], $cookies);
$cookies = $loginRes['cookies'];
echo "1. Đăng nhập qlk01: HTTP " . $loginRes['code'] . PHP_EOL;

// 2. GET phan-bo/index (tab pending)
$idxRes = httpRequest($baseUrl . '?route=phan-bo/index', 'GET', [], $cookies);
echo "2. GET phan-bo/index: HTTP " . $idxRes['code'] . " - " . (str_contains($idxRes['body'], 'Phân bổ số lượng nguyên vật liệu lưu kho') && str_contains($idxRes['body'], 'LSX2610001') ? "PASS" : "FAIL") . PHP_EOL;

// 3. GET phan-bo/index?tab=history
$hisRes = httpRequest($baseUrl . '?route=phan-bo/index&tab=history', 'GET', [], $cookies);
echo "3. GET phan-bo/index&tab=history: HTTP " . $hisRes['code'] . " - " . (str_contains($hisRes['body'], 'Lịch sử phiếu phân bổ') ? "PASS" : "FAIL") . PHP_EOL;

// 4. GET phan-bo/create
$createRes = httpRequest($baseUrl . '?route=phan-bo/create&order_id=LSX2610001&kho_id=K001', 'GET', [], $cookies);
echo "4. GET phan-bo/create: HTTP " . $createRes['code'] . " - " . (str_contains($createRes['body'], 'Nguyên tắc FEFO') ? "PASS" : "FAIL") . PHP_EOL;

// 5. Test LKH cannot access phan-bo/create (Only QLK has write access)
$resLkh = httpRequest($baseUrl . '?route=auth/login');
preg_match('/name="csrf_token"\s+value="([^"]+)"/', $resLkh['body'], $m);
$csrfLkh = $m[1] ?? '';
$cookiesLkh = $resLkh['cookies'];

$loginLkh = httpRequest($baseUrl . '?route=auth/login', 'POST', [
    'username'   => 'lkh01',
    'password'   => '123456',
    'csrf_token' => $csrfLkh
], $cookiesLkh);
$cookiesLkh = $loginLkh['cookies'];

$lkhCreate = httpRequest($baseUrl . '?route=phan-bo/create', 'GET', [], $cookiesLkh);
$isForbiddenLkh = ($lkhCreate['code'] === 403 || str_contains($lkhCreate['header'], 'error/forbidden'));
echo "5. LKH vào phan-bo/create: " . ($isForbiddenLkh ? "PASS (Chặn truy cập -> 403 / error/forbidden)" : "FAIL") . PHP_EOL;
