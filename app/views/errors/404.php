<?php
use App\Core\View;
?>

<?php View::partial('page_header', [
    'title' => '404 - Không tìm thấy trang',
    'subtitle' => 'Đường dẫn bạn yêu cầu không tồn tại hoặc đã được di chuyển trên hệ thống.',
    'breadcrumb' => [
        'Trang chủ' => '?route=dashboard/index',
        'Lỗi đường dẫn' => null,
    ],
    'tagPill' => 'NOT FOUND 404',
]); ?>

<div class="card" style="text-align: center; padding: 60px 24px; max-width: 680px; margin: 0 auto 30px;">
    <div style="width: 80px; height: 80px; margin: 0 auto 20px; background: var(--surface-alt); border: 2px solid var(--border); border-radius: 50%; display: grid; place-items: center; color: var(--muted-light); font-size: 36px;">
        <i class="bi bi-question-circle"></i>
    </div>

    <h2 style="margin: 0 0 12px; font-size: 22px; font-weight: 800; color: var(--text);">
        Không tìm thấy trang yêu cầu
    </h2>

    <p style="margin: 0 auto 24px; color: var(--muted); line-height: 1.7; max-width: 500px; font-size: 14px;">
        Route: <code style="background: #EEF2F6; padding: 2px 8px; border-radius: 4px; color: var(--primary);"><?= htmlspecialchars($requestedRoute ?? $_GET['route'] ?? 'N/A') ?></code><br>
        Hệ thống không tìm thấy tài nguyên tương ứng trong danh mục định tuyến hợp lệ.
    </p>

    <div style="display: flex; align-items: center; justify-content: center; gap: 12px;">
        <a href="?route=dashboard/index" class="btn btn-primary">
            <i class="bi bi-house-door"></i>
            Về Trang chủ
        </a>
    </div>
</div>

