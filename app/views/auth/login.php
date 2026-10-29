<?php
use App\Core\Csrf;
use App\Core\View;
?>
<div class="auth-wrapper">
    <div class="auth-card">
        <!-- Logo & Thương hiệu -->
        <div class="auth-brand">
            <div class="auth-logo">
                <i class="bi bi-box-seam"></i>
            </div>
            <h2>HỆ THỐNG QUẢN LÝ KHO</h2>
            <p>Enterprise Warehouse Management Core · Marvel Sweet</p>
        </div>

        <!-- Flash Message (báo lỗi sai mật khẩu hoặc tài khoản khóa) -->
        <?php View::partial('flash_message'); ?>

        <!-- Form Đăng nhập -->
        <form method="POST" action="?route=auth/login">
            <?= Csrf::field() ?>

            <div class="form-group" style="margin-bottom: 18px;">
                <label class="form-label" for="username">
                    Tên đăng nhập <span class="required">*</span>
                </label>
                <div class="input-icon-wrapper">
                    <i class="bi bi-person"></i>
                    <input type="text"
                           id="username"
                           name="username"
                           class="input"
                           placeholder="Nhập mã nhân viên hoặc username"
                           value="<?= htmlspecialchars($username ?? '') ?>"
                           required
                           autofocus>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label" for="password">
                    Mật khẩu <span class="required">*</span>
                </label>
                <div class="input-icon-wrapper">
                    <i class="bi bi-shield-lock"></i>
                    <input type="password"
                           id="password"
                           name="password"
                           class="input"
                           placeholder="Nhập mật khẩu truy cập"
                           required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; min-height: 46px; font-size: 14px;">
                <i class="bi bi-box-arrow-in-right"></i>
                Đăng nhập vào hệ thống
            </button>
        </form>

        <!-- Bảng tài khoản thử nghiệm (CHỈ HIỂN THỊ KHI APP_ENV = 'dev') -->
        <?php if (($appEnv ?? '') === 'dev' && !empty($demoAccounts)): ?>
            <div class="dev-accounts-box">
                <div style="display: flex; align-items: center; justify-content: space-between; font-weight: 700; color: var(--primary);">
                    <span><i class="bi bi-code-square"></i> Tài khoản thử nghiệm (DEV)</span>
                    <span style="font-size: 10px; color: var(--muted);">Pass: <strong>123456</strong></span>
                </div>
                <table>
                    <thead>
                        <tr style="text-align: left; color: var(--muted); font-size: 10px;">
                            <th>User</th>
                            <th>Vai trò</th>
                            <th>Nhân viên</th>
                            <th>Trạng thái</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($demoAccounts as $acc): ?>
                            <tr>
                                <td><strong style="color: var(--text);"><?= htmlspecialchars($acc['tenDangNhap']) ?></strong></td>
                                <td>
                                    <span class="badge badge-ongoing" style="font-size: 10px; padding: 2px 6px;">
                                        <?= htmlspecialchars($acc['vaiTro']) ?>
                                    </span>
                                </td>
                                <td style="color: var(--muted);"><?= htmlspecialchars($acc['hoTen'] ?? '—') ?></td>
                                <td>
                                    <?php if ($acc['trangThai'] === 'KHOA'): ?>
                                        <span class="badge badge-failed" style="font-size: 9px; padding: 1px 5px;">KHÓA</span>
                                    <?php else: ?>
                                        <span class="badge badge-passed" style="font-size: 9px; padding: 1px 5px;">ACTIVE</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <button type="button"
                                            class="quick-fill-btn"
                                            onclick="quickFillLogin('<?= htmlspecialchars($acc['tenDangNhap']) ?>', '123456')">
                                        Chọn
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

