<?php
/**
 * View: Chi tiết lệnh điều phối nhập kho (Mẫu 02: Modal chi tiết chỉ đọc kèm KPI)
 * @var array $order Thông tin lệnh điều phối và danh sách vị trí ô/kệ
 * @var bool $canConfirm Quyền xác nhận nhận lệnh
 */
use App\Core\Csrf;

$maLDP = htmlspecialchars($order['maLenhDieuPhoi'] ?? '');
$items = $order['items'] ?? [];
$tongSL = (float)($order['tongSoLuongDieuPhoi'] ?? 0);
$soViTri = (int)($order['soViTri'] ?? 0);

$badgeClass = 'badge-draft';
$badgeText = $order['trangThai'] ?? 'CHO_THUC_HIEN';
switch ($order['trangThai']) {
    case 'CHO_THUC_HIEN':
        $badgeClass = 'badge-pending';
        $badgeText = 'Chờ thực hiện (Chưa nhận)';
        break;
    case 'DANG_THUC_HIEN':
        $badgeClass = 'badge-ongoing';
        $badgeText = 'Đang thực hiện (Đã nhận)';
        break;
    case 'HOAN_THANH':
        $badgeClass = 'badge-completed';
        $badgeText = 'Đã hoàn thành';
        break;
    case 'HUY':
        $badgeClass = 'badge-failed';
        $badgeText = 'Đã hủy';
        break;
}
?>

