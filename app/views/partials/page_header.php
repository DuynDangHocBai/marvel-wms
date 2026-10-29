<?php
/**
 * Partial: page_header
 * @var string $title Tiêu đề chính trang
 * @var string|null $subtitle Mô tả ngắn gọn
 * @var array|null $breadcrumb Mảng [label => url]
 * @var string|null $tagPill Nhãn pill nhỏ cạnh tiêu đề (vd: 'DPK Console', 'BETA')
 */

use App\Core\Auth;

$currentUser = Auth::user();
?>
<div class="page-header">
    <div class="page-title-group">
        <?php if (!empty($breadcrumb)): ?>
            <div class="page-breadcrumb-sub">
                <?php $i = 0; $len = count($breadcrumb); ?>
                <?php foreach ($breadcrumb as $label => $url): ?>
                    <?php if ($i > 0): ?><i class="bi bi-chevron-right"></i><?php endif; ?>
                    <?php if ($url): ?>
                        <a href="<?= htmlspecialchars($url) ?>"><?= htmlspecialchars($label) ?></a>
                    <?php else: ?>
                        <span><?= htmlspecialchars($label) ?></span>
                    <?php endif; ?>
                    <?php $i++; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="page-title-row">
            <h1><?= htmlspecialchars($title ?? 'Trang chủ') ?></h1>
            <?php if (!empty($tagPill)): ?>
                <span class="page-tag-pill"><?= htmlspecialchars($tagPill) ?></span>
            <?php endif; ?>
        </div>

        <?php if (!empty($subtitle)): ?>
            <p class="page-desc"><?= htmlspecialchars($subtitle) ?></p>
        <?php endif; ?>
    </div>

    <?php if ($currentUser): ?>
        <div class="user-identity-badge">
            <div class="badge-avatar">
                <i class="bi bi-person-fill"></i>
            </div>
            <div class="badge-text">
                <strong><?= htmlspecialchars($currentUser['hoTen']) ?></strong>
                <small><?= htmlspecialchars($currentUser['chucDanh']) ?> (<?= htmlspecialchars($currentUser['vaiTro']) ?>)</small>
            </div>
        </div>
    <?php endif; ?>
</div>

