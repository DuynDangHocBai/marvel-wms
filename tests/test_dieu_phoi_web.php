<?php
require_once __DIR__ . '/test_http_endpoints.php';

echo "\n--- KIỂM THỬ HTTP ĐIỀU PHỐI NHẬP KHO & LỆNH ĐIỀU PHỐI (PORT 81) ---\n";

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

// 2. GET dieu-phoi/index
$idxRes = httpRequest($baseUrl . '?route=dieu-phoi/index', 'GET', [], $cookies);
echo "2. GET dieu-phoi/index: HTTP " . $idxRes['code'] . " - " . (str_contains($idxRes['body'], 'Điều phối nhập kho lưu trữ hàng hóa') ? "PASS" : "FAIL") . PHP_EOL;

// 3. GET dieu-phoi/create
$createRes = httpRequest($baseUrl . '?route=dieu-phoi/create&req_id=YCN26100002', 'GET', [], $cookies);
echo "3. GET dieu-phoi/create: HTTP " . $createRes['code'] . " - " . (str_contains($createRes['body'], 'Điều phối vị trí lưu trữ nhập kho') ? "PASS" : "FAIL") . PHP_EOL;

// 4. GET dieu-phoi/detail
$detRes = httpRequest($baseUrl . '?route=dieu-phoi/detail&id=YCN26100002', 'GET', [], $cookies);
echo "4. GET dieu-phoi/detail: HTTP " . $detRes['code'] . " - " . (str_contains($detRes['body'], 'YCN26100002') ? "PASS" : "FAIL") . PHP_EOL;

// 5. Login as NVK
$resNvk = httpRequest($baseUrl . '?route=auth/login');
preg_match('/name="csrf_token"\s+value="([^"]+)"/', $resNvk['body'], $m);
$csrfNvk = $m[1] ?? '';

$loginNvk = httpRequest($baseUrl . '?route=auth/login', 'POST', [
    'username'   => 'nvk01',
    'password'   => '123456',
    'csrf_token' => $csrfNvk
], $resNvk['cookies']);
$cookiesNvk = $loginNvk['cookies'];
echo "5. Đăng nhập nvk01: HTTP " . $loginNvk['code'] . PHP_EOL;

// 6. NVK GET lenh-dieu-phoi/index
$nvkIdx = httpRequest($baseUrl . '?route=lenh-dieu-phoi/index', 'GET', [], $cookiesNvk);
echo "6. NVK GET lenh-dieu-phoi/index: HTTP " . $nvkIdx['code'] . " - " . (str_contains($nvkIdx['body'], 'Nhiệm vụ bốc dỡ & xếp dỡ hàng vào ô kệ') ? "PASS" : "FAIL") . PHP_EOL;

// 7. NVK GET lenh-dieu-phoi/detail
$nvkDet = httpRequest($baseUrl . '?route=lenh-dieu-phoi/detail&id=LDP26100001', 'GET', [], $cookiesNvk);
echo "7. NVK GET lenh-dieu-phoi/detail: HTTP " . $nvkDet['code'] . " - " . (str_contains($nvkDet['body'], 'LDP26100001') ? "PASS" : "FAIL") . PHP_EOL;

// 8. NVK cannot access dieu-phoi/create (Forbidden)
$nvkCreate = httpRequest($baseUrl . '?route=dieu-phoi/create', 'GET', [], $cookiesNvk);
$isForbiddenNvk = ($nvkCreate['code'] === 403 || str_contains($nvkCreate['header'], 'error/forbidden'));
echo "8. NVK vào dieu-phoi/create: " . ($isForbiddenNvk ? "PASS (Chặn 403)" : "FAIL") . PHP_EOL;

