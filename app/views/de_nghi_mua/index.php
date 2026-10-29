<?php
/**
 * View: Danh sách đề nghị mua nguyên vật liệu (Mẫu 01: Tra cứu / Danh sách)
 * @var array $proposals Danh sách đề nghị mua
 * @var array $filters Bộ lọc tìm kiếm
 * @var array $approvedPlans Danh mục kế hoạch đã duyệt
 * @var bool $canCreate Quyền tạo mới
 */
?>

<div class="page-container">
    <div class="page-header-box">
        <div>
            <div class="breadcrumb-nav">
                <a href="?route=dashboard/index">Kế hoạch</a>
                <span class="sep">›</span>
                <span class="current">Đề nghị mua NVL</span>
            </div>
            <h1 class="page-title">Đề nghị mua nguyên vật liệu sản xuất</h1>
            <p class="page-subtitle">Theo dõi và lập đề nghị mua nguyên vật liệu bổ sung dựa trên định mức BOM, tồn kho khả dụng và hàng đang về</p>
        </div>
        <div class="header-actions">
            <?php if ($canCreate): ?>
                <a href="?route=de-nghi-mua/create" class="btn btn-primary">
                    <i class="bi bi-cart-plus"></i> Lập đề nghị mua mới
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- THẺ 1: ĐIỀU KIỆN TRA CỨU -->
    <div class="card mb-4">
        <div class="card-head">
            <div class="card-head-title"><i class="bi bi-funnel"></i> Điều kiện tra cứu</div>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="form-grid grid-4 align-items-end">
                <input type="hidden" name="route" value="de-nghi-mua/index">

                <div class="form-group">
                    <label class="form-label">Từ khóa tìm kiếm</label>
                    <input type="text" name="keyword" class="input" placeholder="Mã đề nghị, lý do, người lập..." value="<?= htmlspecialchars($filters['keyword']) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Kế hoạch sản xuất</label>
                    <select name="maKeHoach" class="input">
                        <option value="">-- Tất cả kế hoạch --</option>
                        <?php foreach ($approvedPlans as $p): ?>
                            <option value="<?= htmlspecialchars($p['maKeHoach']) ?>" <?= $filters['maKeHoach'] === $p['maKeHoach'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['maKeHoach']) ?> (Xưởng: <?= htmlspecialchars($p['tenXuong'] ?? $p['maXuong']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Trạng thái phê duyệt</label>
                    <select name="trangThai" class="input">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="CHO_DUYET" <?= $filters['trangThai'] === 'CHO_DUYET' ? 'selected' : '' ?>>Chờ phê duyệt</option>
                        <option value="DA_DUYET" <?= $filters['trangThai'] === 'DA_DUYET' ? 'selected' : '' ?>>Đã phê duyệt</option>
                        <option value="TU_CHOI" <?= $filters['trangThai'] === 'TU_CHOI' ? 'selected' : '' ?>>Từ chối / Đã hủy</option>
                        <option value="DANG_MUA" <?= $filters['trangThai'] === 'DANG_MUA' ? 'selected' : '' ?>>Đang mua hàng</option>
                        <option value="HOAN_THANH" <?= $filters['trangThai'] === 'HOAN_THANH' ? 'selected' : '' ?>>Hoàn thành</option>
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary mr-2"><i class="bi bi-search"></i> Tìm kiếm</button>
                    <a href="?route=de-nghi-mua/index" class="btn btn-secondary">Đặt lại</a>
                </div>
            </form>
        </div>
    </div>

    <!-- THẺ 2: KẾT QUẢ TRA CỨU -->
    <div class="card mb-4">
        <div class="card-head space-between">
            <div class="card-head-title">
                <i class="bi bi-table"></i> Kết quả tra cứu
                <span class="badge badge-draft ml-2"><?= count($proposals) ?> đề nghị mua</span>
            </div>
            <div>
                <button type="button" class="btn btn-light btn-sm mr-2" onclick="alert('Tính năng Xuất Excel: Đang phát triển')">
                    <i class="bi bi-file-earmark-excel"></i> Xuất Excel
                </button>
                <button type="button" class="btn btn-light btn-sm" onclick="alert('Tính năng In: Đang phát triển')">
                    <i class="bi bi-printer"></i> In
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 160px;">Mã đề nghị</th>
                            <th>Kế hoạch liên quan</th>
                            <th>Ngày lập</th>
                            <th>Ngày cần hàng</th>
                            <th class="text-center">Số NVL</th>
                            <th class="text-right">Tổng số lượng</th>
                            <th class="text-right">Tổng tiền dự toán</th>
                            <th>Người lập</th>
                            <th class="text-center" style="width: 130px;">Trạng thái</th>
                            <th class="text-center col-actions" style="width: 120px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($proposals)): ?>
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                    Chưa có đề nghị mua nguyên vật liệu nào.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($proposals as $p): ?>
                                <tr>
                                    <td><strong class="text-primary"><?= htmlspecialchars($p['maDeNghi']) ?></strong></td>
                                    <td>
                                        <?php if (!empty($p['maKeHoach'])): ?>
                                            <span class="font-weight-bold"><?= htmlspecialchars($p['maKeHoach']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Ngoài kế hoạch</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($p['ngayLap'])) ?></td>
                                    <td>
                                        <?= !empty($p['ngayCanHang']) ? date('d/m/Y', strtotime($p['ngayCanHang'])) : '-' ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-light"><?= (int)$p['soMatHang'] ?> NVL</span>
                                    </td>
                                    <td class="text-right"><?= formatSo($p['tongSoLuongMua']) ?></td>
                                    <td class="text-right font-weight-bold text-primary">
                                        <?= formatSo($p['tongTienDuKien'], true) ?>
                                    </td>
                                    <td><?= htmlspecialchars($p['tenNhanVienLap'] ?? 'Lập kế hoạch') ?></td>
                                    <td class="text-center">
                                        <?php if ($p['trangThai'] === 'CHO_DUYET'): ?>
                                            <span class="badge badge-pending">Chờ phê duyệt</span>
                                        <?php elseif ($p['trangThai'] === 'DA_DUYET'): ?>
                                            <span class="badge badge-passed">Đã duyệt</span>
                                        <?php elseif ($p['trangThai'] === 'TU_CHOI'): ?>
                                            <span class="badge badge-failed">Từ chối</span>
                                        <?php elseif ($p['trangThai'] === 'DA_HUY'): ?>
                                            <span class="badge badge-draft">Đã hủy</span>
                                        <?php elseif ($p['trangThai'] === 'DANG_MUA'): ?>
                                            <span class="badge badge-ongoing">Đang mua</span>
                                        <?php else: ?>
                                            <span class="badge badge-completed"><?= htmlspecialchars($p['trangThai']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center col-actions">
                                        <a href="?route=de-nghi-mua/detail&id=<?= urlencode($p['maDeNghi']) ?>" class="btn btn-secondary btn-sm">
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

