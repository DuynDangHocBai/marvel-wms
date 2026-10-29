<?php
/**
 * View: Chi tiết phiếu phân bổ số lượng tồn kho (Mẫu 02: Modal chi tiết chỉ đọc kèm KPI)
 * @var array $allocation Thông tin phiếu phân bổ và danh sách lô/vị trí
 */
$maPB = htmlspecialchars($allocation['maPhieuPhanBo'] ?? '');
$items = $allocation['items'] ?? [];
$tongSL = (float)($allocation['tongSoLuong'] ?? 0);
$soLo = (int)($allocation['soLoHang'] ?? 0);
?>

<div class="page-container">
    <div class="page-header-box">
        <div>
            <div class="breadcrumb-nav">
                <a href="?route=phan-bo/index">Kho vận</a>
                <span class="sep">›</span>
                <a href="?route=phan-bo/index&tab=history">Phân bổ số lượng</a>
                <span class="sep">›</span>
                <span class="current"><?= $maPB ?></span>
            </div>
            <h1 class="page-title">Phiếu phân bổ số lượng: <?= $maPB ?></h1>
            <p class="page-subtitle">Chi tiết lô hàng, vị trí lưu trữ và số lượng nguyên vật liệu đã được giữ chỗ cho lệnh sản xuất</p>
        </div>
        <div class="header-actions">
            <span class="badge badge-completed">Hoàn thành</span>
            <a href="?route=phan-bo/index&tab=history" class="btn btn-secondary btn-sm ml-2">
                <i class="bi bi-arrow-left"></i> Quay lại lịch sử
            </a>
        </div>
    </div>

    <!-- 3 THẺ KPI THEO MẪU 02 -->
    <div class="form-grid grid-3 mb-4">
        <div class="kpi-card featured">
            <div class="kpi-label"><i class="bi bi-box-seam text-primary"></i> TỔNG SỐ LƯỢNG PHÂN BỔ</div>
            <div class="kpi-value text-primary"><?= formatSo($tongSL) ?></div>
            <div class="kpi-sub text-muted">Đã chuyển sang trạng thái giữ chỗ theo lô</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label"><i class="bi bi-boxes"></i> SỐ LÔ HÀNG SỬ DỤNG</div>
            <div class="kpi-value"><?= $soLo ?> lô</div>
            <div class="kpi-sub text-muted">Xuất từ kho <?= htmlspecialchars($allocation['tenKhoNguon'] ?? $allocation['maKhoNguon']) ?></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label"><i class="bi bi-diagram-3 text-success"></i> SỐ DÒNG PHÂN BỔ VỊ TRÍ</div>
            <div class="kpi-value text-success"><?= count($items) ?> vị trí</div>
            <div class="kpi-sub text-muted">Tuân thủ nguyên tắc FEFO</div>
        </div>
    </div>

    <!-- THÔNG TIN CHUNG DẠNG LƯỚI 2 CỘT -->
    <div class="card mb-4">
        <div class="card-head">
            <div class="card-head-title"><i class="bi bi-info-circle"></i> Thông tin chung phiếu phân bổ</div>
        </div>
        <div class="card-body">
            <div class="form-grid grid-2">
                <div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Mã phiếu:</span>
                        <strong class="text-primary"><?= $maPB ?></strong>
                    </div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Lệnh sản xuất:</span>
                        <a href="?route=lenh-san-xuat/detail&id=<?= urlencode($allocation['maLenhSX']) ?>" class="font-weight-bold text-decoration-none">
                            <?= htmlspecialchars($allocation['maLenhSX']) ?> <i class="bi bi-box-arrow-up-right small"></i>
                        </a>
                    </div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Kho nguồn:</span>
                        <span><?= htmlspecialchars($allocation['tenKhoNguon']) ?> (<?= htmlspecialchars($allocation['maKhoNguon']) ?>)</span>
                    </div>
                </div>
                <div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Người thực hiện:</span>
                        <span><?= htmlspecialchars($allocation['tenNhanVien'] ?? $allocation['maNhanVien']) ?></span>
                    </div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Thời gian tạo:</span>
                        <span><?= date('d/m/Y H:i', strtotime($allocation['ngayPhanBo'])) ?></span>
                    </div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Ghi chú:</span>
                        <span class="text-muted"><?= htmlspecialchars($allocation['ghiChu'] ?: 'Không có') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- HỘP QUY TẮC BR-06 -->
    <div class="card mb-4" style="border-left: 4px solid var(--primary-color);">
        <div class="card-body">
            <h6 class="font-weight-bold text-primary mb-2"><i class="bi bi-shield-check"></i> Quy tắc nghiệp vụ BR-06 (Giữ chỗ NVL theo Lô)</h6>
            <p class="text-muted mb-0">
                Toàn bộ số lượng trong phiếu này đã được hệ thống cập nhật vào cột <code>soLuongGiuCho</code> của bảng <code>ton_kho</code> cho từng lô/vị trí cụ thể. Các giao dịch kho loại <code>PHAN_BO_GIU_CHO</code> đã được tự động lưu lại trong sổ nhật ký thủ kho.
            </p>
        </div>
    </div>

    <!-- BẢNG CHI TIẾT LÔ & VỊ TRÍ PHÂN BỔ -->
    <div class="card mb-4">
        <div class="card-head">
            <div class="card-head-title"><i class="bi bi-table"></i> Danh sách lô & vị trí nguyên vật liệu phân bổ</div>
            <span class="badge badge-draft"><?= count($items) ?> dòng chi tiết</span>
        </div>
        <div class="card-body p-0">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 50px;" class="text-center">STT</th>
                            <th style="width: 140px;">Mã Lô</th>
                            <th>Vị trí lưu kho</th>
                            <th style="width: 120px;">Mã SKU</th>
                            <th>Tên nguyên vật liệu</th>
                            <th>ĐVT</th>
                            <th>Hạn sử dụng</th>
                            <th class="text-right" style="width: 160px;">Số lượng phân bổ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $idx => $it): ?>
                            <tr>
                                <td class="text-center"><?= $idx + 1 ?></td>
                                <td><strong><?= htmlspecialchars($it['maLo']) ?></strong></td>
                                <td><?= htmlspecialchars($it['tenViTri']) ?></td>
                                <td><code><?= htmlspecialchars($it['maSKU']) ?></code></td>
                                <td class="font-weight-bold col-text-long"><?= htmlspecialchars($it['tenHang']) ?></td>
                                <td><?= htmlspecialchars($it['donViTinh']) ?></td>
                                <td><?= htmlspecialchars($it['ngayHetHan'] ?? 'Không giới hạn') ?></td>
                                <td class="text-right font-weight-bold text-success"><?= formatSo($it['soLuongPhanBo']) ?></td>
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
            <a href="?route=phan-bo/index&tab=history" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại danh sách (Esc)
            </a>
        </div>
        <div class="action-bar-right">
            <button type="button" class="btn btn-light" onclick="alert('Tính năng In phiếu phân bổ: Đang phát triển')">
                <i class="bi bi-printer"></i> In phiếu phân bổ
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        window.location.href = '?route=phan-bo/index&tab=history';
    }
});
</script>

