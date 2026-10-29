<?php
// Script kiểm tra cú pháp toàn bộ file .php trong dự án
$root = realpath(__DIR__ . '/..');
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS)
);

$count = 0;
$errors = [];

foreach ($iterator as $item) {
    if ($item->isFile() && $item->getExtension() === 'php') {
        $path = $item->getPathname();
        
        // Bỏ qua thư mục vendor hoặc .system_generated nếu có
        if (str_contains($path, '.system_generated') || str_contains($path, '.git')) {
            continue;
        }

        $cmd = '"C:\\xampp\\php\\php.exe" -l ' . escapeshellarg($path) . ' 2>&1';
        exec($cmd, $out, $ret);
        if ($ret !== 0) {
            $errors[] = [
                'file' => $path,
                'msg'  => implode("\n", $out)
            ];
        }
        $count++;
    }
}

echo "===============================================================\n";
echo " KIỂM TRA CÚ PHÁP TẤT CẢ FILE PHP (php -l)\n";
echo "===============================================================\n";
echo "Đã quét tổng cộng: {$count} files PHP\n";

if (empty($errors)) {
    echo "[PASS] 100% các file PHP đều có cú pháp hợp lệ, không có lỗi!\n";
    exit(0);
} else {
    echo "[FAIL] Có " . count($errors) . " file bị lỗi cú pháp:\n";
    foreach ($errors as $e) {
        echo "File: " . $e['file'] . "\n" . $e['msg'] . "\n\n";
    }
    exit(1);
}

