<?php
/**
 * View: Chi tiết lệnh sản xuất bánh kẹo (Mẫu 02: Modal chi tiết chỉ đọc kèm KPI & thanh tiến độ)
 * @var array $order Thông tin lệnh sản xuất và danh sách NVL
 */
$maLSX = htmlspecialchars($order['maLenhSX'] ?? '');
$items = $order['items'] ?? [];
$nvlList = $order['nvlList'] ?? [];
$tongCanCap = (float)($order['tongNVLCanCap'] ?? 0);
$tongDaPB = (float)($order['tongNVLDaPhanBo'] ?? 0);
$tongConLai = (float)($order['tongNVLConLai'] ?? 0);
$phanTram = (float)($order['phanTramTienDo'] ?? 0);

$badgeClass = 'badge-draft';
$badgeText = $order['trangThai'] ?? 'CHO_PHAT';
switch ($order['trangThai']) {
    case 'CHO_PHAT':
        $badgeClass = 'badge-pending';
        $badgeText = \App\Config\Messages::LSX_LABEL_DRAFT;
        break;
    case 'DA_PHAT':
        $badgeClass = 'badge-ongoing';
        $badgeText = 'Đã phát lệnh (Chờ phân bổ)';
        break;
    case 'DANG_PHAN_BO':
        $badgeClass = 'badge-warning';
        $badgeText = 'Đang phân bổ';
        break;
    case 'DA_PHAN_BO_DU':
        $badgeClass = 'badge-passed';
        $badgeText = 'Đã phân bổ đủ';
        break;
    case 'DANG_SAN_XUAT':
        $badgeClass = 'badge-ongoing';
        $badgeText = 'Đang sản xuất';
        break;
    case 'HOAN_THANH':
        $badgeClass = 'badge-completed';
        $badgeText = 'Hoàn thành';
        break;
    case 'DA_HUY':
        $badgeClass = 'badge-failed';
        $badgeText = 'Đã hủy';
        break;
}
?>