<div class="page-container">
    <div class="page-header-box">
        <div>
            <div class="breadcrumb-nav">
                <a href="?route=lenh-dieu-phoi/index">Kho vận</a>
                <span class="sep">›</span>
                <a href="?route=lenh-dieu-phoi/index">Lệnh điều phối</a>
                <span class="sep">›</span>
                <span class="current"><?= $maLDP ?></span>
            </div>
            <h1 class="page-title">Lệnh điều phối kho: <?= $maLDP ?></h1>
            <p class="page-subtitle">Chi tiết nhiệm vụ vận chuyển, bốc xếp và đặt hàng vào từng ô/kệ chỉ định</p>
        </div>
        <div class="header-actions">
            <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($badgeText) ?></span>
            <a href="?route=lenh-dieu-phoi/index" class="btn btn-secondary btn-sm ml-2">
                <i class="bi bi-arrow-left"></i> Quay lại nhiệm vụ
            </a>
        </div>
    </div>

    <!-- 3 THẺ KPI THEO MẪU 02 -->
    <div class="form-grid grid-3 mb-4">
        <div class="kpi-card featured">
            <div class="kpi-label"><i class="bi bi-box-seam text-primary"></i> TỔNG SỐ LƯỢNG HÀNG XẾP DỠ</div>
            <div class="kpi-value text-primary"><?= formatSo($tongSL) ?></div>
            <div class="kpi-sub text-muted">Cần xếp đúng vị trí ô/kệ</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label"><i class="bi bi-grid-3x3 text-info"></i> SỐ VỊ TRÍ Ô / KỆ CHỈ ĐỊNH</div>
            <div class="kpi-value text-info"><?= $soViTri ?> vị trí</div>
            <div class="kpi-sub text-muted">Tại kho <?= htmlspecialchars($order['tenKho']) ?></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label"><i class="bi bi-person-badge text-success"></i> NHÂN VIÊN THỰC HIỆN</div>
            <div class="kpi-value text-success" style="font-size: 1.3rem;">
                <?= htmlspecialchars($order['tenNhanVienThucHien'] ?? $order['maNhanVienThucHien']) ?>
            </div>
            <div class="kpi-sub text-muted">Trạng thái: <?= htmlspecialchars($badgeText) ?></div>
        </div>
    </div>

    <!-- THÔNG TIN CHUNG DẠNG LƯỚI 2 CỘT -->
    <div class="card mb-4">
        <div class="card-head">
            <div class="card-head-title"><i class="bi bi-info-circle"></i> Thông tin chung lệnh điều phối</div>
        </div>
        <div class="card-body">
            <div class="form-grid grid-2">
                <div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Mã lệnh điều phối:</span>
                        <strong class="text-primary"><?= $maLDP ?></strong>
                    </div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Phiếu nhập nguồn:</span>
                        <a href="?route=dieu-phoi/detail&id=<?= urlencode($order['maPhieuNhap']) ?>" class="font-weight-bold">
                            <?= htmlspecialchars($order['maPhieuNhap']) ?> <i class="bi bi-box-arrow-up-right small"></i>
                        </a>
                    </div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Kho thực hiện:</span>
                        <span><?= htmlspecialchars($order['tenKho']) ?> (<?= htmlspecialchars($order['maKho']) ?>)</span>
                    </div>
                </div>
                <div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Người phát lệnh:</span>
                        <span><?= htmlspecialchars($order['tenNhanVienPhatLenh'] ?? $order['maNhanVienPhatLenh']) ?></span>
                    </div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Thời gian phát lệnh:</span>
                        <span><?= date('d/m/Y H:i', strtotime($order['ngayPhatLenh'])) ?></span>
                    </div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Ghi chú:</span>
                        <span class="text-muted"><?= htmlspecialchars($order['ghiChu'] ?: 'Không có') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BẢNG CHI TIẾT VỊ TRÍ Ô / KỆ CẦN XẾP HÀNG -->
    <div class="card mb-4">
        <div class="card-head">
            <div class="card-head-title"><i class="bi bi-boxes"></i> Danh sách vị trí ô / kệ chỉ định lưu trữ</div>
            <span class="badge badge-draft"><?= count($items) ?> vị trí</span>
        </div>
        <div class="card-body p-0">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 50px;" class="text-center">STT</th>
                            <th style="width: 140px;">Mã vị trí</th>
                            <th>Kệ / Tầng / Ô</th>
                            <th style="width: 120px;">Mã SKU</th>
                            <th>Tên hàng hóa</th>
                            <th>Mã Lô</th>
                            <th class="text-right" style="width: 140px;">Số lượng cần xếp</th>
                            <th class="text-center" style="width: 130px;">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $idx => $it): ?>
                            <tr>
                                <td class="text-center"><?= $idx + 1 ?></td>
                                <td><strong><?= htmlspecialchars($it['maViTri']) ?></strong></td>
                                <td>Kệ <?= htmlspecialchars($it['maKe'] ?? '-') ?> · Tầng <?= htmlspecialchars($it['tang'] ?? '-') ?> · Ô <?= htmlspecialchars($it['oChua'] ?? '-') ?></td>
                                <td><code><?= htmlspecialchars($it['maSKU']) ?></code></td>
                                <td class="font-weight-bold col-text-long"><?= htmlspecialchars($it['tenHang']) ?></td>
                                <td><strong><?= htmlspecialchars($it['maLo']) ?></strong></td>
                                <td class="text-right font-weight-bold text-primary"><?= formatSo($it['soLuongDieuPhoi']) ?></td>
                                <td class="text-center">
                                    <?php if ($it['trangThai'] === 'CHO_NHAP'): ?>
                                        <span class="badge badge-pending">Chờ nhập</span>
                                    <?php else: ?>
                                        <span class="badge badge-completed"><?= htmlspecialchars($it['trangThai']) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- STICKY ACTION BAR -->
    <div class="sticky-action-bar">
        <div class="action-bar-left">
            <a href="?route=lenh-dieu-phoi/index" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại danh sách (Esc)
            </a>
        </div>
        <div class="action-bar-right">
            <button type="button" class="btn btn-light" onclick="alert('Tính năng In lệnh điều phối: Đang phát triển')">
                <i class="bi bi-printer"></i> In lệnh điều phối
            </button>
            <?php if ($order['trangThai'] === 'CHO_THUC_HIEN' && $canConfirm): ?>
                <form method="POST" action="?route=lenh-dieu-phoi/confirm" style="display: inline-block;">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="maLenhDieuPhoi" value="<?= $maLDP ?>">
                    <button type="submit" class="btn btn-success" onclick="return confirm('Xác nhận đã nhận nhiệm vụ và bắt đầu thực hiện bốc dỡ vào ô kệ?')">
                        <i class="bi bi-check2-circle"></i> Đã nhận lệnh (Bắt đầu thực hiện)
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        window.location.href = '?route=lenh-dieu-phoi/index';
    }
});
</script>

