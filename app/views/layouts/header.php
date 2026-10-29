<?php
use App\Core\Auth;
use App\Core\Csrf;

$user = Auth::user();
$pageTitle = $pageTitle ?? 'Quản lý điều hành';
$breadcrumbSection = $breadcrumbSection ?? 'Kho vận';
?>

<header class="top-header">
    <div class="header-left">
        <div class="header-logo-icon">
            <i class="bi bi-box-seam"></i>
        </div>
        <div class="header-breadcrumb">
            <span><?= htmlspecialchars($breadcrumbSection) ?></span>
            <i class="bi bi-chevron-right"></i>
            <strong><?= htmlspecialchars($pageTitle) ?></strong>
        </div>
    </div>

    <div class="header-right">
        <!-- Pill trạng thái hệ thống -->
        <div class="header-status-pill">
            <span class="status-dot"></span>
            <span>Máy quét: Hoạt động</span>
        </div>

        <!-- Chuông thông báo (hiển thị số thực tế: 0 khi chưa có thông báo) -->
        <button class="notification-btn" type="button" title="Thông báo hệ thống">
            <i class="bi bi-bell"></i>
            <span class="notification-badge" style="background: var(--muted-light);">0</span>
        </button>

        <div class="header-divider"></div>

        <!-- Khối người dùng -->
        <?php if ($user): ?>
            <div class="user-profile" title="Tài khoản: <?= htmlspecialchars($user['tenDangNhap']) ?>">
                <div class="user-avatar">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div class="user-info">
                    <strong><?= htmlspecialchars($user['hoTen']) ?></strong>
                    <small><?= htmlspecialchars($user['chucDanh']) ?> (<?= htmlspecialchars($user['vaiTro']) ?>)</small>
                </div>
            </div>

            <!-- Nút đăng xuất -->
            <a href="?route=auth/logout" class="btn-logout" title="Đăng xuất khỏi hệ thống">
                <i class="bi bi-box-arrow-right"></i>
                <span>Đăng xuất</span>
            </a>
        <?php else: ?>
            <a href="?route=auth/login" class="btn btn-primary btn-sm">
                <i class="bi bi-box-arrow-in-right"></i>
                Đăng nhập
            </a>
        <?php endif; ?>
    </div>
</header>

