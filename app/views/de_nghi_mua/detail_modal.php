<?php
/**
 * View: Chi tiết đề nghị mua nguyên vật liệu (Mẫu 02: Modal chi tiết chỉ đọc kèm KPI & Bảng mặt hàng)
 * @var array $proposal Thông tin đề nghị mua và danh sách NVL
 * @var bool $canCancel Quyền hủy đề nghị
 */
$maDN = htmlspecialchars($proposal['maDeNghi'] ?? '');
$items = $proposal['items'] ?? [];
$tongTien = (float)($proposal['tongTienDuKien'] ?? 0);
$tongSL = (float)($proposal['tongSoLuongMua'] ?? 0);
$soMatHang = count($items);

$badgeClass = 'badge-draft';
$badgeText = $proposal['trangThai'] ?? 'CHO_DUYET';
switch ($proposal['trangThai']) {
    case 'CHO_DUYET':
        $badgeClass = 'badge-pending';
        $badgeText = 'Chờ duyệt';
        break;
    case 'DA_DUYET':
        $badgeClass = 'badge-passed';
        $badgeText = 'Đã duyệt';
        break;
    case 'TU_CHOI':
        $badgeClass = 'badge-failed';
        $badgeText = 'Từ chối';
        break;
    case 'DA_HUY':
        $badgeClass = 'badge-draft';
        $badgeText = 'Đã hủy';
        break;
    case 'DA_LAP_DON_MUA':
        $badgeClass = 'badge-completed';
        $badgeText = 'Đã lập đơn mua';
        break;
}
?>

