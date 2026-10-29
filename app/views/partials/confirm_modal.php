<?php
/**
 * Partial: confirm_modal
 * @var string $modalId ID của modal
 * @var string $title Tiêu đề modal
 * @var string $message Nội dung cảnh báo/xác nhận
 * @var string $confirmUrl Đường dẫn khi xác nhận
 * @var string $confirmText Nhãn nút xác nhận (mặc định: 'Xác nhận')
 */
?>
<div class="modal-backdrop" id="<?= htmlspecialchars($modalId ?? 'confirmModal') ?>">
    <div class="modal-dialog" style="max-width: 480px;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--warning-light); display: grid; place-items: center; color: var(--warning);">
                    <i class="bi bi-exclamation-triangle-fill" style="font-size: 18px;"></i>
                </div>
                <strong style="font-size: 16px; color: var(--text);">
                    <?= htmlspecialchars($title ?? 'Xác nhận thao tác') ?>
                </strong>
            </div>
            <button type="button" class="modal-close" data-modal-close>
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="modal-body" style="font-size: 14px; color: var(--muted); line-height: 1.6;">
            <?= htmlspecialchars($message ?? 'Bạn có chắc chắn muốn thực hiện hành động này? Thao tác này có thể ảnh hưởng đến dữ liệu hệ thống.') ?>
        </div>

        <div class="modal-footer" style="justify-content: flex-end; gap: 10px;">
            <button type="button" class="btn btn-secondary" data-modal-close>
                Hủy bỏ
            </button>
            <a href="<?= htmlspecialchars($confirmUrl ?? '#') ?>" class="btn btn-primary">
                <?= htmlspecialchars($confirmText ?? 'Xác nhận') ?>
            </a>
        </div>
    </div>
</div>

