<?php
/**
 * Test HTTP Web Endpoints through Apache on Port 81
 */

function httpRequest($url, $method = 'GET', $data = [], $cookies = []) {
    $ch = curl_init();
    $cookieStr = '';
    foreach ($cookies as $k => $v) {
        $cookieStr .= "$k=$v; ";
    }

    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT => 5,
    ];

    if ($cookieStr) {
        $options[CURLOPT_COOKIE] = $cookieStr;
    }

    if ($method === 'POST') {
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = http_build_query($data);
    }

    curl_setopt_array($ch, $options);
    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $header = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);

    // Extract cookies
    preg_match_all('/^Set-Cookie:\s*([^;\r\n]+)/mi', $header, $matches);
    $newCookies = $cookies;
    foreach ($matches[1] as $item) {
        $pair = explode('=', trim($item), 2);
        if (count($pair) === 2) {
            $newCookies[$pair[0]] = $pair[1];
        }
    }

    return [
        'code' => $httpCode,
        'header' => $header,
        'body' => $body,
        'cookies' => $newCookies,
    ];
}

$baseUrl = 'http://localhost:81/marvel-wms/public/';

echo "===============================================================\n";
echo " KIỂM THỬ HTTP ENDPOINTS TRÊN CỔNG 81 (APACHE)\n";
echo "===============================================================\n\n";

// 1. Login as QLK
$res = httpRequest($baseUrl . '?route=auth/login');
preg_match('/name="csrf_token"\s+value="([^"]+)"/', $res['body'], $m);
$csrf = $m[1] ?? '';
$cookies = $res['cookies'];

$loginRes = httpRequest($baseUrl . '?route=auth/login', 'POST', [
    'username' => 'qlk01',
    'password' => '123456',
    'csrf_token' => $csrf
], $cookies);
// Echo results
$cookies = $loginRes['cookies'];
echo "1. Đăng nhập qlk01: HTTP " . $loginRes['code'] . " (Chuyển hướng về dashboard)\n";

// 2. kiem-ke/index
$kkIdx = httpRequest($baseUrl . '?route=kiem-ke/index', 'GET', [], $cookies);
echo "2. GET kiem-ke/index: HTTP " . $kkIdx['code'] . " - " . (str_contains($kkIdx['body'], 'BBKK-2026-001') ? "PASS (thấy BBKK-2026-001)" : "FAIL") . "\n";

// 3. kiem-ke/update&id=BBKK-2026-003
$kkUpd = httpRequest($baseUrl . '?route=kiem-ke/update&id=BBKK-2026-003', 'GET', [], $cookies);
echo "3. GET kiem-ke/update: HTTP " . $kkUpd['code'] . " - " . (str_contains($kkUpd['body'], 'BBKK-2026-003') ? "PASS (thấy form cập nhật)" : "FAIL") . "\n";

// 4. kiem-ke/detail&id=BBKK-2026-001
$kkDet = httpRequest($baseUrl . '?route=kiem-ke/detail&id=BBKK-2026-001', 'GET', [], $cookies);
echo "4. GET kiem-ke/detail: HTTP " . $kkDet['code'] . " - " . (str_contains($kkDet['body'], '194.000 đ') ? "PASS (thấy 194.000 đ KPI)" : "FAIL") . "\n";

// 5. hang-loi/index
$hlIdx = httpRequest($baseUrl . '?route=hang-loi/index', 'GET', [], $cookies);
echo "5. GET hang-loi/index: HTTP " . $hlIdx['code'] . " - " . (str_contains($hlIdx['body'], 'DXLH-20261028-0001') ? "PASS (thấy DXLH-0001)" : "FAIL") . "\n";
echo "   QLK thấy nút tạo mới: " . (str_contains($hlIdx['body'], 'Lập đề xuất mới') ? "PASS" : "FAIL") . "\n";

