<?php
/**
 * View: Chi tiết đề xuất xử lý hàng lỗi hỏng (Mẫu 02: Modal chi tiết chỉ đọc kèm KPI)
 * @var array $proposal Thông tin đề xuất và chi tiết mặt hàng
 */
$maDX = htmlspecialchars($proposal['maDeXuat'] ?? '');
$details = $proposal['chiTiet'] ?? [];
$tongGiaTri = (float)($proposal['tongGiaTri'] ?? 0.0);

$badgeClass = 'badge-draft';
$badgeText = $proposal['trangThai'] ?? 'CHO_XU_LY';
switch ($proposal['trangThai']) {
    case 'CHO_XU_LY':
        $badgeClass = 'badge-pending';
        $badgeText = 'Chờ xử lý';
        break;
    case 'CHO_BGD_DUYET':
        $badgeClass = 'badge-pending';
        $badgeText = 'Chờ BGD duyệt';
        break;
    case 'DA_DUYET':
        $badgeClass = 'badge-passed';
        $badgeText = 'Đã duyệt';
        break;
    case 'TU_CHOI':
        $badgeClass = 'badge-failed';
        $badgeText = 'Từ chối';
        break;
    case 'DA_XU_LY':
        $badgeClass = 'badge-completed';
        $badgeText = 'Đã xử lý';
        break;
}
?>

