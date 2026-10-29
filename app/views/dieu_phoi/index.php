<?php
/**
 * View: Danh sách điều phối nhập kho (Mẫu 01: Tra cứu / Danh sách 2 Tab)
 * @var string $activeTab Tab đang xem ('pending' hoặc 'all')
 * @var string $keyword Từ khóa lọc
 * @var string $maKho Mã kho lọc
 * @var string $trangThai Trạng thái lọc
 * @var array $pendingList Danh sách phiếu nhập chờ điều phối
 * @var array $allList Danh sách tất cả phiếu nhập
 * @var array $warehouses Danh mục kho
 * @var bool $canCreate Quyền tạo lệnh điều phối
 */
?>

<div class="page-container">
    <div class="page-header-box">
        <div>
            <div class="breadcrumb-nav">
                <a href="?route=dashboard/index">Kho vận</a>
                <span class="sep">›</span>
                <span class="current">Điều phối nhập kho</span>
            </div>
            <h1 class="page-title">Điều phối nhập kho lưu trữ hàng hóa</h1>
            <p class="page-subtitle">Phân bổ kho, khu vực và vị trí lưu trữ (ô/kệ) cho các lô bánh kẹo và nguyên vật liệu đã kiểm tra đạt chuẩn</p>
        </div>
        <div class="header-actions">
            <?php if ($canCreate): ?>
                <a href="?route=dieu-phoi/create" class="btn btn-primary">
                    <i class="bi bi-arrow-left-right"></i> Lập lệnh điều phối mới
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- TABS ĐIỀU HƯỚNG -->
    <div class="card mb-4">
        <div class="card-body p-2" style="border-bottom: 1px solid var(--border-color);">
            <div class="d-flex align-items-center">
                <a href="?route=dieu-phoi/index&tab=pending" class="btn btn-sm <?= $activeTab === 'pending' ? 'btn-primary' : 'btn-light' ?> mr-2">
                    <i class="bi bi-hourglass-split"></i> Chờ điều phối
                    <span class="badge badge-light text-dark ml-1"><?= count($pendingList) ?></span>
                </a>
                <a href="?route=dieu-phoi/index&tab=all" class="btn btn-sm <?= $activeTab === 'all' ? 'btn-primary' : 'btn-light' ?>">
                    <i class="bi bi-collection"></i> Tất cả phiếu nhập
                    <span class="badge badge-light text-dark ml-1"><?= count($allList) ?></span>
                </a>
            </div>
        </div>
    </div>

    <!-- THẺ 1: ĐIỀU KIỆN TRA CỨU -->
    <div class="card mb-4">
        <div class="card-head">
            <div class="card-head-title"><i class="bi bi-funnel"></i> Điều kiện tra cứu</div>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="form-grid grid-4 align-items-end">
                <input type="hidden" name="route" value="dieu-phoi/index">
                <input type="hidden" name="tab" value="<?= htmlspecialchars($activeTab) ?>">

                <div class="form-group">
                    <label class="form-label">Từ khóa tìm kiếm</label>
                    <input type="text" name="keyword" class="input" placeholder="Mã phiếu, đơn mua, nhà cung cấp..." value="<?= htmlspecialchars($keyword) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Kho tiếp nhận</label>
                    <select name="maKho" class="input">
                        <option value="">-- Tất cả kho --</option>
                        <?php foreach ($warehouses as $wh): ?>
                            <option value="<?= htmlspecialchars($wh['maKho']) ?>" <?= $maKho === $wh['maKho'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($wh['tenKho']) ?> (<?= htmlspecialchars($wh['maKho']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Trạng thái phiếu nhập</label>
                    <select name="trangThai" class="input">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="CHO_DIEU_PHOI" <?= $trangThai === 'CHO_DIEU_PHOI' ? 'selected' : '' ?>>Chờ điều phối</option>
                        <option value="DA_DIEU_PHOI" <?= $trangThai === 'DA_DIEU_PHOI' ? 'selected' : '' ?>>Đã điều phối</option>
                        <option value="DA_NHAP" <?= $trangThai === 'DA_NHAP' ? 'selected' : '' ?>>Đã nhập kho</option>
                        <option value="HUY" <?= $trangThai === 'HUY' ? 'selected' : '' ?>>Đã hủy</option>
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary mr-2"><i class="bi bi-search"></i> Tìm kiếm</button>
                    <a href="?route=dieu-phoi/index&tab=<?= htmlspecialchars($activeTab) ?>" class="btn btn-secondary">Đặt lại</a>
                </div>
            </form>
        </div>
    </div>

    <!-- THẺ 2: KẾT QUẢ TRA CỨU -->
    <div class="card mb-4">
        <div class="card-head space-between">
            <div class="card-head-title">
                <i class="bi bi-table"></i> Kết quả tra cứu
                <span class="badge badge-draft ml-2">
                    <?= $activeTab === 'pending' ? count($pendingList) . ' phiếu chờ điều phối' : count($allList) . ' phiếu nhập kho' ?>
                </span>
            </div>
            <div>
                <button type="button" class="btn btn-light btn-sm mr-2" onclick="alert('Tính năng Xuất Excel: Đang phát triển')">
                    <i class="bi bi-file-earmark-excel"></i> Xuất Excel
                </button>
                <button type="button" class="btn btn-light btn-sm" onclick="alert('Tính năng In danh sách: Đang phát triển')">
                    <i class="bi bi-printer"></i> In
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-wrapper">
                <?php if ($activeTab === 'pending'): ?>
                    <!-- TAB 1: DANH SÁCH CHỜ ĐIỀU PHỐI -->
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 140px;">Mã phiếu nhập</th>
                                <th>Kho tiếp nhận</th>
                                <th class="col-text-long">Nguồn nhập / NCC</th>
                                <th>Ngày lập</th>
                                <th class="text-right">Số dòng</th>
                                <th class="text-right">Tổng số lượng</th>
                                <th class="text-right">Thể tích (m³)</th>
                                <th class="text-center" style="width: 130px;">Trạng thái</th>
                                <th class="text-center col-actions" style="width: 150px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pendingList)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <i class="bi bi-check2-circle fs-3 d-block mb-1 text-success"></i>
                                        Hiện không có phiếu nhập nào đang chờ điều phối vị trí.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pendingList as $item): ?>
                                    <tr>
                                        <td>
                                            <strong class="text-primary"><?= htmlspecialchars($item['maPhieuNhap']) ?></strong>
                                            <?php if (!empty($item['maDonMua'])): ?>
                                                <small class="text-muted d-block"><?= htmlspecialchars($item['maDonMua']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span><?= htmlspecialchars($item['tenKho']) ?></span>
                                            <small class="text-muted d-block"><?= htmlspecialchars($item['maKho']) ?></small>
                                        </td>
                                        <td class="col-text-long">
                                            <?= htmlspecialchars($item['tenNCC'] ?: 'Nhập từ xưởng sản xuất') ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($item['ngayLap'])) ?></td>
                                        <td class="text-right"><?= (int)$item['soDongHang'] ?></td>
                                        <td class="text-right font-weight-bold"><?= formatSo($item['tongSoLuong']) ?></td>
                                        <td class="text-right"><?= formatSo($item['tongTheTich']) ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-pending">Chờ điều phối</span>
                                        </td>
                                        <td class="text-center col-actions">
                                            <?php if ($canCreate): ?>
                                                <a href="?route=dieu-phoi/create&req_id=<?= urlencode($item['maPhieuNhap']) ?>" class="btn btn-primary btn-sm">
                                                    <i class="bi bi-arrow-left-right"></i> Điều phối vị trí
                                                </a>
                                            <?php else: ?>
                                                <a href="?route=dieu-phoi/detail&id=<?= urlencode($item['maPhieuNhap']) ?>" class="btn btn-secondary btn-sm">
                                                    <i class="bi bi-eye"></i> Xem chi tiết
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <!-- TAB 2: TẤT CẢ PHIẾU NHẬP KHO -->
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 140px;">Mã phiếu nhập</th>
                                <th>Kho tiếp nhận</th>
                                <th class="col-text-long">Nguồn nhập / NCC</th>
                                <th>Thời gian lập</th>
                                <th class="text-right">Tổng số lượng</th>
                                <th>Lệnh điều phối</th>
                                <th class="text-center" style="width: 130px;">Trạng thái</th>
                                <th class="text-center col-actions" style="width: 120px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($allList)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">Chưa có dữ liệu phiếu nhập kho.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($allList as $item): ?>
                                    <tr>
                                        <td><strong class="text-primary"><?= htmlspecialchars($item['maPhieuNhap']) ?></strong></td>
                                        <td><?= htmlspecialchars($item['tenKho']) ?></td>
                                        <td class="col-text-long"><?= htmlspecialchars($item['tenNCC'] ?: 'Xưởng sản xuất') ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($item['ngayLap'])) ?></td>
                                        <td class="text-right font-weight-bold"><?= formatSo($item['tongSoLuong']) ?></td>
                                        <td>
                                            <?php if (!empty($item['maLenhDieuPhoi'])): ?>
                                                <a href="?route=lenh-dieu-phoi/detail&id=<?= urlencode($item['maLenhDieuPhoi']) ?>" class="font-weight-bold">
                                                    <?= htmlspecialchars($item['maLenhDieuPhoi']) ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($item['trangThai'] === 'DA_DIEU_PHOI'): ?>
                                                <span class="badge badge-ongoing">Đã điều phối</span>
                                            <?php elseif ($item['trangThai'] === 'DA_NHAP'): ?>
                                                <span class="badge badge-completed">Đã nhập kho</span>
                                            <?php elseif ($item['trangThai'] === 'CHO_DIEU_PHOI'): ?>
                                                <span class="badge badge-pending">Chờ điều phối</span>
                                            <?php else: ?>
                                                <span class="badge badge-failed"><?= htmlspecialchars($item['trangThai']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center col-actions">
                                            <a href="?route=dieu-phoi/detail&id=<?= urlencode($item['maPhieuNhap']) ?>" class="btn btn-secondary btn-sm">
                                                <i class="bi bi-eye"></i> Chi tiết
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