<div class="page-container">
    <div class="page-header-box">
        <div>
            <div class="breadcrumb-nav">
                <a href="?route=de-nghi-mua/index">Kho vận</a>
                <span class="sep">›</span>
                <a href="?route=de-nghi-mua/index">Đề nghị mua NVL</a>
                <span class="sep">›</span>
                <span class="current"><?= $maDN ?></span>
            </div>
            <h1 class="page-title">Đề nghị mua: <?= $maDN ?></h1>
            <p class="page-subtitle">Chi tiết nhu cầu vật tư dự toán phục vụ sản xuất bánh kẹo và cân đối tồn kho</p>
        </div>
        <div class="header-actions">
            <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($badgeText) ?></span>
            <button type="button" class="btn btn-secondary btn-sm ml-2" onclick="alert('Tính năng In phiếu đang phát triển!');">
                <i class="bi bi-printer"></i> In phiếu
            </button>
            <a href="?route=de-nghi-mua/index" class="btn btn-secondary btn-sm ml-2">
                <i class="bi bi-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- 3 THẺ KPI THEO MẪU 02 -->
    <div class="form-grid grid-3 mb-4">
        <div class="kpi-card">
            <div class="kpi-label"><i class="bi bi-cash-stack text-success"></i> TỔNG TIỀN DỰ TOÁN</div>
            <div class="kpi-value text-success"><?= formatSo($tongTien, true) ?></div>
            <div class="kpi-sub text-muted">Dựa trên giá nhập danh mục NVL</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label"><i class="bi bi-box-seam text-primary"></i> TỔNG SỐ LƯỢNG MUA</div>
            <div class="kpi-value text-primary"><?= formatSo($tongSL) ?></div>
            <div class="kpi-sub text-muted">Tổng khối lượng / đơn vị nguyên vật liệu</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label"><i class="bi bi-list-check text-info"></i> SỐ MẶT HÀNG</div>
            <div class="kpi-value text-info"><?= $soMatHang ?></div>
            <div class="kpi-sub text-muted">Mặt hàng NVL cần thu mua</div>
        </div>
    </div>

    <!-- THÔNG TIN CHUNG (LƯỚI 2 CỘT MẪU 02) -->
    <div class="card mb-4">
        <div class="card-head">
            <div class="card-head-title"><i class="bi bi-info-circle"></i> Thông tin chung đề nghị mua</div>
            <?php if ($proposal['trangThai'] === 'CHO_DUYET' && $canCancel): ?>
                <button type="button" class="btn btn-danger btn-sm" onclick="showCancelModal()">
                    <i class="bi bi-x-circle"></i> Hủy đề nghị này
                </button>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <div class="form-grid grid-2">
                <div>
                    <p class="mb-2"><strong>Mã chứng từ:</strong> <span class="text-primary font-weight-bold"><?= $maDN ?></span></p>
                    <p class="mb-2"><strong>Kế hoạch sản xuất:</strong> 
                        <?php if (!empty($proposal['maKeHoach'])): ?>
                            <span class="badge badge-subtle"><?= htmlspecialchars($proposal['maKeHoach']) ?></span>
                        <?php else: ?>
                            <span class="text-muted">Mua bổ sung tự do (Không theo KHSX)</span>
                        <?php endif; ?>
                    </p>
                    <p class="mb-2"><strong>Người lập:</strong> <?= htmlspecialchars($proposal['tenNhanVienLap'] ?? $proposal['maNhanVienLap'] ?? 'Chưa rõ') ?> (Bộ phận Kế hoạch)</p>
                </div>
                <div>
                    <p class="mb-2"><strong>Ngày lập:</strong> <?= date('d/m/Y H:i', strtotime($proposal['ngayLap'])) ?></p>
                    <p class="mb-2"><strong>Ngày cần hàng:</strong> <span class="text-danger font-weight-bold"><?= date('d/m/Y', strtotime($proposal['ngayCanHang'])) ?></span></p>
                    <p class="mb-2"><strong>Lý do / Mục đích:</strong> <?= htmlspecialchars($proposal['lyDo'] ?? 'Cung cấp NVL cho kế hoạch sản xuất') ?></p>
                </div>
            </div>

            <!-- HỘP QUY TẮC NGHIỆP VỤ -->
            <div class="alert alert-info mt-3 mb-0" style="font-size: 0.88rem; background: var(--bg-surface-soft, #f8fafc); border-left: 4px solid var(--primary, #0284c7); padding: 12px 16px; border-radius: 4px;">
                <div class="font-weight-bold text-dark mb-1"><i class="bi bi-calculator"></i> Quy tắc nghiệp vụ tính số lượng đề xuất:</div>
                <div class="text-muted">
                    <code>Số lượng đề xuất = MAX(0, Nhu cầu SX + Tồn tối thiểu - Tồn khả dụng - Đang về)</code>.<br>
                    Tồn khả dụng đã trừ giữ chỗ từ các lệnh sản xuất khác (<code>tonKhaDung = soLuongThucTe - soLuongGiuCho</code>).
                </div>
            </div>
        </div>
    </div>

    <!-- DANH SÁCH CHI TIẾT CÁC MẶT HÀNG MUA -->
    <div class="card">
        <div class="card-head">
            <div class="card-head-title"><i class="bi bi-cart3"></i> Danh mục nguyên vật liệu chi tiết (<?= $soMatHang ?> mặt hàng)</div>
        </div>
        <div class="card-body p-0">
            <div class="table-wrapper">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th style="width: 50px;" class="text-center">#</th>
                            <th style="width: 110px;">Mã SKU</th>
                            <th>Tên nguyên vật liệu</th>
                            <th class="text-center" style="width: 70px;">ĐVT</th>
                            <th class="text-right" style="width: 95px;">Nhu cầu SX</th>
                            <th class="text-right" style="width: 95px;">Tồn K.Dụng</th>
                            <th class="text-right" style="width: 95px;">Tồn tối thiểu</th>
                            <th class="text-right" style="width: 95px;">Đang về</th>
                            <th class="text-right text-muted" style="width: 95px;">SL Đề xuất</th>
                            <th class="text-right text-primary font-weight-bold" style="width: 105px;">SL Đặt mua</th>
                            <th class="text-right" style="width: 105px;">Đơn giá</th>
                            <th class="text-right" style="width: 125px;">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="12" class="text-center text-muted py-4">Không có mặt hàng nào trong đề nghị này.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $idx => $it): ?>
                                <?php 
                                    $isExtra = (int)($it['ngoaiKeHoach'] ?? 0) === 1;
                                    $tt = (float)($it['thanhTienDuKien'] ?? ((float)$it['soLuongMua'] * (float)$it['giaNhap']));
                                ?>
                                <tr>
                                    <td class="text-center text-muted"><?= $idx + 1 ?></td>
                                    <td>
                                        <span class="font-weight-bold"><?= htmlspecialchars($it['maSKU']) ?></span>
                                        <?php if ($isExtra): ?>
                                            <br><span class="badge badge-warning" style="font-size: 0.7rem;">Ngoài KHSX</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="col-text-long">
                                        <?= htmlspecialchars($it['tenHang'] ?? '') ?>
                                    </td>
                                    <td class="text-center"><?= htmlspecialchars($it['donViTinh'] ?? '') ?></td>
                                    <td class="text-right"><?= formatSo((float)($it['nhuCauSX'] ?? 0)) ?></td>
                                    <td class="text-right"><?= formatSo((float)($it['tonKhaDung'] ?? 0)) ?></td>
                                    <td class="text-right"><?= formatSo((float)($it['tonToiThieu'] ?? 0)) ?></td>
                                    <td class="text-right"><?= formatSo((float)($it['hangDangVe'] ?? 0)) ?></td>
                                    <td class="text-right text-muted"><?= formatSo((float)($it['soLuongDeXuat'] ?? 0)) ?></td>
                                    <td class="text-right text-primary font-weight-bold" style="font-size: 1rem;">
                                        <?= formatSo((float)($it['soLuongMua'] ?? 0)) ?>
                                    </td>
                                    <td class="text-right text-muted"><?= formatSo((float)($it['giaNhap'] ?? 0), true) ?></td>
                                    <td class="text-right font-weight-bold text-success"><?= formatSo($tt, true) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background: var(--bg-surface-soft, #f8fafc); font-weight: bold;">
                            <td colspan="9" class="text-right">TỔNG CỘNG TIỀN DỰ KIẾN:</td>
                            <td class="text-right text-primary"><?= formatSo($tongSL) ?></td>
                            <td></td>
                            <td class="text-right text-success" style="font-size: 1.1rem;">
                                <?= formatSo($tongTien, true) ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL HỦY ĐỀ NGHỊ NẾU CÓ QUYỀN VÀ TRẠNG THÁI CHO_DUYET -->
<?php if ($proposal['trangThai'] === 'CHO_DUYET' && $canCancel): ?>
<div id="cancelModal" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 480px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div class="card-head">
            <div class="card-head-title text-danger"><i class="bi bi-exclamation-triangle"></i> Xác nhận hủy đề nghị mua</div>
            <button type="button" class="btn btn-sm text-muted" onclick="hideCancelModal()">&times;</button>
        </div>
        <form method="POST" action="?route=de-nghi-mua/cancel">
            <?= \App\Core\Csrf::field() ?>
            <input type="hidden" name="maDeNghi" value="<?= $maDN ?>">
            <div class="card-body">
                <p>Bạn có chắc chắn muốn hủy đề nghị mua nguyên vật liệu <strong><?= $maDN ?></strong>?</p>
                <div class="form-group mb-3">
                    <label class="font-weight-bold">Lý do hủy: <span class="text-danger">*</span></label>
                    <textarea name="lyDoHuy" class="form-control" rows="3" required placeholder="Nhập lý do hủy đề nghị..."></textarea>
                </div>
                <div class="text-muted" style="font-size: 0.85rem;">
                    Lưu ý: Hành động này không thể hoàn tác sau khi xác nhận.
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end gap-2" style="background: var(--bg-surface-soft); padding: 12px 16px;">
                <button type="button" class="btn btn-secondary" onclick="hideCancelModal()">Bỏ qua</button>
                <button type="submit" class="btn btn-danger">Xác nhận hủy đề nghị</button>
            </div>
        </form>
    </div>
</div>

<script>
function showCancelModal() {
    var m = document.getElementById('cancelModal');
    if (m) m.style.display = 'flex';
}
function hideCancelModal() {
    var m = document.getElementById('cancelModal');
    if (m) m.style.display = 'none';
}
</script>
<?php endif; ?>