<div class="page-container">
    <div class="page-header-box">
        <div>
            <div class="breadcrumb-nav">
                <a href="?route=hang-loi/index">Kho vận</a>
                <span class="sep">›</span>
                <a href="?route=hang-loi/index">Đề xuất xử lý</a>
                <span class="sep">›</span>
                <span class="current"><?= $maDX ?></span>
            </div>
            <h1 class="page-title">Đề xuất xử lý: <?= $maDX ?></h1>
            <p class="page-subtitle">Xem chi tiết thông tin phương án và danh sách bánh kẹo đề xuất xử lý (Chỉ đọc)</p>
        </div>
        <div class="header-actions">
            <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($badgeText) ?></span>
            <a href="?route=hang-loi/index" class="btn btn-secondary btn-sm ml-2">
                <i class="bi bi-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- THẺ KPI: TỔNG GIÁ TRỊ VÀ SỐ MẶT HÀNG -->
    <div class="form-grid grid-2 mb-4">
        <div class="kpi-card featured">
            <div class="kpi-label"><i class="bi bi-cash-stack text-primary"></i> TỔNG GIÁ TRỊ BÁNH KẸO LỖI HỎNG</div>
            <div class="kpi-value text-primary"><?= formatSo($tongGiaTri, true) ?></div>
            <div class="kpi-sub text-muted">Thiệt hại dự kiến / Giá trị hàng xử lý</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label"><i class="bi bi-box-seam"></i> SỐ MẶT HÀNG TRONG ĐỀ XUẤT</div>
            <div class="kpi-value"><?= count($details) ?> SKU</div>
            <div class="kpi-sub text-muted">Phương án: <?= $proposal['phuongAn'] === 'TIEU_HUY' ? 'Tiêu hủy' : 'Trả lại nhà cung cấp' ?></div>
        </div>
    </div>

    <!-- THÔNG TIN CHUNG DẠNG LƯỚI 2 CỘT -->
    <div class="card mb-4">
        <div class="card-head">
            <div class="card-head-title"><i class="bi bi-info-circle"></i> Thông tin chung đề xuất</div>
        </div>
        <div class="card-body">
            <div class="form-grid grid-2">
                <div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Mã đề xuất:</span>
                        <strong class="text-primary"><?= $maDX ?></strong>
                    </div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Ngày lập:</span>
                        <span><?= htmlspecialchars(date('d/m/Y H:i', strtotime($proposal['ngayLap']))) ?></span>
                    </div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Người lập:</span>
                        <span><?= htmlspecialchars($proposal['tenNhanVienLap'] ?? $proposal['maNhanVienLap']) ?></span>
                    </div>
                </div>
                <div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Phương án xử lý:</span>
                        <strong><?= $proposal['phuongAn'] === 'TIEU_HUY' ? 'Tiêu hủy' : 'Trả lại nhà cung cấp' ?></strong>
                    </div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Trạng thái:</span>
                        <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($badgeText) ?></span>
                    </div>
                </div>
            </div>
            <div class="mt-3 pt-3 border-top">
                <span class="text-muted d-block mb-1">Lý do đề xuất xử lý:</span>
                <div class="p-3 bg-light rounded border">
                    <?= nl2br(htmlspecialchars($proposal['lyDo'])) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- BẢNG CHI TIẾT BÁNH KẸO XỬ LÝ (SNAPSHOT LÔ & HSD) -->
    <div class="card mb-4">
        <div class="card-head">
            <div class="card-head-title">
                <i class="bi bi-table"></i> Chi tiết bánh kẹo xử lý (Đã lưu snapshot số lô, HSD và đơn giá)
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 50px;" class="text-center">STT</th>
                            <th style="width: 120px;">Mã SKU</th>
                            <th>Tên bánh kẹo</th>
                            <th>Lô sản xuất / HSD</th>
                            <th>Chứng từ nguồn</th>
                            <th class="text-right" style="width: 130px;">Số lượng xử lý</th>
                            <th class="text-right" style="width: 130px;">Đơn giá</th>
                            <th class="text-right" style="width: 150px;">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($details as $idx => $line): ?>
                            <tr>
                                <td class="text-center text-muted"><?= $idx + 1 ?></td>
                                <td><strong><?= htmlspecialchars($line['maSKU']) ?></strong></td>
                                <td class="col-text-long"><?= htmlspecialchars($line['tenHang'] ?? '') ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($line['soLo']) ?></strong>
                                    <div class="small text-muted">HSD: <?= htmlspecialchars(date('d/m/Y', strtotime($line['ngayHetHan']))) ?></div>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars($line['maChungTuNguon'] ?? 'N/A') ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($line['nguyenNhan'] ?? '') ?></small>
                                </td>
                                <td class="text-right fw-bold">
                                    <?= formatSo((float)$line['soLuongXuLy']) ?> <?= htmlspecialchars($line['donViTinh'] ?? '') ?>
                                </td>
                                <td class="text-right"><?= formatSo((float)$line['donGia'], true) ?></td>
                                <td class="text-right fw-bold text-primary"><?= formatSo((float)$line['thanhTien'], true) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-active">
                            <td colspan="7" class="text-right fw-bold">TỔNG CỘNG GIÁ TRỊ:</td>
                            <td class="text-right fw-bold text-primary fs-5"><?= formatSo($tongGiaTri, true) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- HỘP QUY TẮC NGHIỆP VỤ -->
    <div class="rule-box mb-4">
        <div class="rule-box-title">
            <i class="bi bi-shield-check"></i> Quy định kiểm soát (BR-01 & BR-09):
        </div>
        <ul class="rule-box-list">
            <li><strong>Thẩm quyền phê duyệt:</strong> Ban Giám Đốc (BGD) là cấp duy nhất có thẩm quyền phê duyệt phương án tiêu hủy hoặc thanh lý hàng hóa (BR-01).</li>
            <li><strong>Tính nguyên vẹn:</strong> Thông tin số lô, hạn sử dụng và đơn giá được snapshot cố định tại thời điểm lập đề xuất để bảo đảm tính pháp lý khi lưu trữ và kiểm toán.</li>
        </ul>
    </div>

    <!-- NÚT THAO TÁC CUỐI -->
    <div class="text-right">
        <button type="button" class="btn btn-secondary" onclick="alert('Tính năng In đề xuất đang phát triển')">
            <i class="bi bi-printer"></i> In đề xuất
        </button>
        <a href="?route=hang-loi/index" class="btn btn-primary ml-2">
            <i class="bi bi-check2"></i> Đóng
        </a>
    </div>
</div>

