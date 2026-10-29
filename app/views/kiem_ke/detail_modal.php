<?php
/**
 * View: Chi tiết biên bản kiểm kê (Mẫu 02: Modal xem chi tiết chỉ đọc kèm KPI)
 * @var array $record Thông tin biên bản
 * @var array $calculated Dữ liệu tính toán chi tiết và KPI
 */
$maBB = htmlspecialchars($record['maBienBan'] ?? '');
$details = $record['chiTiet'] ?? [];
$tongThua = (float)($record['tongGiaTriThua'] ?? ($calculated['tongThua'] ?? 0.0));
$tongThieu = (float)($record['tongGiaTriThieu'] ?? ($calculated['tongThieu'] ?? 0.0));
$tongChenhLech = (float)($record['tongGiaTriChenhLech'] ?? ($calculated['tongChenhLech'] ?? 0.0));
$giaTriRong = (float)($record['giaTriChenhLechRong'] ?? ($calculated['giaTriRong'] ?? 0.0));

$badgeClass = 'badge-draft';
$badgeText = $record['trangThai'];
switch ($record['trangThai']) {
    case 'DANG_KIEM_KE':
        $badgeClass = 'badge-ongoing';
        $badgeText = 'Đang kiểm kê';
        break;
    case 'CHO_QLK_DUYET':
        $badgeClass = 'badge-pending';
        $badgeText = 'Chờ QLK duyệt (≤ 10tr)';
        break;
    case 'CHO_BGD_DUYET':
        $badgeClass = 'badge-pending';
        $badgeText = 'Chờ BGD duyệt (> 10tr)';
        break;
    case 'DA_DUYET':
        $badgeClass = 'badge-passed';
        $badgeText = 'Đã phê duyệt';
        break;
    case 'TU_CHOI':
        $badgeClass = 'badge-failed';
        $badgeText = 'Từ chối';
        break;
}
?>

