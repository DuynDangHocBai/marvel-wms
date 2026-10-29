<?php
/**
 * View: Danh sách biên bản kiểm kê (Mẫu 01: Tra cứu tiến độ / danh sách)
 * @var array $items Danh sách biên bản
 * @var array $filters Dữ liệu bộ lọc
 * @var array $khoList Danh sách kho
 * @var bool $canWrite Quyền cập nhật (QLK)
 */
use App\Core\Auth;
?>

<div class="page-container">
    <!-- Header trang & Breadcrumb -->
    <div class="page-header-box">
        <div>
            <div class="breadcrumb-nav">
                <span>Kho vận</span>
                <span class="sep">›</span>
                <span class="current">Cập nhật chênh lệch kiểm kê</span>
            </div>
            <h1 class="page-title">Cập nhật chênh lệch kiểm kê</h1>
            <p class="page-subtitle">Theo dõi, tính toán và ghi nhận chênh lệch số lượng kiểm kê thực tế so với sổ sách</p>
        </div>
        <div class="header-actions">
            <button type="button" class="btn btn-secondary btn-sm" onclick="alert('Tính năng Quét mã đang phát triển')">
                <i class="bi bi-qr-code-scan"></i> Quét mã
            </button>
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
                <input type="hidden" name="route" value="kiem-ke/index">
                <div class="form-grid grid-4">
                    <div class="form-group">
                        <label class="form-label">Mã biên bản / Từ khóa</label>
                        <input type="text" name="keyword" class="input" placeholder="Nhập mã BB, tên kho..." value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kho kiểm kê</label>
                        <select name="maKho" class="input">
                            <option value="">-- Tất cả kho --</option>
                            <?php foreach ($khoList as $k): ?>
                                <option value="<?= htmlspecialchars($k['maKho']) ?>" <?= ($filters['maKho'] ?? '') === $k['maKho'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['maKho'] . ' - ' . $k['tenKho']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Trạng thái biên bản</label>
                        <select name="trangThai" class="input">
                            <option value="">-- Tất cả trạng thái --</option>
                            <option value="DANG_KIEM_KE" <?= ($filters['trangThai'] ?? '') === 'DANG_KIEM_KE' ? 'selected' : '' ?>>Đang kiểm kê (Cần tính)</option>
                            <option value="CHO_QLK_DUYET" <?= ($filters['trangThai'] ?? '') === 'CHO_QLK_DUYET' ? 'selected' : '' ?>>Chờ QLK duyệt (≤ 10tr)</option>
                            <option value="CHO_BGD_DUYET" <?= ($filters['trangThai'] ?? '') === 'CHO_BGD_DUYET' ? 'selected' : '' ?>>Chờ BGD duyệt (> 10tr)</option>
                            <option value="DA_DUYET" <?= ($filters['trangThai'] ?? '') === 'DA_DUYET' ? 'selected' : '' ?>>Đã phê duyệt</option>
                            <option value="TU_CHOI" <?= ($filters['trangThai'] ?? '') === 'TU_CHOI' ? 'selected' : '' ?>>Từ chối</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Khoảng ngày kiểm kê</label>
                        <div class="input-group-range">
                            <input type="date" name="tuNgay" class="input" value="<?= htmlspecialchars($filters['tuNgay'] ?? '') ?>">
                            <span class="range-sep">đến</span>
                            <input type="date" name="denNgay" class="input" value="<?= htmlspecialchars($filters['denNgay'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                <div class="filter-actions mt-3 text-right">
                    <a href="?route=kiem-ke/index" class="btn btn-ghost btn-sm">
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
                <i class="bi bi-table"></i> Kết quả tra cứu
                <span class="badge badge-ongoing ml-2"><?= count($items) ?> biên bản</span>
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
                            <th style="width: 140px;">Mã biên bản</th>
                            <th style="width: 110px;">Ngày kiểm kê</th>
                            <th class="col-text-long">Kho lưu trữ</th>
                            <th>Người lập</th>
                            <th class="text-center" style="width: 100px;">Số mặt hàng</th>
                            <th class="text-right" style="width: 150px;">Tổng chênh lệch</th>
                            <th class="text-center" style="width: 140px;">Trạng thái</th>
                            <th class="text-center col-actions" style="width: 180px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-muted"></i>
                                    Không tìm thấy biên bản kiểm kê nào phù hợp với điều kiện tra cứu.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <a href="?route=kiem-ke/detail&id=<?= urlencode($item['maBienBan']) ?>" class="fw-bold text-primary">
                                            <?= htmlspecialchars($item['maBienBan']) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($item['ngayKiemKe']))) ?></td>
                                    <td class="col-text-long">
                                        <strong><?= htmlspecialchars($item['maKho']) ?></strong> - <?= htmlspecialchars($item['tenKho'] ?? '') ?>
                                    </td>
                                    <td><?= htmlspecialchars($item['tenNhanVienLap'] ?? $item['maNhanVienLap']) ?></td>
                                    <td class="text-center">
                                        <span class="badge badge-draft"><?= (int)$item['soMatHang'] ?> SKU</span>
                                    </td>
                                    <td class="text-right fw-bold">
                                        <?= formatSo((float)$item['tongGiaTriChenhLech'], true) ?>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $badgeClass = 'badge-draft';
                                        $badgeText = $item['trangThai'];
                                        switch ($item['trangThai']) {
                                            case 'DANG_KIEM_KE':
                                                $badgeClass = 'badge-ongoing';
                                                $badgeText = 'Đang kiểm kê';
                                                break;
                                            case 'CHO_QLK_DUYET':
                                                $badgeClass = 'badge-pending';
                                                $badgeText = 'Chờ QLK duyệt';
                                                break;
                                            case 'CHO_BGD_DUYET':
                                                $badgeClass = 'badge-pending';
                                                $badgeText = 'Chờ BGD duyệt';
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
                                        <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($badgeText) ?></span>
                                    </td>
                                    <td class="text-center col-actions">
                                        <div class="btn-group">
                                            <a href="?route=kiem-ke/detail&id=<?= urlencode($item['maBienBan']) ?>" class="btn btn-ghost btn-sm" title="Xem chi tiết biên bản">
                                                <i class="bi bi-eye"></i> Chi tiết
                                            </a>
                                            <?php if ($canWrite && $item['trangThai'] === 'DANG_KIEM_KE'): ?>
                                                <a href="?route=kiem-ke/update&id=<?= urlencode($item['maBienBan']) ?>" class="btn btn-primary btn-sm ml-1" title="Cập nhật chênh lệch kiểm kê">
                                                    <i class="bi bi-calculator"></i> Cập nhật
                                                </a>
                                            <?php endif; ?>
                                        </div>
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

