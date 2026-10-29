<?php
/**
 * Partial: empty_state
 * @var string $title Tiêu đề
 * @var string|null $description Mô tả
 * @var string|null $actionUrl Nút hành động
 * @var string|null $actionText Nhãn nút
 */
?>
<div class="card" style="text-align: center; padding: 60px 24px;">
    <div style="width: 64px; height: 64px; margin: 0 auto 16px; background: var(--surface-alt); border-radius: 50%; display: grid; place-items: center; color: var(--muted-light); font-size: 28px;">
        <i class="bi bi-folder2-open"></i>
    </div>
    <h3 style="margin: 0 0 8px; font-size: 18px; font-weight: 750; color: var(--text);">
        <?= htmlspecialchars($title ?? 'Chưa có dữ liệu') ?>
    </h3>
    <p style="margin: 0 auto 20px; max-width: 440px; font-size: 13px; color: var(--muted); line-height: 1.6;">
        <?= htmlspecialchars($description ?? 'Hiện tại chưa có bản ghi nào trong phân hệ này.') ?>
    </p>
    <?php if (!empty($actionUrl)): ?>
        <div>
            <a href="<?= htmlspecialchars($actionUrl) ?>" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i>
                <?= htmlspecialchars($actionText ?? 'Tạo mới') ?>
            </a>
        </div>
    <?php endif; ?>
</div>

