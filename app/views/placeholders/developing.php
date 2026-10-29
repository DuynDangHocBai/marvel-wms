<?php
use App\Core\Auth;
use App\Core\View;

/**
 * View dùng chung cho các màn hình giữ chỗ đang phát triển.
 * @var string $moduleKey Mã module
 * @var string $moduleName Tên module
 * @var string $description Mô tả chức năng
 * @var string $groupName Tên nhóm chức năng
 * @var array $moduleDef Thông tin cấu hình module từ permissions.php
 */

$user = Auth::user();
$canWrite = Auth::canWrite($moduleKey);
$viewRoles = $moduleDef['view'] ?? [];
$writeRoles = $moduleDef['write'] ?? [];
?>

<?php View::partial('page_header', [
    'title' => $moduleName,
    'subtitle' => $description,
    'breadcrumb' => [
        'Trang chủ' => '?route=dashboard/index',
        $groupName => null,
        $moduleName => null,
    ],
    'tagPill' => 'MODULE: ' . strtoupper($moduleKey),
]); ?>

<!-- Banner thông báo chế độ quyền -->
<?php if (!$canWrite): ?>
    <div class="alert alert-info">
        <i class="bi bi-eye"></i>
        <div>
            <strong>Chế độ giới hạn: Bạn chỉ có quyền xem</strong><br>
            Vai trò <strong><?= htmlspecialchars($user['vaiTro']) ?></strong> chỉ được cấp quyền tra cứu/đọc thông tin trong phân hệ này. Các thao tác lập mới, sửa đổi hoặc hủy bỏ chứng từ chỉ dành riêng cho: <strong><?= implode(', ', $writeRoles) ?></strong>.
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-success">
        <i class="bi bi-pencil-square"></i>
        <div>
            <strong>Quyền hạn đầy đủ: Bạn có quyền xem và ghi dữ liệu</strong><br>
            Vai trò <strong><?= htmlspecialchars($user['vaiTro']) ?></strong> được phép khởi tạo, cập nhật và xử lý nghiệp vụ trong phân hệ này.
        </div>
    </div>
<?php endif; ?>

<!-- Thẻ thông báo Đang phát triển -->
<div class="card">
    <div class="card-head">
        <div class="card-head-left">
            <div class="card-icon">
                <i class="bi bi-tools"></i>
            </div>
            <div class="card-title-text">
                <strong>Tính năng đang phát triển</strong>
                <small>Khung giao diện và logic phân quyền đã được thiết lập thành công</small>
            </div>
        </div>
        <div class="card-head-right">
            <span class="badge badge-ongoing">ĐANG PHÁT TRIỂN</span>
        </div>
    </div>

    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 24px;">
            <div style="padding: 16px; background: var(--surface-alt); border-radius: var(--radius-md);">
                <div style="font-size: 11px; font-weight: 700; color: var(--muted); margin-bottom: 4px;">THÔNG TIN PHÂN HỆ</div>
                <div style="font-size: 14px; font-weight: 700; color: var(--text); margin-bottom: 2px;"><?= htmlspecialchars($moduleName) ?></div>
                <div style="font-size: 12px; color: var(--muted);"><?= htmlspecialchars($description) ?></div>
            </div>

            <div style="padding: 16px; background: var(--surface-alt); border-radius: var(--radius-md);">
                <div style="font-size: 11px; font-weight: 700; color: var(--muted); margin-bottom: 4px;">MA TRẬN PHÂN QUYỀN (PERMISSIONS.PHP)</div>
                <div style="font-size: 12px; color: var(--text); margin-bottom: 4px;">
                    • Quyền xem (index, detail): <strong style="color: var(--primary);"><?= implode(', ', $viewRoles) ?: 'Không' ?></strong>
                </div>
                <div style="font-size: 12px; color: var(--text);">
                    • Quyền ghi (create, store, update...): <strong style="color: var(--success);"><?= implode(', ', $writeRoles) ?: 'Chỉ đọc (Không có)' ?></strong>
                </div>
            </div>
        </div>

        <div class="rule-box">
            <i class="bi bi-shield-check"></i>
            <div>
                <strong>Bảo mật phân quyền (NFR-04):</strong> Phân hệ này đã được liên kết với <code>Auth::requirePermission('<?= htmlspecialchars($moduleKey) ?>', '...')</code>. Mọi yêu cầu truy cập từ vai trò không nằm trong ma trận đều tự động trả mã lỗi <code>403 Forbidden</code>.
            </div>
        </div>
    </div>

    <div class="card-footer">
        <div style="color: var(--muted); font-size: 12px;">
            Trạng thái phân hệ: <strong>Sẵn sàng tích hợp Service & Repository</strong>
        </div>
        <div>
            <a href="?route=dashboard/index" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i>
                Về Trang chủ
            </a>
        </div>
    </div>
</div>

