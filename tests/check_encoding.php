<?php
$files = ['database/schema.sql', 'database/seed.sql'];
foreach ($files as $f) {
    $content = file_get_contents($f);
    $hex = bin2hex(substr($content, 0, 4));
    echo "{$f} first 4 bytes: {$hex}\n";
    if (str_starts_with($hex, 'efbbbf')) {
        echo "  -> CẢNH BÁO: Có UTF-8 BOM!\n";
    } else {
        echo "  -> ĐẠT: Không có BOM (Clean UTF-8)\n";
    }
    if (str_contains($content, 'SET NAMES utf8mb4;')) {
        echo "  -> ĐẠT: Có 'SET NAMES utf8mb4;'\n";
    } else {
        echo "  -> CHƯA CÓ: Cần thêm 'SET NAMES utf8mb4;'\n";
    }
}

