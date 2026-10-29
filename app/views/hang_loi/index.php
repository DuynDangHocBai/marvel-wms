<?php
/**
 * View: Danh sách đề xuất xử lý hàng lỗi hỏng (Mẫu 01: Tra cứu tiến độ / danh sách)
 * @var array $items Danh sách đề xuất
 * @var array $filters Dữ liệu bộ lọc
 * @var bool $canCreate Quyền tạo mới đề xuất (Chỉ QLK có quyền, QC và BGD chỉ xem)
 */
?>

<div class="page-container">
    <!-- Header trang & Breadcrumb -->
    <div class="page-header-box">
        <div>
            <div class="breadcrumb-nav">
                <span>Kho vận</span>
                <span class="sep">›</span>
                <span class="current">Đề xuất xử lý hàng lỗi hỏng</span>
            </div>
            <h1 class="page-title">Đề xuất xử lý hàng lỗi hỏng</h1>
            <p class="page-subtitle">Quản lý phương án xử lý bánh kẹo lỗi bao bì, hỏng chất lượng hoặc cận date lưu kho</p>
        </div>
        <div class="header-actions">
            <?php if ($canCreate): ?>
                <a href="?route=hang-loi/create" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg"></i> Lập đề xuất mới
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- THẺ 1: ĐIỀU KIỆN TRA CỨU (Lưới 4 ô lọc) -->
    <div class="card mb-4">
        <div class="card-head">
            <div class="card-head-title">
                <i class="bi bi-funnel"></i> Điều kiện tra cứu
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="filter-grid-form">
                <input type="hidden" name="route" value="hang-loi/index">
                <div class="form-grid grid-4">
                    <div class="form-group">
                        <label class="form-label">Mã đề xuất / Lý do</label>
                        <input type="text" name="keyword" class="input" placeholder="Nhập mã DXLH, lý do..." value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phương án xử lý</label>
                        <select name="phuongAn" class="input">
                            <option value="">-- Tất cả phương án --</option>
                            <option value="TIEU_HUY" <?= ($filters['phuongAn'] ?? '') === 'TIEU_HUY' ? 'selected' : '' ?>>Tiêu hủy</option>
                            <option value="TRA_NCC" <?= ($filters['phuongAn'] ?? '') === 'TRA_NCC' ? 'selected' : '' ?>>Trả lại nhà cung cấp</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Trạng thái đề xuất</label>
                        <select name="trangThai" class="input">
                            <option value="">-- Tất cả trạng thái --</option>
                            <option value="CHO_XU_LY" <?= ($filters['trangThai'] ?? '') === 'CHO_XU_LY' ? 'selected' : '' ?>>Chờ xử lý</option>
                            <option value="CHO_BGD_DUYET" <?= ($filters['trangThai'] ?? '') === 'CHO_BGD_DUYET' ? 'selected' : '' ?>>Chờ BGD duyệt</option>
                            <option value="DA_DUYET" <?= ($filters['trangThai'] ?? '') === 'DA_DUYET' ? 'selected' : '' ?>>Đã duyệt</option>
                            <option value="TU_CHOI" <?= ($filters['trangThai'] ?? '') === 'TU_CHOI' ? 'selected' : '' ?>>Từ chối</option>
                            <option value="DA_XU_LY" <?= ($filters['trangThai'] ?? '') === 'DA_XU_LY' ? 'selected' : '' ?>>Đã xử lý</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Khoảng ngày lập</label>
                        <div class="input-group-range">
                            <input type="date" name="tuNgay" class="input" value="<?= htmlspecialchars($filters['tuNgay'] ?? '') ?>">
                            <span class="range-sep">đến</span>
                            <input type="date" name="denNgay" class="input" value="<?= htmlspecialchars($filters['denNgay'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                <div class="filter-actions mt-3 text-right">
                    <a href="?route=hang-loi/index" class="btn btn-ghost btn-sm">
                        <i class="bi bi-arrow-counterclockwise"></i> Đặt lại bộ lọc
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
                <i class="bi bi-table"></i> Danh sách đề xuất xử lý
                <span class="badge badge-ongoing ml-2"><?= count($items) ?> đề xuất</span>
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
                            <th style="width: 170px;">Mã đề xuất</th>
                            <th style="width: 140px;">Ngày lập</th>
                            <th>Người lập</th>
                            <th style="width: 150px;">Phương án</th>
                            <th class="col-text-long">Lý do xử lý</th>
                            <th class="text-center" style="width: 100px;">Số SKU</th>
                            <th class="text-right" style="width: 140px;">Tổng giá trị</th>
                            <th class="text-center" style="width: 130px;">Trạng thái</th>
                            <th class="text-center col-actions" style="width: 120px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-muted"></i>
                                    Không tìm thấy đề xuất xử lý hàng lỗi hỏng nào.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <a href="?route=hang-loi/detail&id=<?= urlencode($item['maDeXuat']) ?>" class="fw-bold text-primary">
                                            <?= htmlspecialchars($item['maDeXuat']) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($item['ngayLap']))) ?></td>
                                    <td><?= htmlspecialchars($item['tenNhanVienLap'] ?? $item['maNhanVienLap']) ?></td>
                                    <td>
                                        <?php if ($item['phuongAn'] === 'TIEU_HUY'): ?>
                                            <span class="badge badge-failed">Tiêu hủy</span>
                                        <?php else: ?>
                                            <span class="badge badge-ongoing">Trả lại NCC</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="col-text-long">
                                        <div title="<?= htmlspecialchars($item['lyDo']) ?>">
                                            <?= htmlspecialchars($item['lyDo']) ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-draft"><?= (int)$item['soMatHang'] ?> SKU</span>
                                    </td>
                                    <td class="text-right fw-bold">
                                        <?= formatSo((float)$item['tongGiaTri'], true) ?>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $badgeClass = 'badge-draft';
                                        $badgeText = $item['trangThai'];
                                        switch ($item['trangThai']) {
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
                                        <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($badgeText) ?></span>
                                    </td>
                                    <td class="text-center col-actions">
                                        <a href="?route=hang-loi/detail&id=<?= urlencode($item['maDeXuat']) ?>" class="btn btn-ghost btn-sm" title="Xem chi tiết">
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

