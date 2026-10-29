<?php
use App\Core\Auth;
use App\Core\View;

$user = Auth::user();
?>

<?php View::partial('page_header', [
    'title' => '403 - Quyền truy cập bị từ chối',
    'subtitle' => 'Tài khoản của bạn không được cấp phép để thực hiện thao tác hoặc truy cập phân hệ này.',
    'breadcrumb' => [
        'Trang chủ' => '?route=dashboard/index',
        'Lỗi bảo mật' => null,
    ],
    'tagPill' => 'FORBIDDEN 403',
]); ?>

<div class="card" style="text-align: center; padding: 60px 24px; max-width: 680px; margin: 0 auto 30px;">
    <div style="width: 80px; height: 80px; margin: 0 auto 20px; background: var(--danger-light); border: 2px solid var(--danger-border); border-radius: 50%; display: grid; place-items: center; color: var(--danger); font-size: 36px;">
        <i class="bi bi-shield-x"></i>
    </div>

    <h2 style="margin: 0 0 12px; font-size: 22px; font-weight: 800; color: var(--text);">
        Bạn không có quyền truy cập chức năng này
    </h2>

    <p style="margin: 0 auto 24px; color: var(--muted); line-height: 1.7; max-width: 500px; font-size: 14px;">
        Hệ thống phân quyền WMS phát hiện vai trò hiện tại của bạn là 
        <strong style="color: var(--primary);"><?= htmlspecialchars($user['vaiTro'] ?? 'Khách') ?></strong> 
        (<?= htmlspecialchars($user['chucDanh'] ?? '') ?>) 
        không nằm trong danh sách các vai trò được phân quyền cho chức năng được yêu cầu.
    </p>

    <div class="rule-box" style="text-align: left; max-width: 500px; margin: 0 auto 24px;">
        <i class="bi bi-shield-lock-fill"></i>
        <div>
            <strong>Quy tắc bảo mật và phân quyền (NFR-04):</strong><br>
            Mọi truy cập trái phép đều được ghi nhận vào nhật ký an ninh hệ thống. Nếu bạn cho rằng đây là sự nhầm lẫn, vui lòng liên hệ Ban Giám Đốc hoặc Quản trị viên để được cấp quyền bổ sung.
        </div>
    </div>

    <div style="display: flex; align-items: center; justify-content: center; gap: 12px;">
        <a href="?route=dashboard/index" class="btn btn-primary">
            <i class="bi bi-arrow-left"></i>
            Quay lại Trang chủ
        </a>
        <a href="javascript:history.back()" class="btn btn-secondary">
            Quay lại trang trước
        </a>
    </div>
</div>