<div class="page-container">
    <div class="page-header-box">
        <div>
            <div class="breadcrumb-nav">
                <a href="?route=lenh-san-xuat/index">Kho vận</a>
                <span class="sep">›</span>
                <a href="?route=lenh-san-xuat/index">Lệnh sản xuất</a>
                <span class="sep">›</span>
                <span class="current"><?= $maLSX ?></span>
            </div>
            <h1 class="page-title">Lệnh sản xuất: <?= $maLSX ?></h1>
            <p class="page-subtitle">Chi tiết phân bổ xưởng, danh mục thành phẩm sản xuất và tiến độ cấp phát nguyên vật liệu</p>
        </div>
        <div class="header-actions">
            <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($badgeText) ?></span>
            <a href="?route=lenh-san-xuat/index" class="btn btn-secondary btn-sm ml-2">
                <i class="bi bi-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- 3 THẺ KPI THEO MẪU 02 -->
    <div class="form-grid grid-3 mb-4">
        <div class="kpi-card">
            <div class="kpi-label"><i class="bi bi-box text-muted"></i> TỔNG NVL CẦN CẤP</div>
            <div class="kpi-value text-primary"><?= formatSo($tongCanCap) ?></div>
            <div class="kpi-sub text-muted">Tính theo định mức BOM</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label"><i class="bi bi-box-arrow-in-right text-success"></i> TỔNG NVL ĐÃ PHÂN BỔ</div>
            <div class="kpi-value text-success"><?= formatSo($tongDaPB) ?></div>
            <div class="kpi-sub text-muted">Đã tạo phiếu phân bổ từ kho</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label"><i class="bi bi-hourglass-split text-danger"></i> TỔNG NVL CÒN THIẾU</div>
            <div class="kpi-value <?= $tongConLai > 0 ? 'text-danger' : 'text-muted' ?>">
                <?= formatSo($tongConLai) ?>
            </div>
            <div class="kpi-sub text-muted">Cần phân bổ tiếp</div>
        </div>
    </div>

    <!-- TIẾN ĐỘ PHÂN BỔ NVL VÀ CÔNG THỨC -->
    <div class="card mb-4">
        <div class="card-head">
            <div class="card-head-title"><i class="bi bi-speedometer2"></i> Tiến độ phân bổ nguyên vật liệu</div>
            <span class="font-weight-bold text-primary"><?= number_format($phanTram, 1) ?>%</span>
        </div>
        <div class="card-body">
            <div class="progress mb-2" style="height: 14px; background-color: var(--border-color); border-radius: 7px; overflow: hidden;">
                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= min(100, $phanTram) ?>%; height: 100%; transition: width 0.4s ease;" aria-valuenow="<?= $phanTram ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="d-flex justify-content-between text-muted" style="font-size: 0.85rem;">
                <span>Công thức: <code>(Tổng NVL đã phân bổ / Tổng NVL cần cấp) × 100%</code></span>
                <span><?= formatSo($tongDaPB) ?> / <?= formatSo($tongCanCap) ?> đơn vị</span>
            </div>
        </div>
    </div>

    <!-- THÔNG TIN CHUNG DẠNG LƯỚI 2 CỘT -->
    <div class="card mb-4">
        <div class="card-head">
            <div class="card-head-title"><i class="bi bi-info-circle"></i> Thông tin chung lệnh sản xuất</div>
        </div>
        <div class="card-body">
            <div class="form-grid grid-2">
                <div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Mã lệnh:</span>
                        <strong class="text-primary"><?= $maLSX ?></strong>
                    </div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Kế hoạch gốc:</span>
                        <strong><?= htmlspecialchars($order['maKeHoach']) ?></strong>
                    </div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Xưởng tiếp nhận:</span>
                        <span><?= htmlspecialchars($order['tenXuong'] ?? $order['maXuong']) ?> (<?= htmlspecialchars($order['maXuong']) ?>)</span>
                    </div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Người lập lệnh:</span>
                        <span><?= htmlspecialchars($order['tenNhanVienLap'] ?? $order['maNhanVienLap']) ?></span>
                    </div>
                </div>
                <div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Ngày lập:</span>
                        <span><?= htmlspecialchars(date('d/m/Y H:i', strtotime($order['ngayLap']))) ?></span>
                    </div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Thời gian SX:</span>
                        <span><?= date('d/m/Y', strtotime($order['ngayBatDau'])) ?> → <?= date('d/m/Y', strtotime($order['ngayHoanThanh'])) ?></span>
                    </div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Trạng thái:</span>
                        <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($badgeText) ?></span>
                    </div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Ghi chú:</span>
                        <span class="text-muted"><?= htmlspecialchars($order['ghiChu'] ?: 'Không có') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- HỘP QUY TẮC NGHIỆP VỤ BR-06 -->
    <div class="card mb-4" style="border-left: 4px solid var(--primary-color);">
        <div class="card-body">
            <h6 class="font-weight-bold text-primary mb-2"><i class="bi bi-shield-check"></i> Quy tắc nghiệp vụ BR-06 (Giữ chỗ NVL theo BOM)</h6>
            <p class="text-muted mb-0">
                Khi lệnh sản xuất ở trạng thái <code>DA_PHAT</code> hoặc <code>DANG_PHAN_BO</code>, toàn bộ số lượng NVL cần cấp chưa phân bổ sẽ được hệ thống <b>giữ chỗ tự động</b>. Số lượng khả dụng trong view <code>vw_ton_kho_kha_dung</code> đã được khấu trừ để ngăn chặn việc cấp phát vượt quá khả năng thực tế của kho.
            </p>
        </div>
    </div>

    <!-- BẢNG 1: THÀNH PHẨM GIAO SẢN XUẤT -->
    <div class="card mb-4">
        <div class="card-head">
            <div class="card-head-title"><i class="bi bi-box-seam"></i> 1. Danh sách bánh kẹo giao sản xuất</div>
            <span class="badge badge-draft"><?= count($items) ?> mặt hàng</span>
        </div>
        <div class="card-body p-0">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 60px;" class="text-center">STT</th>
                            <th style="width: 140px;">Mã SKU</th>
                            <th>Tên thành phẩm</th>
                            <th>Đơn vị tính</th>
                            <th class="text-right" style="width: 160px;">Số lượng giao</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $idx => $item): ?>
                            <tr>
                                <td class="text-center"><?= $idx + 1 ?></td>
                                <td><code><?= htmlspecialchars($item['maSKU']) ?></code></td>
                                <td class="font-weight-bold col-text-long"><?= htmlspecialchars($item['tenHang']) ?></td>
                                <td><?= htmlspecialchars($item['donViTinh']) ?></td>
                                <td class="text-right font-weight-bold text-primary"><?= formatSo($item['soLuongGiao']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- BẢNG 2: NVL CẦN CẤP VÀ TIẾN ĐỘ PHÂN BỔ -->
    <div class="card mb-4">
        <div class="card-head">
            <div class="card-head-title"><i class="bi bi-diagram-3"></i> 2. Nhu cầu nguyên vật liệu & Tiến độ phân bổ kho</div>
            <span class="badge badge-draft"><?= count($nvlList) ?> nguyên vật liệu</span>
        </div>
        <div class="card-body p-0">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 60px;" class="text-center">STT</th>
                            <th style="width: 140px;">Mã NVL</th>
                            <th>Tên nguyên vật liệu</th>
                            <th>ĐVT</th>
                            <th class="text-right">Cần cấp (BOM)</th>
                            <th class="text-right">Đã phân bổ</th>
                            <th class="text-right">Còn lại</th>
                            <th class="text-center" style="width: 140px;">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($nvlList as $idx => $nvl): ?>
                            <?php 
                                $statusBadge = 'badge-pending';
                                $statusLabel = 'Chờ phân bổ';
                                if ($nvl['trangThai'] === 'DA_PHAN_BO_DU') {
                                    $statusBadge = 'badge-passed';
                                    $statusLabel = 'Đã phân bổ đủ';
                                } elseif ($nvl['trangThai'] === 'DANG_PHAN_BO') {
                                    $statusBadge = 'badge-ongoing';
                                    $statusLabel = 'Đang phân bổ';
                                }
                            ?>
                            <tr>
                                <td class="text-center"><?= $idx + 1 ?></td>
                                <td><code><?= htmlspecialchars($nvl['maSKU']) ?></code></td>
                                <td class="font-weight-bold col-text-long"><?= htmlspecialchars($nvl['tenHang']) ?></td>
                                <td><?= htmlspecialchars($nvl['donViTinh']) ?></td>
                                <td class="text-right font-weight-bold"><?= formatSo($nvl['soLuongCanCap']) ?></td>
                                <td class="text-right text-success"><?= formatSo($nvl['soLuongDaPhanBo']) ?></td>
                                <td class="text-right font-weight-bold <?= $nvl['soLuongConLai'] > 0 ? 'text-danger' : 'text-muted' ?>">
                                    <?= formatSo($nvl['soLuongConLai']) ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $statusBadge ?>"><?= $statusLabel ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- THANH NÚT DƯỚI TRANG -->
    <div class="sticky-action-bar">
        <div class="action-bar-left">
            <a href="?route=lenh-san-xuat/index" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại danh sách (Esc)
            </a>
        </div>
        <div class="action-bar-right">
            <button type="button" class="btn btn-light" onclick="alert('Tính năng In chứng từ: Đang phát triển')">
                <i class="bi bi-printer"></i> In lệnh sản xuất
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        window.location.href = '?route=lenh-san-xuat/index';
    }
});
</script>

