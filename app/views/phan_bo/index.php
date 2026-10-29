<?php
/**
 * View: Danh sách phân bổ số lượng tồn kho (Mẫu 01: Tra cứu / Danh sách 2 Tab)
 * @var string $activeTab Tab đang xem ('pending' hoặc 'history')
 * @var string $keyword Từ khóa tìm kiếm
 * @var string $maKho Mã kho lọc
 * @var string $trangThai Trạng thái lọc
 * @var array $pendingOrders Danh sách lệnh chờ phân bổ
 * @var array $history Danh sách lịch sử phiếu phân bổ
 * @var array $warehouses Danh mục kho
 * @var bool $canCreate Người dùng có quyền tạo phiếu không
 */
?>

<div class="page-container">
    <div class="page-header-box">
        <div>
            <div class="breadcrumb-nav">
                <a href="?route=dashboard/index">Kho vận</a>
                <span class="sep">›</span>
                <span class="current">Phân bổ số lượng</span>
            </div>
            <h1 class="page-title">Phân bổ số lượng nguyên vật liệu lưu kho</h1>
            <p class="page-subtitle">Phân bổ tồn kho khả dụng theo lô và vị trí cho các lệnh sản xuất đã phát hành theo chuẩn FEFO</p>
        </div>
        <div class="header-actions">
            <?php if ($canCreate): ?>
                <a href="?route=phan-bo/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Lập phiếu phân bổ mới
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- TABS ĐIỀU HƯỚNG -->
    <div class="card mb-4">
        <div class="card-body p-2" style="border-bottom: 1px solid var(--border-color);">
            <div class="d-flex align-items-center">
                <a href="?route=phan-bo/index&tab=pending" class="btn btn-sm <?= $activeTab === 'pending' ? 'btn-primary' : 'btn-light' ?> mr-2">
                    <i class="bi bi-hourglass-split"></i> Lệnh chờ phân bổ
                    <span class="badge badge-light text-dark ml-1"><?= count($pendingOrders) ?></span>
                </a>
                <a href="?route=phan-bo/index&tab=history" class="btn btn-sm <?= $activeTab === 'history' ? 'btn-primary' : 'btn-light' ?>">
                    <i class="bi bi-clock-history"></i> Lịch sử phiếu phân bổ
                    <span class="badge badge-light text-dark ml-1"><?= count($history) ?></span>
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
                <input type="hidden" name="route" value="phan-bo/index">
                <input type="hidden" name="tab" value="<?= htmlspecialchars($activeTab) ?>">

                <div class="form-group">
                    <label class="form-label">Từ khóa tìm kiếm</label>
                    <input type="text" name="keyword" class="input" placeholder="Mã lệnh, phiếu, xưởng, kho..." value="<?= htmlspecialchars($keyword) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Kho nguồn</label>
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
                    <label class="form-label">Trạng thái</label>
                    <select name="trangThai" class="input">
                        <option value="">-- Tất cả trạng thái --</option>
                        <?php if ($activeTab === 'history'): ?>
                            <option value="HOAN_THANH" <?= $trangThai === 'HOAN_THANH' ? 'selected' : '' ?>>Hoàn thành</option>
                            <option value="DANG_PHAN_BO" <?= $trangThai === 'DANG_PHAN_BO' ? 'selected' : '' ?>>Đang phân bổ</option>
                            <option value="HUY" <?= $trangThai === 'HUY' ? 'selected' : '' ?>>Đã hủy</option>
                        <?php else: ?>
                            <option value="DA_PHAT" <?= $trangThai === 'DA_PHAT' ? 'selected' : '' ?>>Đã phát lệnh (Chờ phân bổ)</option>
                            <option value="DANG_PHAN_BO" <?= $trangThai === 'DANG_PHAN_BO' ? 'selected' : '' ?>>Đang phân bổ dở dang</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary mr-2"><i class="bi bi-search"></i> Tìm kiếm</button>
                    <a href="?route=phan-bo/index&tab=<?= htmlspecialchars($activeTab) ?>" class="btn btn-secondary">Đặt lại</a>
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
                    <?= $activeTab === 'pending' ? count($pendingOrders) . ' lệnh chờ phân bổ' : count($history) . ' phiếu phân bổ' ?>
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
                    <!-- BẢNG TAB 1: LỆNH CHỜ PHÂN BỔ -->
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 140px;">Mã Lệnh SX</th>
                                <th class="col-text-long">Kế hoạch / Xưởng</th>
                                <th>Thời gian sản xuất</th>
                                <th class="text-center">Loại NVL</th>
                                <th class="text-right">Tổng cần cấp</th>
                                <th class="text-right">Đã phân bổ</th>
                                <th class="text-right">Còn thiếu</th>
                                <th class="text-center" style="width: 130px;">Trạng thái</th>
                                <th class="text-center col-actions" style="width: 140px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pendingOrders)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <i class="bi bi-check2-circle fs-3 d-block mb-1 text-success"></i>
                                        Hiện không có lệnh sản xuất nào đang chờ phân bổ nguyên vật liệu.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pendingOrders as $order): ?>
                                    <tr>
                                        <td>
                                            <strong class="text-primary"><?= htmlspecialchars($order['maLenhSX']) ?></strong>
                                        </td>
                                        <td class="col-text-long">
                                            <div class="font-weight-bold"><?= htmlspecialchars($order['maKeHoach']) ?></div>
                                            <small class="text-muted"><i class="bi bi-building-gear"></i> <?= htmlspecialchars($order['tenXuong'] ?? $order['maXuong']) ?></small>
                                        </td>
                                        <td>
                                            <div><?= date('d/m/Y', strtotime($order['ngayBatDau'])) ?> → <?= date('d/m/Y', strtotime($order['ngayHoanThanh'])) ?></div>
                                            <small class="text-muted">Lập ngày <?= date('d/m/Y', strtotime($order['ngayLap'])) ?></small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge <?= $order['soLoaiNVLDaDu'] == $order['tongLoaiNVL'] ? 'badge-passed' : 'badge-draft' ?>">
                                                <?= $order['soLoaiNVLDaDu'] ?> / <?= $order['tongLoaiNVL'] ?> loại đủ
                                            </span>
                                        </td>
                                        <td class="text-right"><?= formatSo($order['tongCanCap']) ?></td>
                                        <td class="text-right text-success"><?= formatSo($order['tongDaPhanBo']) ?></td>
                                        <td class="text-right font-weight-bold text-danger"><?= formatSo($order['tongConThieu']) ?></td>
                                        <td class="text-center">
                                            <?php if ($order['trangThai'] === 'DA_PHAT'): ?>
                                                <span class="badge badge-pending">Chờ phân bổ</span>
                                            <?php else: ?>
                                                <span class="badge badge-ongoing">Đang phân bổ</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center col-actions">
                                            <?php if ($canCreate): ?>
                                                <a href="?route=phan-bo/create&order_id=<?= urlencode($order['maLenhSX']) ?>" class="btn btn-primary btn-sm">
                                                    <i class="bi bi-pie-chart"></i> Phân bổ ngay
                                                </a>
                                            <?php else: ?>
                                                <a href="?route=lenh-san-xuat/detail&id=<?= urlencode($order['maLenhSX']) ?>" class="btn btn-secondary btn-sm">
                                                    <i class="bi bi-eye"></i> Xem LSX
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <!-- BẢNG TAB 2: LỊCH SỬ PHIẾU PHÂN BỔ -->
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 150px;">Mã phiếu</th>
                                <th>Thời gian phân bổ</th>
                                <th>Lệnh sản xuất</th>
                                <th>Kho nguồn xuất</th>
                                <th class="text-center">Số lô / Vị trí</th>
                                <th class="text-right">Tổng SL phân bổ</th>
                                <th>Người thực hiện</th>
                                <th class="text-center" style="width: 120px;">Trạng thái</th>
                                <th class="text-center col-actions" style="width: 120px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($history)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                        Chưa có phiếu phân bổ số lượng nào được tạo.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($history as $item): ?>
                                    <tr>
                                        <td>
                                            <strong class="text-primary"><?= htmlspecialchars($item['maPhieuPhanBo']) ?></strong>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($item['ngayPhanBo'])) ?></td>
                                        <td>
                                            <a href="?route=lenh-san-xuat/detail&id=<?= urlencode($item['maLenhSX']) ?>" class="font-weight-bold text-decoration-none">
                                                <?= htmlspecialchars($item['maLenhSX']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span><?= htmlspecialchars($item['tenKhoNguon']) ?></span>
                                            <small class="text-muted d-block"><?= htmlspecialchars($item['maKhoNguon']) ?></small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-light"><?= (int)$item['soDongPhanBo'] ?> vị trí</span>
                                        </td>
                                        <td class="text-right font-weight-bold text-success">
                                            <?= formatSo($item['tongSoLuongPhanBo']) ?>
                                        </td>
                                        <td><?= htmlspecialchars($item['tenNhanVien'] ?? $item['maNhanVien']) ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-completed">Hoàn thành</span>
                                        </td>
                                        <td class="text-center col-actions">
                                            <a href="?route=phan-bo/detail&id=<?= urlencode($item['maPhieuPhanBo']) ?>" class="btn btn-secondary btn-sm">
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

