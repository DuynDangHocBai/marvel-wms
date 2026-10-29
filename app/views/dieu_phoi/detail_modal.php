<?php
/**
 * View: Chi tiết phiếu nhập kho (Mẫu 02: Modal chi tiết chỉ đọc kèm KPI)
 * @var array $inboundReq Thông tin phiếu nhập và danh sách hàng hóa
 */
$maPN = htmlspecialchars($inboundReq['maPhieuNhap'] ?? '');
$items = $inboundReq['items'] ?? [];
$tongSL = (float)($inboundReq['tongSoLuong'] ?? 0);
$tongVol = (float)($inboundReq['tongTheTich'] ?? 0);

$badgeClass = 'badge-draft';
$badgeText = $inboundReq['trangThai'] ?? 'CHO_DIEU_PHOI';
switch ($inboundReq['trangThai']) {
    case 'CHO_DIEU_PHOI':
        $badgeClass = 'badge-pending';
        $badgeText = 'Chờ điều phối';
        break;
    case 'DA_DIEU_PHOI':
        $badgeClass = 'badge-ongoing';
        $badgeText = 'Đã điều phối';
        break;
    case 'DA_NHAP':
        $badgeClass = 'badge-completed';
        $badgeText = 'Đã nhập kho';
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
                <a href="?route=dieu-phoi/index">Kho vận</a>
                <span class="sep">›</span>
                <a href="?route=dieu-phoi/index">Điều phối nhập kho</a>
                <span class="sep">›</span>
                <span class="current"><?= $maPN ?></span>
            </div>
            <h1 class="page-title">Phiếu nhập kho: <?= $maPN ?></h1>
            <p class="page-subtitle">Chi tiết lô hàng, chứng từ nguồn và kết quả kiểm định chất lượng QA/QC</p>
        </div>
        <div class="header-actions">
            <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($badgeText) ?></span>
            <a href="?route=dieu-phoi/index" class="btn btn-secondary btn-sm ml-2">
                <i class="bi bi-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- 3 THẺ KPI THEO MẪU 02 -->
    <div class="form-grid grid-3 mb-4">
        <div class="kpi-card featured">
            <div class="kpi-label"><i class="bi bi-box-seam text-primary"></i> TỔNG SỐ LƯỢNG HÀNG HÓA</div>
            <div class="kpi-value text-primary"><?= formatSo($tongSL) ?></div>
            <div class="kpi-sub text-muted">Đơn vị lưu kho</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label"><i class="bi bi-aspect-ratio text-info"></i> TỔNG THỂ TÍCH CHIẾM CHỖ</div>
            <div class="kpi-value text-info"><?= formatSo($tongVol) ?> m³</div>
            <div class="kpi-sub text-muted">Dung tích ước tính trong kho</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label"><i class="bi bi-tags text-success"></i> SỐ MẶT HÀNG / LÔ</div>
            <div class="kpi-value text-success"><?= count($items) ?> dòng</div>
            <div class="kpi-sub text-muted">Kho đích: <?= htmlspecialchars($inboundReq['tenKho']) ?></div>
        </div>
    </div>

    <!-- THÔNG TIN CHUNG DẠNG LƯỚI 2 CỘT -->
    <div class="card mb-4">
        <div class="card-head">
            <div class="card-head-title"><i class="bi bi-info-circle"></i> Thông tin chung phiếu nhập kho</div>
        </div>
        <div class="card-body">
            <div class="form-grid grid-2">
                <div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Mã phiếu nhập:</span>
                        <strong class="text-primary"><?= $maPN ?></strong>
                    </div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Đơn mua hàng:</span>
                        <span><?= htmlspecialchars($inboundReq['maDonMua'] ?: 'Nhập nội bộ từ xưởng') ?></span>
                    </div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Kho tiếp nhận:</span>
                        <span><?= htmlspecialchars($inboundReq['tenKho']) ?> (<?= htmlspecialchars($inboundReq['maKho']) ?>)</span>
                    </div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Nhà cung cấp / Nguồn:</span>
                        <span><?= htmlspecialchars($inboundReq['tenNCC'] ?: 'Xưởng sản xuất nội bộ') ?></span>
                    </div>
                </div>
                <div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Ngày lập phiếu:</span>
                        <span><?= date('d/m/Y H:i', strtotime($inboundReq['ngayLap'])) ?></span>
                    </div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Người lập phiếu:</span>
                        <span><?= htmlspecialchars($inboundReq['tenNhanVienLap'] ?? $inboundReq['maNhanVienLap']) ?></span>
                    </div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Trạng thái:</span>
                        <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($badgeText) ?></span>
                    </div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Ghi chú:</span>
                        <span class="text-muted"><?= htmlspecialchars($inboundReq['ghiChu'] ?: 'Không có') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BẢNG CHI TIẾT HÀNG HÓA -->
    <div class="card mb-4">
        <div class="card-head">
            <div class="card-head-title"><i class="bi bi-boxes"></i> Danh mục mặt hàng và kết quả kiểm định QA/QC</div>
            <span class="badge badge-draft"><?= count($items) ?> mặt hàng</span>
        </div>
        <div class="card-body p-0">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 50px;" class="text-center">STT</th>
                            <th style="width: 130px;">Mã SKU</th>
                            <th>Tên hàng hóa</th>
                            <th>Mã Lô</th>
                            <th>ĐVT</th>
                            <th class="text-right">Số lượng</th>
                            <th>Hạn sử dụng</th>
                            <th class="text-center" style="width: 140px;">Kiểm định QA/QC</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $idx => $it): ?>
                            <tr>
                                <td class="text-center"><?= $idx + 1 ?></td>
                                <td><code><?= htmlspecialchars($it['maSKU']) ?></code></td>
                                <td class="font-weight-bold col-text-long"><?= htmlspecialchars($it['tenHang']) ?></td>
                                <td><strong><?= htmlspecialchars($it['maLo']) ?></strong></td>
                                <td><?= htmlspecialchars($it['donViTinh']) ?></td>
                                <td class="text-right font-weight-bold text-primary"><?= formatSo($it['soLuong']) ?></td>
                                <td><?= htmlspecialchars($it['ngayHetHan'] ?? 'Không giới hạn') ?></td>
                                <td class="text-center">
                                    <?php if ($it['trangThaiChatLuong'] === 'DAT'): ?>
                                        <span class="badge badge-passed"><i class="bi bi-check"></i> ĐẠT QC</span>
                                    <?php else: ?>
                                        <span class="badge badge-failed"><i class="bi bi-x"></i> <?= htmlspecialchars($it['trangThaiChatLuong']) ?></span>
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
            <a href="?route=dieu-phoi/index" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại danh sách (Esc)
            </a>
        </div>
        <div class="action-bar-right">
            <button type="button" class="btn btn-light" onclick="alert('Tính năng In phiếu nhập: Đang phát triển')">
                <i class="bi bi-printer"></i> In phiếu nhập
            </button>
            <?php if ($inboundReq['trangThai'] === 'CHO_DIEU_PHOI'): ?>
                <a href="?route=dieu-phoi/create&req_id=<?= urlencode($maPN) ?>" class="btn btn-primary">
                    <i class="bi bi-arrow-left-right"></i> Điều phối vị trí ngay
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        window.location.href = '?route=dieu-phoi/index';
    }
});
</script>

