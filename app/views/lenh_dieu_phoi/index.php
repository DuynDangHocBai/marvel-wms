<?php
/**
 * View: Lệnh điều phối được giao (Mẫu 01: Tra cứu / Danh sách dành cho NVK)
 * @var array $orders Danh sách lệnh điều phối
 * @var string $trangThai Trạng thái lọc
 * @var string $userRole Vai trò người dùng (NVK, QLK)
 * @var array $currentUser Thông tin người dùng
 * @var bool $canConfirm Quyền xác nhận nhận lệnh
 */
use App\Core\Csrf;
?>

<div class="page-container">
    <div class="page-header-box">
        <div>
            <div class="breadcrumb-nav">
                <a href="?route=dashboard/index">Kho vận</a>
                <span class="sep">›</span>
                <span class="current">Lệnh điều phối được giao</span>
            </div>
            <h1 class="page-title">Nhiệm vụ bốc dỡ & xếp dỡ hàng vào ô kệ</h1>
            <p class="page-subtitle">Danh sách các lệnh điều phối kho được giao cho nhân viên kho bốc xếp và lưu kho chính xác</p>
        </div>
        <div class="header-actions">
            <span class="badge badge-ongoing">BỐC XẾP & LƯU KHO</span>
        </div>
    </div>

    <!-- BỘ LỌC TRẠNG THÁI -->
    <div class="card mb-4">
        <div class="card-head">
            <div class="card-head-title"><i class="bi bi-funnel"></i> Lọc trạng thái lệnh</div>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="form-grid grid-3 align-items-end">
                <input type="hidden" name="route" value="lenh-dieu-phoi/index">

                <div class="form-group">
                    <label class="form-label">Trạng thái thực hiện</label>
                    <select name="trangThai" class="input">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="CHO_THUC_HIEN" <?= $trangThai === 'CHO_THUC_HIEN' ? 'selected' : '' ?>>Chờ thực hiện (Chưa nhận lệnh)</option>
                        <option value="DANG_THUC_HIEN" <?= $trangThai === 'DANG_THUC_HIEN' ? 'selected' : '' ?>>Đang thực hiện (Đã nhận lệnh)</option>
                        <option value="HOAN_THANH" <?= $trangThai === 'HOAN_THANH' ? 'selected' : '' ?>>Đã hoàn thành</option>
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary mr-2"><i class="bi bi-search"></i> Lọc dữ liệu</button>
                    <a href="?route=lenh-dieu-phoi/index" class="btn btn-secondary">Đặt lại</a>
                </div>
            </form>
        </div>
    </div>

    <!-- BẢNG DANH SÁCH LỆNH ĐIỀU PHỐI -->
    <div class="card mb-4">
        <div class="card-head space-between">
            <div class="card-head-title">
                <i class="bi bi-card-checklist"></i> Danh sách lệnh điều phối
                <span class="badge badge-draft ml-2"><?= count($orders) ?> lệnh</span>
            </div>
            <div>
                <button type="button" class="btn btn-light btn-sm" onclick="alert('Tính năng In danh sách: Đang phát triển')">
                    <i class="bi bi-printer"></i> In danh sách
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 140px;">Mã Lệnh ĐP</th>
                            <th>Phiếu nhập nguồn</th>
                            <th class="col-text-long">Kho thực hiện</th>
                            <th>Ngày phát lệnh</th>
                            <th class="text-center">Số vị trí</th>
                            <th class="text-right">Tổng số lượng</th>
                            <th>Nhân viên thực hiện</th>
                            <th class="text-center" style="width: 130px;">Trạng thái</th>
                            <th class="text-center col-actions" style="width: 170px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                    Không có lệnh điều phối nào được giao.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $o): ?>
                                <tr>
                                    <td><strong class="text-primary"><?= htmlspecialchars($o['maLenhDieuPhoi']) ?></strong></td>
                                    <td>
                                        <a href="?route=dieu-phoi/detail&id=<?= urlencode($o['maPhieuNhap']) ?>" class="font-weight-bold">
                                            <?= htmlspecialchars($o['maPhieuNhap']) ?>
                                        </a>
                                    </td>
                                    <td class="col-text-long">
                                        <span><?= htmlspecialchars($o['tenKho']) ?></span>
                                        <small class="text-muted d-block"><?= htmlspecialchars($o['maKho']) ?></small>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($o['ngayPhatLenh'])) ?></td>
                                    <td class="text-center">
                                        <span class="badge badge-light"><?= (int)$o['soViTri'] ?> vị trí</span>
                                    </td>
                                    <td class="text-right font-weight-bold"><?= formatSo($o['tongSoLuong']) ?></td>
                                    <td><?= htmlspecialchars($o['tenNhanVienThucHien'] ?? $o['maNhanVienThucHien']) ?></td>
                                    <td class="text-center">
                                        <?php if ($o['trangThai'] === 'CHO_THUC_HIEN'): ?>
                                            <span class="badge badge-pending">Chờ thực hiện</span>
                                        <?php elseif ($o['trangThai'] === 'DANG_THUC_HIEN'): ?>
                                            <span class="badge badge-ongoing">Đang thực hiện</span>
                                        <?php elseif ($o['trangThai'] === 'HOAN_THANH'): ?>
                                            <span class="badge badge-completed">Hoàn thành</span>
                                        <?php else: ?>
                                            <span class="badge badge-failed"><?= htmlspecialchars($o['trangThai']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center col-actions">
                                        <a href="?route=lenh-dieu-phoi/detail&id=<?= urlencode($o['maLenhDieuPhoi']) ?>" class="btn btn-secondary btn-sm mr-1">
                                            <i class="bi bi-eye"></i> Chi tiết
                                        </a>
                                        <?php if ($o['trangThai'] === 'CHO_THUC_HIEN' && $canConfirm): ?>
                                            <form method="POST" action="?route=lenh-dieu-phoi/confirm" style="display: inline-block;">
                                                <?= Csrf::field() ?>
                                                <input type="hidden" name="maLenhDieuPhoi" value="<?= htmlspecialchars($o['maLenhDieuPhoi']) ?>">
                                                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Xác nhận đã nhận lệnh và bắt đầu xếp dỡ hàng vào ô kệ?')">
                                                    <i class="bi bi-check2"></i> Nhận lệnh
                                                </button>
                                            </form>
                                        <?php endif; ?>
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

