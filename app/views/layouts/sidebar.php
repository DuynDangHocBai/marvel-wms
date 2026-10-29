<?php
use App\Core\Auth;

$currentRoute = $_GET['route'] ?? 'dashboard/index';
$currentModule = explode('/', $currentRoute)[0] ?? '';

// Đọc cấu hình phân quyền từ app/config/permissions.php
$permissions = Auth::getPermissions();
$modules = $permissions['modules'] ?? [];
$groups = $permissions['groups'] ?? [];

// Đọc cấu hình APP_ENV
$configFile = dirname(__DIR__, 3) . '/config/config.php';
$config = file_exists($configFile) ? require $configFile : [];
$appEnv = $config['app_env'] ?? 'dev';
?>

<aside class="sidebar">
    <!-- Brand Logo -->
    <div class="sidebar-brand">
        <div class="brand-icon">
            <i class="bi bi-box-seam"></i>
        </div>
        <div class="brand-text">
            <strong>HỆ THỐNG QUẢN LÝ KHO</strong>
            <small>Enterprise WMS Core</small>
        </div>
    </div>

    <!-- Menu phân theo nhóm bộ phận -->
    <div class="sidebar-section-label">TỔNG QUAN</div>
    <nav class="sidebar-menu">
        <a href="?route=dashboard/index" class="menu-item <?= $currentRoute === 'dashboard/index' ? 'active' : '' ?>">
            <i class="bi bi-grid"></i>
            <span>Trang chủ</span>
        </a>
    </nav>

    <?php foreach ($groups as $groupKey => $groupDef): ?>
        <?php
            // Lọc các module thuộc nhóm mà vai trò hiện tại được phép XEM
            $visibleModules = array_filter($modules, function ($mod, $modKey) use ($groupKey) {
                return ($mod['group'] ?? '') === $groupKey && Auth::can($modKey, 'index');
            }, ARRAY_FILTER_USE_BOTH);
        ?>

        <?php if (!empty($visibleModules)): ?>
            <div class="sidebar-section-label"><?= htmlspecialchars($groupDef['label']) ?></div>
            <nav class="sidebar-menu">
                <?php foreach ($visibleModules as $modKey => $mod): ?>
                    <?php
                        $isActive = ($currentModule === $modKey);
                    ?>
                    <a href="<?= htmlspecialchars($mod['default_url']) ?>"
                       class="menu-item <?= $isActive ? 'active' : '' ?>"
                       title="<?= htmlspecialchars($mod['description'] ?? '') ?>">
                        <i class="bi <?= htmlspecialchars($mod['icon']) ?>"></i>
                        <span><?= htmlspecialchars($mod['name']) ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php if ($appEnv === 'dev'): ?>
        <div class="sidebar-section-label">KIỂM THỬ (DEV)</div>
        <nav class="sidebar-menu">
            <a href="?route=uikit/index" class="menu-item <?= $currentRoute === 'uikit/index' ? 'active' : '' ?>">
                <i class="bi bi-palette"></i>
                <span>Thư viện UI Kit</span>
            </a>
        </nav>
    <?php endif; ?>

    <!-- Đáy sidebar: Trạng thái kết nối + Thông tin phiên bản -->
    <div class="sidebar-bottom">
        <div class="system-status-card">
            <span class="status-dot pulse"></span>
            <div>
                <strong>Máy quét v1.4 - Kho Tổng</strong>
                <small>Đang kết nối trực tiếp</small>
            </div>
        </div>
        <div class="sidebar-version">
            <span><i class="bi bi-question-circle"></i> Trợ giúp</span>
            <span>v1.0.0</span>
        </div>
    </div>
</aside>

