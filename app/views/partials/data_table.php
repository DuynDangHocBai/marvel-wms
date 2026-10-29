<?php
/**
 * Partial: data_table
 * @var string $title Tiêu đề khối bảng
 * @var string|null $badgePill Pill nhỏ (vd: 'Tìm thấy 4 kế hoạch sản xuất')
 * @var array $columns Danh sách cột ['key' => 'Tên cột', ...]
 * @var array $rows Danh sách dòng
 * @var string|null $emptyMessage Thông báo nếu không có dữ liệu
 */
?>
<div class="card">
    <div class="card-head">
        <div class="card-head-left">
            <div class="card-icon">
                <i class="bi bi-table"></i>
            </div>
            <div class="card-title-text">
                <strong><?= htmlspecialchars($title ?? 'Kết quả tra cứu') ?></strong>
                <?php if (!empty($badgePill)): ?>
                    <span class="badge badge-ongoing" style="margin-left: 8px;">
                        <?= htmlspecialchars($badgePill) ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-head-right">
            <button type="button" class="btn btn-secondary btn-sm" onclick="window.print()">
                <i class="bi bi-download"></i>
                Xuất báo cáo
            </button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="location.reload()" title="Tải lại">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <?php foreach ($columns as $key => $colName): ?>
                        <th><?= htmlspecialchars($colName) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="<?= count($columns) ?>" style="text-align: center; padding: 40px 20px; color: var(--muted);">
                            <i class="bi bi-inbox" style="font-size: 32px; display: block; margin-bottom: 8px; color: var(--muted-light);"></i>
                            <?= htmlspecialchars($emptyMessage ?? 'Chưa có bản ghi dữ liệu nào.') ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <?php foreach ($columns as $key => $colName): ?>
                                <td><?= $row[$key] ?? '' ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination-bar">
        <div class="pagination-info">
            Hiển thị <strong><?= count($rows) ?></strong> kết quả
        </div>
        <div class="pagination-controls">
            <span class="page-btn"><i class="bi bi-chevron-left"></i></span>
            <span class="page-btn active">1</span>
            <span class="page-btn"><i class="bi bi-chevron-right"></i></span>
        </div>
    </div>
</div>

