<?php
/**
 * View: Danh sách lệnh sản xuất (Mẫu 01: Tra cứu có cột thanh % tiến độ)
 * @var array $orders Danh sách lệnh sản xuất
 * @var array $filters Bộ lọc
 * @var array $xuongList Danh sách xưởng
 * @var bool $canCreate Quyền tạo lệnh (LKH)
 */
?>

<div class="page-container">
    <div class="page-header-box">
        <div>
            <div class="breadcrumb-nav">
                <span>Kế hoạch</span>
                <span class="sep">›</span>
                <span class="current">Lệnh sản xuất</span>
            </div>
            <h1 class="page-title">Theo dõi tiến độ lệnh sản xuất</h1>
            <p class="page-subtitle">Quản lý việc phát hành lệnh sản xuất bánh kẹo theo định mức BOM và giám sát tiến độ cấp phát NVL</p>
        </div>
        <div class="header-actions">
            <?php if ($canCreate): ?>
                <a href="?route=lenh-san-xuat/create" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg"></i> Tạo lệnh sản xuất mới
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- THẺ 1: ĐIỀU KIỆN TRA CỨU -->
    <div class="card mb-4">
        <div class="card-head">
            <div class="card-head-title">
                <i class="bi bi-funnel"></i> Điều kiện tra cứu
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="filter-grid-form">
                <input type="hidden" name="route" value="lenh-san-xuat/index">
                <div class="form-grid grid-3">
                    <div class="form-group">
                        <label class="form-label">Mã lệnh / Mã kế hoạch</label>
                        <input type="text" name="keyword" class="input" placeholder="Nhập LSX, KHSX, tên xưởng..." value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Xưởng sản xuất</label>
                        <select name="maXuong" class="input">
                            <option value="">-- Tất cả xưởng --</option>
                            <?php foreach ($xuongList as $x): ?>
                                <option value="<?= htmlspecialchars($x['maXuong']) ?>" <?= ($filters['maXuong'] ?? '') === $x['maXuong'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($x['maXuong'] . ' - ' . $x['tenXuong']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Trạng thái lệnh</label>
                        <select name="trangThai" class="input">
                            <option value="">-- Tất cả trạng thái --</option>
                            <option value="CHO_PHAT" <?= ($filters['trangThai'] ?? '') === 'CHO_PHAT' ? 'selected' : '' ?>>Bản nháp / Chờ phát</option>
                            <option value="DA_PHAT" <?= ($filters['trangThai'] ?? '') === 'DA_PHAT' ? 'selected' : '' ?>>Đã phát lệnh (Đang giữ chỗ)</option>
                            <option value="DANG_PHAN_BO" <?= ($filters['trangThai'] ?? '') === 'DANG_PHAN_BO' ? 'selected' : '' ?>>Đang phân bổ NVL</option>
                            <option value="DANG_SAN_XUAT" <?= ($filters['trangThai'] ?? '') === 'DANG_SAN_XUAT' ? 'selected' : '' ?>>Đang sản xuất</option>
                            <option value="HOAN_THANH" <?= ($filters['trangThai'] ?? '') === 'HOAN_THANH' ? 'selected' : '' ?>>Hoàn thành</option>
                            <option value="HUY" <?= ($filters['trangThai'] ?? '') === 'HUY' ? 'selected' : '' ?>>Đã hủy</option>
                        </select>
                    </div>
                </div>
                <div class="filter-actions mt-3 text-right">
                    <a href="?route=lenh-san-xuat/index" class="btn btn-ghost btn-sm">
                        <i class="bi bi-arrow-counterclockwise"></i> Đặt lại
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm ml-2">
                        <i class="bi bi-search"></i> Tìm kiếm
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- THẺ 2: KẾT QUẢ TRA CỨU -->
    <div class="card">
        <div class="card-head space-between">
            <div class="card-head-title">
                <i class="bi bi-table"></i> Danh sách lệnh sản xuất
                <span class="badge badge-ongoing ml-2"><?= count($orders) ?> lệnh</span>
            </div>
            <div class="card-head-tools">
                <button type="button" class="btn btn-ghost btn-sm" onclick="alert('Tính năng Xuất Excel đang phát triển')">
                    <i class="bi bi-file-earmark-excel"></i> Xuất Excel
                </button>
                <button type="button" class="btn btn-ghost btn-sm" onclick="alert('Tính năng Xuất PDF đang phát triển')">
                    <i class="bi bi-file-earmark-pdf"></i> Xuất PDF
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 140px;">Mã lệnh SX</th>
                            <th style="width: 120px;">Kế hoạch</th>
                            <th class="col-text-long">Xưởng tiếp nhận</th>
                            <th>Thời gian SX</th>
                            <th class="text-right" style="width: 110px;">Số lượng giao</th>
                            <th class="text-center" style="width: 140px;">Trạng thái</th>
                            <th style="width: 190px;">Tiến độ cấp NVL</th>
                            <th class="text-center col-actions" style="width: 120px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-muted"></i>
                                    Không tìm thấy lệnh sản xuất nào.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $ord): ?>
                                <?php
                                $pct = (float)$ord['phanTramTienDo'];
                                $badgeClass = 'badge-draft';
                                $badgeText = $ord['trangThai'];
                                switch ($ord['trangThai']) {
                                    case 'CHO_PHAT':
                                        $badgeClass = 'badge-draft';
                                        $badgeText = 'Bản nháp';
                                        break;
                                    case 'DA_PHAT':
                                        $badgeClass = 'badge-pending';
                                        $badgeText = 'Đã phát lệnh';
                                        break;
                                    case 'DANG_PHAN_BO':
                                        $badgeClass = 'badge-ongoing';
                                        $badgeText = 'Đang phân bổ';
                                        break;
                                    case 'DANG_SAN_XUAT':
                                        $badgeClass = 'badge-ongoing';
                                        $badgeText = 'Đang sản xuất';
                                        break;
                                    case 'HOAN_THANH':
                                        $badgeClass = 'badge-passed';
                                        $badgeText = 'Hoàn thành';
                                        break;
                                    case 'HUY':
                                        $badgeClass = 'badge-failed';
                                        $badgeText = 'Đã hủy';
                                        break;
                                }
                                ?>
                                <tr>
                                    <td>
                                        <a href="?route=lenh-san-xuat/detail&id=<?= urlencode($ord['maLenhSX']) ?>" class="fw-bold text-primary">
                                            <?= htmlspecialchars($ord['maLenhSX']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge badge-draft"><?= htmlspecialchars($ord['maKeHoach']) ?></span>
                                    </td>
                                    <td class="col-text-long">
                                        <strong><?= htmlspecialchars($ord['maXuong']) ?></strong> - <?= htmlspecialchars($ord['tenXuong'] ?? '') ?>
                                    </td>
                                    <td>
                                        <div><?= htmlspecialchars(date('d/m/Y', strtotime($ord['ngayBatDau']))) ?></div>
                                        <small class="text-muted">đến <?= htmlspecialchars(date('d/m/Y', strtotime($ord['ngayHoanThanh']))) ?></small>
                                    </td>
                                    <td class="text-right fw-bold">
                                        <?= formatSo($ord['tongSoLuongGiao']) ?> hộp
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($badgeText) ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="fw-bold"><?= $pct ?>%</small>
                                            <small class="text-muted"><?= formatSo($ord['tongNVLDaPhanBo']) ?> / <?= formatSo($ord['tongNVLCanCap']) ?> kg</small>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar" style="width: <?= min(100, $pct) ?>%; background: <?= $pct >= 100 ? 'var(--success)' : 'var(--primary)' ?>;"></div>
                                        </div>
                                    </td>
                                    <td class="text-center col-actions">
                                        <a href="?route=lenh-san-xuat/detail&id=<?= urlencode($ord['maLenhSX']) ?>" class="btn btn-ghost btn-sm" title="Xem chi tiết lệnh sản xuất">
                                            <i class="bi bi-eye"></i> Chi tiết
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

