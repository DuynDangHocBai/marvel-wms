<?php
use App\Core\View;
?>

<!-- Tiêu đề trang -->
<?php View::partial('page_header', [
    'title' => 'Bảng điều khiển trung tâm',
    'subtitle' => "Xin chào {$user['hoTen']} ({$user['chucDanh']}) · Bộ phận: {$user['tenBoPhan']}",
    'breadcrumb' => [
        'Kho vận' => null,
        'Bảng điều khiển' => null,
    ],
    'tagPill' => "VAI TRÒ: {$role}",
]); ?>

<!-- 4 THẺ KPI SỐ LIỆU THỰC TẾ THEO VAI TRÒ -->
<div class="kpi-grid">
    <?php foreach ($kpiCards as $card): ?>
        <div class="kpi-card <?= htmlspecialchars($card['tint']) ?>">
            <div class="kpi-card-header">
                <i class="bi <?= htmlspecialchars($card['icon']) ?>"></i>
                <span><?= htmlspecialchars($card['label']) ?></span>
            </div>
            <div class="kpi-value-row">
                <span class="kpi-number tabular-nums">
                    <?= is_numeric($card['value']) ? formatSo($card['value']) : htmlspecialchars($card['value']) ?>
                </span>
                <span class="kpi-unit"><?= htmlspecialchars($card['unit']) ?></span>
            </div>
            <div class="kpi-desc">
                <?= htmlspecialchars($card['desc']) ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- DANH SÁCH LỐI TẮT CHỨC NĂNG ĐƯỢC PHÉP TRUY CẬP -->
<div class="card">
    <div class="card-head">
        <div class="card-head-left">
            <div class="card-icon">
                <i class="bi bi-grid-fill"></i>
            </div>
            <div class="card-title-text">
                <strong>Chức năng được phân quyền</strong>
                <small>Danh sách các phân hệ nghiệp vụ thuộc quyền hạn của vai trò <?= htmlspecialchars($role) ?></small>
            </div>
        </div>
        <div class="card-head-right">
            <span class="badge badge-passed">
                <?= count($shortcuts) ?> Chức năng khả dụng
            </span>
        </div>
    </div>

    <div class="card-body">
        <?php if (empty($shortcuts)): ?>
            <div style="text-align: center; padding: 30px; color: var(--muted);">
                Chưa có chức năng nào được cấp phép cho tài khoản này.
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
                <?php foreach ($shortcuts as $item): ?>
                    <div style="display: flex; flex-direction: column; justify-content: space-between; padding: 18px; background: #FAFBFD; border: 1px solid var(--border); border-radius: var(--radius-md); transition: all 0.15s ease;">
                        <div>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                                <div style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; background: var(--primary-light); border-radius: var(--radius-sm); color: var(--primary); font-size: 18px;">
                                    <i class="bi <?= htmlspecialchars($item['icon']) ?>"></i>
                                </div>
                                <?php if ($item['canWrite']): ?>
                                    <span class="badge badge-passed" style="font-size: 10px;">Toàn quyền (Xem & Ghi)</span>
                                <?php else: ?>
                                    <span class="badge badge-ongoing" style="font-size: 10px;">Chỉ xem</span>
                                <?php endif; ?>
                            </div>
                            <strong style="display: block; font-size: 14px; color: var(--text); margin-bottom: 4px;">
                                <?= htmlspecialchars($item['name']) ?>
                            </strong>
                            <p style="margin: 0 0 16px; font-size: 12px; color: var(--muted); line-height: 1.5;">
                                <?= htmlspecialchars($item['description']) ?>
                            </p>
                        </div>
                        <div>
                            <a href="<?= htmlspecialchars($item['url']) ?>" class="btn btn-secondary btn-sm" style="width: 100%; justify-content: space-between;">
                                <span>Truy cập phân hệ</span>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Banner thông tin phiên làm việc -->
<div class="alert alert-info" style="margin-bottom: 0;">
    <i class="bi bi-clock-history"></i>
    <div style="display: flex; align-items: center; justify-content: space-between; width: 100%; flex-wrap: wrap; gap: 10px;">
        <div>
            <strong>Phiên bảo mật đang hoạt động:</strong> Tự động đăng xuất sau 15 phút không thao tác theo tiêu chuẩn bảo mật (NFR-04).
        </div>
        <div style="font-size: 12px; color: var(--muted);">
            IP hiện tại: <code><?= htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1') ?></code>
        </div>
    </div>
</div>

