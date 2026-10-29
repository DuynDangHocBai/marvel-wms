<?php
require_once __DIR__ . '/test_http_endpoints.php';

// Test LKH login
$res = httpRequest($baseUrl . '?route=auth/login');
preg_match('/name="csrf_token"\s+value="([^"]+)"/', $res['body'], $m);
$csrf = $m[1] ?? '';
$cookies = $res['cookies'];

$loginRes = httpRequest($baseUrl . '?route=auth/login', 'POST', [
    'username' => 'lkh01',
    'password' => '123456',
    'csrf_token' => $csrf
], $cookies);
$cookies = $loginRes['cookies'];
echo "1. Đăng nhập lkh01: HTTP " . $loginRes['code'] . PHP_EOL;

$idxRes = httpRequest($baseUrl . '?route=lenh-san-xuat/index', 'GET', [], $cookies);
echo "2. GET lenh-san-xuat/index: HTTP " . $idxRes['code'] . " - " . (str_contains($idxRes['body'], 'Danh sách lệnh sản xuất') ? "PASS" : "FAIL") . PHP_EOL;

$createRes = httpRequest($baseUrl . '?route=lenh-san-xuat/create', 'GET', [], $cookies);
echo "3. GET lenh-san-xuat/create: HTTP " . $createRes['code'] . " - " . (str_contains($createRes['body'], 'Tạo lệnh sản xuất bánh kẹo theo BOM') ? "PASS" : "FAIL") . PHP_EOL;

$detailRes = httpRequest($baseUrl . '?route=lenh-san-xuat/detail&id=LSX2610001', 'GET', [], $cookies);
echo "4. GET lenh-san-xuat/detail: HTTP " . $detailRes['code'] . " - " . (str_contains($detailRes['body'], 'LSX2610001') ? "PASS" : "FAIL") . PHP_EOL;
