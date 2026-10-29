<?php
/**
 * Partial: filter_bar
 * @var string $title Tiêu đề bộ lọc (mặc định: 'Điều kiện tra cứu')
 * @var string|null $badgeText Nhãn bên phải (vd: 'CHẾ ĐỘ TRA CỨU ĐIỀU PHỐI')
 * @var string $formAction URL submit form
 * @var string|null $contentSlot Nội dung form HTML các ô input
 * @var string|null $infoNote Dòng ghi chú dưới form
 */
?>
<div class="card">
    <div class="card-head">
        <div class="card-head-left">
            <div class="card-icon">
                <i class="bi bi-funnel"></i>
            </div>
            <div class="card-title-text">
                <strong><?= htmlspecialchars($title ?? 'Điều kiện tra cứu') ?></strong>
                <small>Nhập tiêu chí lọc dữ liệu hệ thống</small>
            </div>
        </div>
        <?php if (!empty($badgeText)): ?>
            <div class="card-head-right">
                <span style="font-size: 11px; font-weight: 800; color: var(--muted); letter-spacing: 0.5px;">
                    <?= htmlspecialchars($badgeText) ?>
                </span>
            </div>
        <?php endif; ?>
    </div>

    <form method="GET" action="<?= htmlspecialchars($formAction ?? '') ?>">
        <?php if (!empty($hiddenInputs)): ?>
            <?php foreach ($hiddenInputs as $name => $val): ?>
                <input type="hidden" name="<?= htmlspecialchars($name) ?>" value="<?= htmlspecialchars($val) ?>">
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="card-body">
            <div class="form-grid grid-4">
                <?= $slot ?? '' ?>
            </div>
        </div>

        <div class="card-footer">
            <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--muted);">
                <i class="bi bi-info-circle" style="color: var(--primary);"></i>
                <span><?= htmlspecialchars($infoNote ?? 'Hệ thống hỗ trợ tra cứu dữ liệu thời gian thực.') ?></span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <button type="reset" class="btn btn-secondary">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    Đặt lại bộ lọc
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i>
                    Tìm kiếm dữ liệu
                </button>
            </div>
        </div>
    </form>
</div>