<div class="page-container">
    <div class="page-header-box">
        <div>
            <div class="breadcrumb-nav">
                <a href="?route=kiem-ke/index">Kho vận</a>
                <span class="sep">›</span>
                <a href="?route=kiem-ke/index">Biên bản kiểm kê</a>
                <span class="sep">›</span>
                <span class="current"><?= $maBB ?></span>
            </div>
            <h1 class="page-title">Biên bản kiểm kê: <?= $maBB ?></h1>
            <p class="page-subtitle">Xem chi tiết số liệu kiểm kê thực tế và tổng hợp giá trị chênh lệch (Chỉ đọc)</p>
        </div>
        <div class="header-actions">
            <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($badgeText) ?></span>
            <a href="?route=kiem-ke/index" class="btn btn-secondary btn-sm ml-2">
                <i class="bi bi-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- THẺ KPI: 3 CHỈ SỐ CHÊNH LỆCH -->
    <div class="form-grid grid-3 mb-4">
        <div class="kpi-card">
            <div class="kpi-label"><i class="bi bi-arrow-up-circle text-success"></i> TỔNG GIÁ TRỊ THỪA</div>
            <div class="kpi-value text-success">+<?= formatSo($tongThua, true) ?></div>
            <div class="kpi-sub text-muted">Số lượng thực tế vượt sổ sách</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label"><i class="bi bi-arrow-down-circle text-danger"></i> TỔNG GIÁ TRỊ THIẾU</div>
            <div class="kpi-value text-danger">-<?= formatSo($tongThieu, true) ?></div>
            <div class="kpi-sub text-muted">Số lượng thực tế hụt so với sổ sách</div>
        </div>
        <div class="kpi-card featured">
            <div class="kpi-label"><i class="bi bi-calculator text-primary"></i> TỔNG ĐỘ LỚN CHÊNH LỆCH</div>
            <div class="kpi-value text-primary"><?= formatSo($tongChenhLech, true) ?></div>
            <div class="kpi-sub text-muted">Căn cứ xác định cấp thẩm quyền duyệt (ngưỡng 10tr)</div>
        </div>
    </div>

    <!-- THÔNG TIN CHUNG DẠNG LƯỚI 2 CỘT -->
    <div class="card mb-4">
        <div class="card-head">
            <div class="card-head-title"><i class="bi bi-card-text"></i> Thông tin chung biên bản</div>
        </div>
        <div class="card-body">
            <div class="form-grid grid-2">
                <div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Mã biên bản:</span>
                        <strong class="text-primary"><?= $maBB ?></strong>
                    </div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Kho kiểm kê:</span>
                        <strong><?= htmlspecialchars($record['maKho']) ?> - <?= htmlspecialchars($record['tenKho'] ?? '') ?></strong>
                    </div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Ngày kiểm kê:</span>
                        <span><?= htmlspecialchars(date('d/m/Y', strtotime($record['ngayKiemKe']))) ?></span>
                    </div>
                </div>
                <div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Người lập:</span>
                        <span><?= htmlspecialchars($record['tenNhanVienLap'] ?? $record['maNhanVienLap']) ?></span>
                    </div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Cập nhật lần cuối:</span>
                        <span><?= !empty($record['ngayCapNhat']) ? htmlspecialchars(date('d/m/Y H:i', strtotime($record['ngayCapNhat']))) : 'Chưa cập nhật' ?></span>
                    </div>
                    <div class="detail-row mb-2">
                        <span class="text-muted" style="width: 140px; display: inline-block;">Người cập nhật:</span>
                        <span><?= htmlspecialchars($record['tenNhanVienCapNhat'] ?? $record['maNhanVienCapNhat'] ?? '--') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BẢNG CHI TIẾT MẶT HÀNG -->
    <div class="card mb-4">
        <div class="card-head space-between">
            <div class="card-head-title">
                <i class="bi bi-list-check"></i> Chi tiết các dòng mặt hàng (<?= count($details) ?> SKU)
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 50px;" class="text-center">STT</th>
                            <th style="width: 120px;">Mã SKU</th>
                            <th>Tên mặt hàng</th>
                            <th>Lô / Hạn sử dụng</th>
                            <th class="text-right">Sổ sách</th>
                            <th class="text-right">Thực tế</th>
                            <th class="text-right">Đơn giá</th>
                            <th class="text-right">Chênh lệch</th>
                            <th class="text-center">Phân loại</th>
                            <th class="text-right">Giá trị chênh lệch</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($details as $i => $row): ?>
                            <tr>
                                <td class="text-center text-muted"><?= $i + 1 ?></td>
                                <td><strong><?= htmlspecialchars($row['maSKU']) ?></strong></td>
                                <td class="col-text-long"><?= htmlspecialchars($row['tenHang'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['soLo'] ?? $row['maLo'] ?? 'N/A') ?></td>
                                <td class="text-right"><?= formatSo((float)($row['soLuongSoSach'] ?? 0)) ?></td>
                                <td class="text-right"><?= $row['soLuongThucTe'] !== null ? formatSo((float)$row['soLuongThucTe']) : '<span class="text-danger">Chưa có</span>' ?></td>
                                <td class="text-right"><?= $row['donGia'] !== null ? formatSo((float)$row['donGia'], true) : '<span class="text-danger">Chưa có</span>' ?></td>
                                <td class="text-right fw-bold">
                                    <?php $d = (float)($row['soLuongChenhLech'] ?? 0); ?>
                                    <?php if ($d > 0): ?>
                                        <span class="text-success">+<?= formatSo($d) ?></span>
                                    <?php elseif ($d < 0): ?>
                                        <span class="text-danger"><?= formatSo($d) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">0</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php $pl = $row['phanLoai'] ?? 'KHOP'; ?>
                                    <?php if ($pl === 'THUA'): ?>
                                        <span class="badge badge-passed">Thừa</span>
                                    <?php elseif ($pl === 'THIEU'): ?>
                                        <span class="badge badge-failed">Thiếu</span>
                                    <?php else: ?>
                                        <span class="badge badge-draft">Khớp</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right fw-bold">
                                    <?php $gv = (float)($row['giaTriChenhLech'] ?? 0); ?>
                                    <?php if ($gv > 0): ?>
                                        <span class="text-success">+<?= formatSo($gv, true) ?></span>
                                    <?php elseif ($gv < 0): ?>
                                        <span class="text-danger"><?= formatSo($gv, true) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted"><?= formatSo(0, true) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- HỘP QUY TẮC NGHIỆP VỤ -->
    <div class="rule-box mb-4">
        <div class="rule-box-title">
            <i class="bi bi-shield-check"></i> Quy tắc nghiệp vụ (BR-01 & BR-08):
        </div>
        <ul class="rule-box-list">
            <li><strong>Ngưỡng thẩm quyền:</strong> Tổng độ lớn chênh lệch ≤ 10.000.000 đ do Quản lý kho (QLK) phê duyệt; > 10.000.000 đ do Ban Giám Đốc (BGD) phê duyệt.</li>
            <li><strong>Không điều chỉnh tồn kho:</strong> Chênh lệch kiểm kê chỉ được ghi nhận kết quả tại use case này; số lượng tồn kho chỉ được điều chỉnh sau khi cấp có thẩm quyền phê duyệt chính thức.</li>
        </ul>
    </div>

    <!-- NÚT THAO TÁC CUỐI -->
    <div class="text-right">
        <button type="button" class="btn btn-secondary" onclick="alert('Tính năng In biên bản đang phát triển')">
            <i class="bi bi-printer"></i> In biên bản
        </button>
        <a href="?route=kiem-ke/index" class="btn btn-primary ml-2">
            <i class="bi bi-check2"></i> Đóng
        </a>
    </div>
</div>

