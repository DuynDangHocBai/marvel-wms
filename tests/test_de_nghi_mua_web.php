<?php
require_once __DIR__ . '/test_http_endpoints.php';

echo "\n--- KIỂM THỬ HTTP ĐỀ NGHỊ MUA NVL (PORT 81) ---\n";

// 1. Đăng nhập LKH
$res = httpRequest($baseUrl . '?route=auth/login');
preg_match('/name="csrf_token"\s+value="([^"]+)"/', $res['body'], $m);
$csrf = $m[1] ?? '';
$cookies = $res['cookies'];

$loginRes = httpRequest($baseUrl . '?route=auth/login', 'POST', [
    'username'   => 'lkh01',
    'password'   => '123456',
    'csrf_token' => $csrf
], $cookies);
$cookiesLkh = $loginRes['cookies'];
echo "1. Đăng nhập lkh01: HTTP " . $loginRes['code'] . PHP_EOL;

// 2. GET de-nghi-mua/index
$idxRes = httpRequest($baseUrl . '?route=de-nghi-mua/index', 'GET', [], $cookiesLkh);
$idxPass = ($idxRes['code'] === 200 && str_contains($idxRes['body'], 'Đề nghị mua nguyên vật liệu'));
echo "2. GET de-nghi-mua/index: HTTP " . $idxRes['code'] . " - " . ($idxPass ? "PASS" : "FAIL") . PHP_EOL;

// 3. GET de-nghi-mua/create với plan_id=KHSX004
$createRes = httpRequest($baseUrl . '?route=de-nghi-mua/create&plan_id=KHSX004', 'GET', [], $cookiesLkh);
$createPass = ($createRes['code'] === 200 && str_contains($createRes['body'], 'Lập đề nghị mua NVL mới') && str_contains($createRes['body'], 'KHSX004'));
echo "3. GET de-nghi-mua/create: HTTP " . $createRes['code'] . " - " . ($createPass ? "PASS" : "FAIL") . PHP_EOL;

// 4. GET de-nghi-mua/detail (kiểm tra với id thực tế từ DB hoặc từ trang index)
preg_match('/route=de-nghi-mua\/detail&(?:amp;)?id=([^"\'\s&]+)/', $idxRes['body'], $dm);
$sampleId = $dm[1] ?? 'DNM-20261027-568A973';
$detailRes = httpRequest($baseUrl . '?route=de-nghi-mua/detail&id=' . urlencode($sampleId), 'GET', [], $cookiesLkh);
$detailPass = ($detailRes['code'] === 200 && (str_contains($detailRes['body'], 'Đề nghị mua') || str_contains($detailRes['body'], 'Chi tiết đề nghị mua')));
echo "4. GET de-nghi-mua/detail (" . $sampleId . "): HTTP " . $detailRes['code'] . " - " . ($detailPass ? "PASS" : "FAIL") . PHP_EOL;

// 5. Đăng nhập QLK (để kiểm tra phân quyền xem & chặn tạo)
$resQlk = httpRequest($baseUrl . '?route=auth/login');
preg_match('/name="csrf_token"\s+value="([^"]+)"/', $resQlk['body'], $m);
$csrfQlk = $m[1] ?? '';

$loginQlk = httpRequest($baseUrl . '?route=auth/login', 'POST', [
    'username'   => 'qlk01',
    'password'   => '123456',
    'csrf_token' => $csrfQlk
], $resQlk['cookies']);
$cookiesQlk = $loginQlk['cookies'];
echo "5. Đăng nhập qlk01: HTTP " . $loginQlk['code'] . PHP_EOL;

// 6. QLK GET de-nghi-mua/index (Được phép xem)
$qlkIdx = httpRequest($baseUrl . '?route=de-nghi-mua/index', 'GET', [], $cookiesQlk);
$qlkIdxPass = ($qlkIdx['code'] === 200 && str_contains($qlkIdx['body'], 'Đề nghị mua'));
echo "6. QLK xem de-nghi-mua/index: HTTP " . $qlkIdx['code'] . " - " . ($qlkIdxPass ? "PASS (Xem thành công)" : "FAIL") . PHP_EOL;

// 7. QLK GET de-nghi-mua/create (Bị chặn 403 Forbidden)
$qlkCreate = httpRequest($baseUrl . '?route=de-nghi-mua/create', 'GET', [], $cookiesQlk);
$isForbiddenQlk = ($qlkCreate['code'] === 403 || str_contains($qlkCreate['header'], 'error/forbidden') || str_contains($qlkCreate['body'], 'không có quyền'));
echo "7. QLK vào de-nghi-mua/create: " . ($isForbiddenQlk ? "PASS (Chặn 403)" : "FAIL") . PHP_EOL;
