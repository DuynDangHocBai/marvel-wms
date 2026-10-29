<?php
/**
 * View: Lập đề xuất xử lý hàng lỗi hỏng (Mẫu 03: Form lập chứng từ)
 * @var array $items Danh sách hàng lỗi hỏng còn có thể đề xuất (soLuongConLai > 0)
 * @var array $input Dữ liệu form đã submit
 * @var array|null $preview Kết quả tổng hợp nếu đã bấm nút Tổng hợp giá trị
 * @var array $currentUser Thông tin người dùng đăng nhập
 */
use App\Core\Csrf;

$selected = $input['selected'] ?? [];
if (!is_array($selected)) $selected = [];
$qtyInput = $input['qty'] ?? [];
if (!is_array($qtyInput)) $qtyInput = [];
$phuongAn = $input['phuongAn'] ?? 'TIEU_HUY';
$lyDo = $input['lyDo'] ?? '';
?>

<div class="page-container">
    <div class="page-header-box">
        <div>
            <div class="breadcrumb-nav">
                <a href="?route=hang-loi/index">Kho vận</a>
                <span class="sep">›</span>
                <a href="?route=hang-loi/index">Hàng lỗi hỏng</a>
                <span class="sep">›</span>
                <span class="current">Lập đề xuất xử lý mới</span>
            </div>
            <h1 class="page-title">Lập đề xuất xử lý bánh kẹo lỗi, hỏng</h1>
            <p class="page-subtitle">Chọn các lô bánh kẹo hỏng, lỗi bao bì hoặc cận date để đề xuất tiêu hủy hoặc trả lại nhà cung cấp</p>
        </div>
        <div class="header-actions">
            <span class="badge badge-ongoing">ĐANG SOẠN THẢO</span>
        </div>
    </div>

    <form id="proposalForm" method="POST" action="?route=hang-loi/create">
        <?= Csrf::field() ?>
        <input type="hidden" name="action" id="actionInput" value="save">

        <!-- THẺ 1: THÔNG TIN ĐỀ XUẤT -->
        <div class="card mb-4">
            <div class="card-head">
                <div class="card-head-title">
                    <i class="bi bi-info-circle"></i> 1. Thông tin đề xuất xử lý
                </div>
            </div>
            <div class="card-body">
                <div class="form-grid grid-3">
                    <div class="form-group">
                        <label class="form-label text-muted">Người lập đề xuất (Khóa)</label>
                        <input type="text" class="input bg-light" value="<?= htmlspecialchars(($currentUser['hoTen'] ?? 'Quản lý kho') . ' (' . ($currentUser['chucDanh'] ?? 'QLK') . ')') ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phương án xử lý <span class="text-danger">*</span></label>
                        <select name="phuongAn" class="input" id="phuongAnSelect" required>
                            <option value="TIEU_HUY" <?= $phuongAn === 'TIEU_HUY' ? 'selected' : '' ?>>Tiêu hủy (Hủy bỏ sản phẩm hỏng/cận date)</option>
                            <option value="TRA_NCC" <?= $phuongAn === 'TRA_NCC' ? 'selected' : '' ?>>Trả lại nhà cung cấp (Lỗi nguồn gốc nhập)</option>
                        </select>
                        <small class="text-muted mt-1 d-block">Lưu ý: Hàng trả về từ khách không áp dụng trả lại NCC</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ngày đề xuất</label>
                        <input type="text" class="input bg-light" value="<?= date('d/m/Y') ?>" readonly>
                    </div>
                </div>
                <div class="form-group mt-3">
                    <label class="form-label">Lý do xử lý chi tiết <span class="text-danger">*</span></label>
                    <textarea name="lyDo" class="input" rows="3" maxlength="2000" required placeholder="Nhập lý do cụ thể (vd: Bánh quy lô BQ-1026-01 bị bong seal, kẹo rách bao bì, quá hạn lưu kho...)..."><?= htmlspecialchars($lyDo) ?></textarea>
                    <small class="text-muted">Nêu rõ tình trạng lỗi bao bì, kết luận QC, số lô hoặc lý do khách trả (tối đa 2.000 ký tự).</small>
                </div>
            </div>
        </div>

        <!-- THẺ 2: HÀNG LỖI HỎNG CẦN XỬ LÝ -->
        <div class="card mb-4">
            <div class="card-head space-between">
                <div class="card-head-title">
                    <i class="bi bi-box-seam"></i> 2. Chọn bánh kẹo lỗi, hỏng cần xử lý
                    <span class="badge badge-draft ml-2"><?= count($items) ?> mặt hàng khả dụng</span>
                </div>
                <div>
                    <input type="text" id="filterItemInput" class="input input-sm" placeholder="Lọc nhanh tên, SKU, số lô..." style="width: 250px;">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-wrapper">
                    <table class="table" id="itemTable">
                        <thead>
                            <tr>
                                <th style="width: 50px;" class="text-center">CHỌN</th>
                                <th style="width: 120px;">Mã SKU</th>
                                <th class="col-text-long">Tên bánh kẹo / Mặt hàng</th>
                                <th>Lô / Hạn sử dụng</th>
                                <th>Kho / Vị trí</th>
                                <th>Chứng từ / Nguyên nhân</th>
                                <th class="text-right" style="width: 120px;">Lỗi còn lại</th>
                                <th class="text-right" style="width: 120px;">Đơn giá</th>
                                <th class="text-right" style="width: 130px;">Số lượng xử lý</th>
                                <th class="text-right" style="width: 140px;">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted">
                                        Hiện không có mặt hàng lỗi hỏng nào đang chờ xử lý.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($items as $item): ?>
                                    <?php
                                    $id = $item['maHangLoi'];
                                    $isChecked = in_array($id, $selected);
                                    $conLai = (float)$item['soLuongConLai'];
                                    $currentQty = $qtyInput[$id] ?? '';
                                    $donGia = (float)$item['donGia'];
                                    $thanhTienRow = ($isChecked && is_numeric($currentQty)) ? ((float)$currentQty * $donGia) : 0;
                                    ?>
                                    <tr class="item-row" data-search="<?= strtolower(htmlspecialchars($item['tenHang'] . ' ' . $item['maSKU'] . ' ' . $item['soLo'])) ?>">
                                        <td class="text-center">
                                            <input type="checkbox" name="selected[]" value="<?= htmlspecialchars($id) ?>" class="item-checkbox" data-id="<?= htmlspecialchars($id) ?>" <?= $isChecked ? 'checked' : '' ?> onchange="toggleItem(this)">
                                        </td>
                                        <td><strong><?= htmlspecialchars($item['maSKU']) ?></strong></td>
                                        <td class="col-text-long">
                                            <div class="fw-bold"><?= htmlspecialchars($item['tenHang']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($item['maHangLoi']) ?> · <?= htmlspecialchars($item['donViTinh']) ?></small>
                                        </td>
                                        <td>
                                            <div><strong><?= htmlspecialchars($item['soLo']) ?></strong></div>
                                            <small class="text-muted">HSD: <?= htmlspecialchars(date('d/m/Y', strtotime($item['ngayHetHan']))) ?></small>
                                        </td>
                                        <td>
                                            <div><?= htmlspecialchars($item['maKho']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($item['tenKho'] ?? '') ?></small>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($item['nguyenNhan']) ?>">
                                                <?= htmlspecialchars($item['nguyenNhan']) ?>
                                            </div>
                                            <small class="text-muted"><?= htmlspecialchars($item['maChungTuNguon']) ?></small>
                                        </td>
                                        <td class="text-right">
                                            <strong><?= formatSo($conLai) ?></strong>
                                            <small class="text-muted d-block"><?= htmlspecialchars($item['donViTinh']) ?></small>
                                        </td>
                                        <td class="text-right">
                                            <span class="item-price" data-price="<?= $donGia ?>"><?= formatSo($donGia, true) ?></span>
                                        </td>
                                        <td class="text-right">
                                            <input type="number" name="qty[<?= htmlspecialchars($id) ?>]" class="input input-sm text-right qty-input" id="qty_<?= htmlspecialchars($id) ?>" min="1" max="<?= $conLai ?>" step="1" placeholder="SL" value="<?= htmlspecialchars((string)$currentQty) ?>" <?= !$isChecked ? 'disabled' : '' ?> oninput="calcRow('<?= htmlspecialchars($id) ?>')" style="width: 100px; min-width: 90px; display: inline-block; font-weight: 600;">
                                        </td>
                                        <td class="text-right fw-bold text-primary">
                                            <span id="row_total_<?= htmlspecialchars($id) ?>"><?= formatSo($thanhTienRow, true) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer space-between">
                <span id="selectedCountText" class="text-muted">Đã chọn 0 mặt hàng</span>
                <span class="small text-muted">Số lượng xử lý không được vượt quá số lượng lỗi còn lại trong kho</span>
            </div>
        </div>

        <!-- THẺ 3: TỔNG HỢP GIÁ TRỊ & PREVIEW -->
        <div class="card mb-4">
            <div class="card-head">
                <div class="card-head-title">
                    <i class="bi bi-calculator"></i> 3. Tổng hợp giá trị đề xuất
                </div>
            </div>
            <div class="card-body">
                <div class="grid-3 mb-3">
                    <div class="kpi-card">
                        <div class="kpi-label">SỐ MẶT HÀNG ĐÃ CHỌN</div>
                        <div class="kpi-value" id="kpiSelectedCount">0</div>
                        <div class="kpi-sub text-muted">SKU bánh kẹo cần xử lý</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-label">TỔNG SỐ LƯỢNG XỬ LÝ</div>
                        <div class="kpi-value" id="kpiTotalQty">0</div>
                        <div class="kpi-sub text-muted">Tổng số đơn vị bánh kẹo</div>
                    </div>
                    <div class="kpi-card featured">
                        <div class="kpi-label">TỔNG GIÁ TRỊ TẠM TÍNH</div>
                        <div class="kpi-value text-primary" id="kpiTotalPrice">0 đ</div>
                        <div class="kpi-sub text-muted">Căn cứ để Ban Giám Đốc xem xét</div>
                    </div>
                </div>

                <?php if ($preview): ?>
                    <div class="alert alert-success mt-3">
                        <div class="fw-bold mb-2"><i class="bi bi-check-circle"></i> Kết quả tổng hợp từ hệ thống (Đã kiểm tra hợp lệ):</div>
                        <div class="table-wrapper">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th class="col-text-long">Mặt hàng</th>
                                        <th>Lô / HSD</th>
                                        <th class="text-right">Số lượng xử lý</th>
                                        <th class="text-right">Đơn giá</th>
                                        <th class="text-right">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($preview['chiTiet'] as $pLine): ?>
                                        <tr>
                                            <td class="col-text-long"><strong><?= htmlspecialchars($pLine['tenHang']) ?></strong> (<?= htmlspecialchars($pLine['maSKU']) ?>)</td>
                                            <td><?= htmlspecialchars($pLine['soLo']) ?> · <?= htmlspecialchars(date('d/m/Y', strtotime($pLine['ngayHetHan']))) ?></td>
                                            <td class="text-right"><?= formatSo($pLine['soLuongXuLy']) ?> <?= htmlspecialchars($pLine['donViTinh']) ?></td>
                                            <td class="text-right"><?= formatSo($pLine['donGia'], true) ?></td>
                                            <td class="text-right fw-bold"><?= formatSo($pLine['thanhTien'], true) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="table-active">
                                        <td colspan="4" class="text-right fw-bold">TỔNG GIÁ TRỊ TOÀN BỘ ĐỀ XUẤT:</td>
                                        <td class="text-right fw-bold text-primary fs-5"><?= formatSo($preview['tongGiaTri'], true) ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- THẺ 4: QUY TẮC NGHIỆP VỤ BR-01 & BR-09 -->
        <div class="rule-box mb-5">
            <div class="rule-box-title">
                <i class="bi bi-shield-check"></i> Quy định xử lý hàng lỗi hỏng (Bảng 13 BR-01 & BR-09):
            </div>
            <ul class="rule-box-list">
                <li><strong>Trạng thái lưu kho:</strong> Bánh kẹo lỗi hỏng phải được cách ly và quản lý chặt chẽ trong kho (BR-09).</li>
                <li><strong>Thẩm quyền phê duyệt:</strong> Mọi phương án tiêu hủy hoặc thanh lý hàng hóa đều thuộc thẩm quyền phê duyệt của Ban Giám Đốc (BR-01). Bước này chỉ ghi nhận đề xuất ở trạng thái <code>CHO_XU_LY</code>.</li>
            </ul>
        </div>

        <!-- STICKY ACTIONS BAR -->
        <div class="sticky-actions">
            <div class="sticky-actions-inner space-between">
                <button type="button" class="btn btn-ghost" onclick="confirmCancel()">
                    <i class="bi bi-x-circle"></i> Hủy bỏ (Esc)
                </button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary" onclick="submitForm('calculate')">
                        <i class="bi bi-calculator"></i> Tổng hợp giá trị
                    </button>
                    <button type="button" class="btn btn-primary" onclick="submitForm('save')">
                        <i class="bi bi-check-lg"></i> Lưu đề xuất (Ctrl + S)
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal xác nhận Hủy bỏ -->
<div id="cancelModal" class="modal-backdrop" style="display: none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title"><i class="bi bi-exclamation-circle text-warning"></i> Xác nhận hủy thao tác</h3>
            <button type="button" class="btn-close" onclick="closeCancelModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p><?= \App\Config\Messages::HL_CONFIRM_CANCEL ?></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeCancelModal()">Tiếp tục soạn thảo</button>
            <a href="?route=hang-loi/index" class="btn btn-danger">Đồng ý hủy</a>
        </div>
    </div>
</div>

<script>
function toggleItem(checkbox) {
    const id = checkbox.getAttribute('data-id');
    const input = document.getElementById('qty_' + id);
    if (checkbox.checked) {
        input.disabled = false;
        if (!input.value || input.value === '0') {
            input.value = 1;
        }
    } else {
        input.disabled = true;
    }
    calcRow(id);
    updateTotals();
}

function calcRow(id) {
    const checkbox = document.querySelector('input[data-id="' + id + '"]');
    const input = document.getElementById('qty_' + id);
    const row = checkbox.closest('tr');
    const priceEl = row.querySelector('.item-price');
    const price = parseFloat(priceEl.getAttribute('data-price') || 0);
    const totalEl = document.getElementById('row_total_' + id);

    if (checkbox.checked && input.value) {
        const qty = parseFloat(input.value) || 0;
        const total = qty * price;
        totalEl.innerText = total.toLocaleString('vi-VN') + ' đ';
    } else {
        totalEl.innerText = '0 đ';
    }
    updateTotals();
}

function updateTotals() {
    let count = 0;
    let totalQty = 0;
    let totalPrice = 0;

    const checkboxes = document.querySelectorAll('.item-checkbox:checked');
    checkboxes.forEach(cb => {
        count++;
        const id = cb.getAttribute('data-id');
        const input = document.getElementById('qty_' + id);
        const row = cb.closest('tr');
        const price = parseFloat(row.querySelector('.item-price').getAttribute('data-price') || 0);
        const qty = parseFloat(input.value) || 0;
        totalQty += qty;
        totalPrice += (qty * price);
    });

    document.getElementById('kpiSelectedCount').innerText = count;
    document.getElementById('kpiTotalQty').innerText = totalQty.toLocaleString('vi-VN');
    document.getElementById('kpiTotalPrice').innerText = totalPrice.toLocaleString('vi-VN') + ' đ';
    document.getElementById('selectedCountText').innerText = 'Đã chọn ' + count + ' mặt hàng';
}

function submitForm(action) {
    document.getElementById('actionInput').value = action;
    document.getElementById('proposalForm').submit();
}

function confirmCancel() {
    document.getElementById('cancelModal').style.display = 'flex';
}

function closeCancelModal() {
    document.getElementById('cancelModal').style.display = 'none';
}

// Lọc nhanh bảng mặt hàng
document.getElementById('filterItemInput').addEventListener('input', function() {
    const kw = this.value.toLowerCase().trim();
    document.querySelectorAll('.item-row').forEach(row => {
        const text = row.getAttribute('data-search') || '';
        row.style.display = text.includes(kw) ? '' : 'none';
    });
});

// Phím tắt Ctrl + S và Esc
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        confirmCancel();
    }
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        submitForm('save');
    }
});

// Chạy khởi tạo tổng tính ngay khi nạp trang
document.addEventListener('DOMContentLoaded', function() {
    updateTotals();
});
</script>