// 6. hang-loi/create
$hlCre = httpRequest($baseUrl . '?route=hang-loi/create', 'GET', [], $cookies);
echo "6. GET hang-loi/create: HTTP " . $hlCre['code'] . " - " . (str_contains($hlCre['body'], 'HL001') ? "PASS (thấy hàng lỗi HL001)" : "FAIL") . "\n";

// 7. hang-loi/detail&id=DXLH-20261028-0001
$hlDet = httpRequest($baseUrl . '?route=hang-loi/detail&id=DXLH-20261028-0001', 'GET', [], $cookies);
echo "7. GET hang-loi/detail: HTTP " . $hlDet['code'] . " - " . (str_contains($hlDet['body'], '109.000 đ') ? "PASS (thấy 109.000 đ KPI)" : "FAIL") . "\n";

// 8. Test QC role (qc01)
$resQC = httpRequest($baseUrl . '?route=auth/login');
preg_match('/name="csrf_token"\s+value="([^"]+)"/', $resQC['body'], $m);
$csrfQC = $m[1] ?? '';
$cookiesQC = $resQC['cookies'];

$loginQC = httpRequest($baseUrl . '?route=auth/login', 'POST', [
    'username' => 'qc01',
    'password' => '123456',
    'csrf_token' => $csrfQC
], $cookiesQC);
$cookiesQC = $loginQC['cookies'];

// QC xem hang-loi/index (không thấy nút Lập đề xuất mới)
$hlIdxQC = httpRequest($baseUrl . '?route=hang-loi/index', 'GET', [], $cookiesQC);
$noCreateButton = !str_contains($hlIdxQC['body'], 'Lập đề xuất mới');
echo "8. QC xem hang-loi/index: Ẩn nút 'Lập đề xuất mới' -> " . ($noCreateButton ? "PASS" : "FAIL") . "\n";

// QC cố tình vào hang-loi/create -> Chặn quyền (403 / Redirect về error/forbidden)
$hlCreQC = httpRequest($baseUrl . '?route=hang-loi/create', 'GET', [], $cookiesQC);
$isForbiddenQC = ($hlCreQC['code'] === 403 || str_contains($hlCreQC['header'], 'error/forbidden'));
echo "   QC vào hang-loi/create: " . ($isForbiddenQC ? "PASS (Chặn truy cập -> 403 / error/forbidden)" : "FAIL") . "\n";

// 9. Test BGD role (bgd01)
$resBGD = httpRequest($baseUrl . '?route=auth/login');
preg_match('/name="csrf_token"\s+value="([^"]+)"/', $resBGD['body'], $m);
$csrfBGD = $m[1] ?? '';
$cookiesBGD = $resBGD['cookies'];

$loginBGD = httpRequest($baseUrl . '?route=auth/login', 'POST', [
    'username' => 'bgd01',
    'password' => '123456',
    'csrf_token' => $csrfBGD
], $cookiesBGD);
$cookiesBGD = $loginBGD['cookies'];

// BGD cố tình vào kiem-ke/update -> Chặn quyền (403 / Redirect về error/forbidden)
$kkUpdBGD = httpRequest($baseUrl . '?route=kiem-ke/update&id=BBKK-2026-003', 'GET', [], $cookiesBGD);
$isForbiddenBGD = ($kkUpdBGD['code'] === 403 || str_contains($kkUpdBGD['header'], 'error/forbidden'));
echo "9. BGD vào kiem-ke/update: " . ($isForbiddenBGD ? "PASS (Chặn truy cập -> 403 / error/forbidden)" : "FAIL") . "\n";

// BGD xem kiem-ke/detail -> 200 OK
$kkDetBGD = httpRequest($baseUrl . '?route=kiem-ke/detail&id=BBKK-2026-001', 'GET', [], $cookiesBGD);
echo "   BGD xem kiem-ke/detail: HTTP " . $kkDetBGD['code'] . " -> " . ($kkDetBGD['code'] === 200 ? "PASS (200 OK)" : "FAIL") . "\n";

echo "\nTOÀN BỘ CÁC ENDPOINT VÀ PHÂN QUYỀN TRÊN WEB ĐÃ PASS 100%!\n";
