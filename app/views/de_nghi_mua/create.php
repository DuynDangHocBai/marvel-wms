<?php
/**
 * View: Lập đề nghị mua NVL mới (Mẫu 03: Form lập chứng từ)
 * @var array $approvedPlans Danh sách kế hoạch đã duyệt
 * @var array $allMaterials Danh sách tất cả NVL (để thêm ngoài kế hoạch)
 * @var string $selectedPlanId Mã kế hoạch đang chọn
 * @var array|null $planDetail Chi tiết kế hoạch
 * @var array $calculatedItems Danh sách NVL tính toán theo công thức
 * @var array $formData Dữ liệu form submit lại nếu lỗi
 * @var array $currentUser Thông tin người dùng đăng nhập
 */
use App\Core\Csrf;

$ngayCanHang = $formData['ngayCanHang'] ?? date('Y-m-d', strtotime('+7 days'));
$lyDo = $formData['lyDo'] ?? '';
$displayItems = !empty($formData['items']) ? $formData['items'] : $calculatedItems;
?>

<style>
#proposalTable th {
    padding: 12px 8px;
    font-size: 11px;
    white-space: nowrap;
}
#proposalTable td {
    padding: 10px 8px;
    font-size: 13px;
    vertical-align: middle;
}
#proposalTable input.qty-input {
    min-height: 36px;
    height: 36px;
    padding: 4px 8px;
    width: 92px;
    min-width: 90px;
    display: inline-block;
    font-weight: 600;
    font-size: 13px;
}
</style>

<div class="page-container">
    <div class="page-header-box">
        <div>
            <div class="breadcrumb-nav">
                <a href="?route=de-nghi-mua/index">Kế hoạch</a>
                <span class="sep">›</span>
                <a href="?route=de-nghi-mua/index">Đề nghị mua NVL</a>
                <span class="sep">›</span>
                <span class="current">Lập đề nghị mua mới</span>
            </div>
            <h1 class="page-title">Lập đề nghị mua nguyên vật liệu</h1>
            <p class="page-subtitle">Tính toán nhu cầu mua bổ sung theo công thức cân đối tồn khả dụng, tồn tối thiểu và hàng đang về</p>
        </div>
        <div class="header-actions">
            <span class="badge badge-ongoing">ĐANG SOẠN THẢO</span>
        </div>
    </div>

    <form id="proposalForm" method="POST" action="?route=de-nghi-mua/create">
        <?= Csrf::field() ?>

        <!-- THẺ 1: THÔNG TIN ĐỀ NGHỊ MUA -->
        <div class="card mb-4">
            <div class="card-head">
                <div class="card-head-title"><i class="bi bi-info-circle"></i> 1. Thông tin chung đề nghị mua</div>
            </div>
            <div class="card-body">
                <div class="form-grid grid-3">
                    <div class="form-group">
                        <label class="form-label font-weight-bold">Kế hoạch sản xuất <span class="text-danger">*</span></label>
                        <select name="maKeHoach" id="planSelect" class="input" onchange="onPlanSelectChange(this.value)">
                            <option value="">-- Chọn kế hoạch đã duyệt (hoặc ngoài kế hoạch) --</option>
                            <?php foreach ($approvedPlans as $p): ?>
                                <option value="<?= htmlspecialchars($p['maKeHoach']) ?>" <?= $selectedPlanId === $p['maKeHoach'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['maKeHoach']) ?> - Xưởng: <?= htmlspecialchars($p['tenXuong'] ?? $p['maXuong']) ?> (Bắt đầu: <?= date('d/m', strtotime($p['ngayBatDau'])) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label text-muted">Người lập đề nghị (Khóa)</label>
                        <input type="text" class="input bg-light" value="<?= htmlspecialchars(($currentUser['hoTen'] ?? 'Lập kế hoạch') . ' (' . ($currentUser['chucDanh'] ?? 'LKH') . ')') ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label font-weight-bold">Ngày cần hàng <span class="text-danger">*</span></label>
                        <input type="date" name="ngayCanHang" class="input" value="<?= htmlspecialchars($ngayCanHang) ?>" min="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <label class="form-label">Lý do đề nghị mua</label>
                    <textarea name="lyDo" class="input" rows="2" placeholder="Ghi chú mục đích mua bổ sung, phục vụ đơn hàng gấp..."><?= htmlspecialchars($lyDo) ?></textarea>
                </div>
            </div>
        </div>

        <!-- THẺ 2: DANH SÁCH NGUYÊN VẬT LIỆU ĐỀ NGHỊ -->
        <div class="card mb-4">
            <div class="card-head space-between">
                <div class="card-head-title">
                    <i class="bi bi-cart3"></i> 2. Danh sách nguyên vật liệu đề nghị mua
                    <span class="badge badge-draft ml-2" id="itemCountBadge"><?= count($displayItems) ?> mặt hàng</span>
                </div>
                <div>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="openAddNonPlanModal()">
                        <i class="bi bi-plus-lg"></i> Thêm NVL ngoài kế hoạch
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <!-- Khung cuộn ngang độc lập để bảng không tràn thẻ card -->
                <div class="table-wrapper">
                    <table class="table mb-0" id="proposalTable" style="min-width: 920px; width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 85px;">Mã SKU</th>
                                <th style="min-width: 160px;">Tên nguyên vật liệu</th>
                                <th class="text-right" style="width: 75px;">Nhu cầu SX</th>
                                <th class="text-right" style="width: 75px;">Tồn khả dụng</th>
                                <th class="text-right" style="width: 70px;">Tồn tối thiểu</th>
                                <th class="text-right" style="width: 65px;">Đang về</th>
                                <th class="text-right font-weight-bold text-info" style="width: 75px;">SL Đề xuất</th>
                                <th class="text-right font-weight-bold" style="width: 105px;">SL Mua <span class="text-danger">*</span></th>
                                <th class="text-right" style="width: 85px;">Đơn giá</th>
                                <th class="text-right font-weight-bold" style="width: 110px;">Thành tiền</th>
                                <th class="text-center col-actions" style="width: 40px;"></th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <?php if (empty($displayItems)): ?>
                                <tr id="emptyRow">
                                    <td colspan="11" class="text-center py-4 text-muted">
                                        Vui lòng chọn Kế hoạch sản xuất ở trên để hệ thống tự động tính toán nhu cầu nguyên vật liệu theo định mức BOM.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                    $tongTienAll = 0.0;
                                    foreach ($displayItems as $idx => $it): 
                                        $sku = $it['maSKU'];
                                        $slDeXuat = (float)($it['soLuongDeXuat'] ?? 0);
                                        // Mặc định SL Mua = SL Đề xuất (5, 16, 38 với KHSX004)
                                        $slMua = isset($it['soLuongMua']) ? (float)$it['soLuongMua'] : $slDeXuat;
                                        $price = (float)($it['giaNhap'] ?? 0);
                                        $lineTotal = $slMua * $price;
                                        $tongTienAll += $lineTotal;
                                ?>
                                    <tr id="row_<?= htmlspecialchars($sku) ?>">
                                        <td>
                                            <code><?= htmlspecialchars($sku) ?></code>
                                            <input type="hidden" name="items[<?= $idx ?>][maSKU]" value="<?= htmlspecialchars($sku) ?>">
                                            <input type="hidden" name="items[<?= $idx ?>][tenHang]" value="<?= htmlspecialchars($it['tenHang'] ?? '') ?>">
                                            <input type="hidden" name="items[<?= $idx ?>][donViTinh]" value="<?= htmlspecialchars($it['donViTinh'] ?? 'kg') ?>">
                                            <input type="hidden" name="items[<?= $idx ?>][nhuCauSX]" value="<?= htmlspecialchars($it['nhuCauSX'] ?? 0) ?>">
                                            <input type="hidden" name="items[<?= $idx ?>][tonKhaDung]" value="<?= htmlspecialchars($it['tonKhaDung'] ?? 0) ?>">
                                            <input type="hidden" name="items[<?= $idx ?>][tonToiThieu]" value="<?= htmlspecialchars($it['tonToiThieu'] ?? 0) ?>">
                                            <input type="hidden" name="items[<?= $idx ?>][hangDangVe]" value="<?= htmlspecialchars($it['hangDangVe'] ?? 0) ?>">
                                            <input type="hidden" name="items[<?= $idx ?>][soLuongDeXuat]" value="<?= htmlspecialchars($slDeXuat) ?>">
                                            <input type="hidden" name="items[<?= $idx ?>][giaNhap]" value="<?= htmlspecialchars($price) ?>">
                                            <input type="hidden" name="items[<?= $idx ?>][ngoaiKeHoach]" value="<?= !empty($it['ngoaiKeHoach']) ? 1 : 0 ?>">
                                            <?php if (!empty($it['ngoaiKeHoach'])): ?>
                                                <input type="hidden" name="items[<?= $idx ?>][lyDoRieng]" value="<?= htmlspecialchars($it['lyDoRieng'] ?? '') ?>">
                                                <span class="badge badge-warning d-block mt-1">Ngoài KH</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="font-weight-bold" style="line-height: 1.3;"><?= htmlspecialchars($it['tenHang'] ?? '') ?></div>
                                            <small class="text-muted">ĐVT: <?= htmlspecialchars($it['donViTinh'] ?? 'kg') ?></small>
                                            <?php if ($slDeXuat <= 0 || !empty($it['canhBaoTonDu'])): ?>
                                                <div class="alert alert-warning py-1 px-2 mt-1 mb-0" style="font-size: 11px; line-height: 1.3;">
                                                    <i class="bi bi-exclamation-triangle"></i> <?= \App\Config\Messages::DNM_EXC_5_1_NOT_NEEDED ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-right"><?= formatSo($it['nhuCauSX'] ?? 0) ?></td>
                                        <td class="text-right"><?= formatSo($it['tonKhaDung'] ?? 0) ?></td>
                                        <td class="text-right"><?= formatSo($it['tonToiThieu'] ?? 0) ?></td>
                                        <td class="text-right"><?= formatSo($it['hangDangVe'] ?? 0) ?></td>
                                        <td class="text-right font-weight-bold text-info">
                                            <?= formatSo($slDeXuat) ?>
                                        </td>
                                        <td class="text-right">
                                            <input type="number" 
                                                   name="items[<?= $idx ?>][soLuongMua]" 
                                                   class="input text-right qty-input" 
                                                   min="0.01" 
                                                   step="0.01" 
                                                   value="<?= htmlspecialchars((string)$slMua) ?>" 
                                                   data-price="<?= $price ?>" 
                                                   oninput="recalcTotals()" 
                                                   required>
                                        </td>
                                        <td class="text-right text-muted"><?= formatSo($price, true) ?></td>
                                        <td class="text-right font-weight-bold text-success line-total"><?= formatSo($lineTotal, true) ?></td>
                                        <td class="text-center col-actions">
                                            <button type="button" class="btn btn-sm btn-link text-danger p-0" title="Xóa dòng" onclick="removeRow('row_<?= htmlspecialchars($sku) ?>')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr class="bg-light font-weight-bold">
                                <td colspan="8" class="text-right py-3" style="font-size: 0.95rem;">TỔNG TIỀN DỰ TOÁN MUA HÀNG:</td>
                                <td colspan="2" class="text-right text-primary py-3" id="totalAmountText" style="font-size: 1.15rem; color: #1e40af;">
                                    <?= isset($tongTienAll) ? formatSo($tongTienAll, true) : '0 đ' ?>
                                </td>
                                <td class="col-actions"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- THẺ 3: KIỂM TRA HỢP LỆ 4 TIÊU CHÍ -->
        <div class="card mb-4" style="border-left: 4px solid var(--info-color);">
            <div class="card-head">
                <div class="card-head-title"><i class="bi bi-shield-check"></i> 3. Tiêu chuẩn thẩm định đề nghị mua</div>
            </div>
            <div class="card-body">
                <div class="form-grid grid-2">
                    <div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-check-circle-fill text-success mr-2"></i>
                            <span>Công thức tính: <code>SL đề xuất = max(0, Nhu cầu SX + Tồn tối thiểu - Tồn khả dụng - Đang về)</code></span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success mr-2"></i>
                            <span>Ngày cần hàng phải lớn hơn hoặc bằng ngày hiện tại</span>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-check-circle-fill text-success mr-2"></i>
                            <span>Số lượng mua mỗi dòng phải lớn hơn 0 (SL mua &gt; 0)</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success mr-2"></i>
                            <span>Mặt hàng ngoài kế hoạch phải có lý do giải trình</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STICKY ACTION BAR -->
        <div class="sticky-action-bar">
            <div class="action-bar-left">
                <a href="?route=de-nghi-mua/index" class="btn btn-light" onclick="return confirmCancel(event)">
                    <i class="bi bi-x-circle"></i> Hủy bỏ (Esc)
                </a>
            </div>
            <div class="action-bar-right">
                <button type="submit" class="btn btn-primary" onclick="return confirmSubmit()">
                    <i class="bi bi-send-check"></i> Lưu đề nghị mua (Ctrl+S)
                </button>
            </div>
        </div>
    </form>
</div>

<script>
// Bản đồ dữ liệu danh mục NVL để tra cứu đơn giá & ĐVT khi thêm ngoài kế hoạch
const materialsMap = <?= json_encode(array_column($allMaterials, null, 'maSKU'), JSON_UNESCAPED_UNICODE) ?>;

function onPlanSelectChange(planId) {
    if (planId) {
        window.location.href = '?route=de-nghi-mua/create&plan_id=' + encodeURIComponent(planId);
    }
}

function recalcTotals() {
    var inputs = document.querySelectorAll('.qty-input');
    var grandTotal = 0;
    inputs.forEach(function(inp) {
        var qty = parseFloat(inp.value) || 0;
        var price = parseFloat(inp.getAttribute('data-price')) || 0;
        var total = qty * price;
        var row = inp.closest('tr');
        if (row) {
            var lineTotCell = row.querySelector('.line-total');
            if (lineTotCell) {
                lineTotCell.innerText = Math.round(total).toLocaleString('vi-VN') + ' đ';
            }
        }
        grandTotal += total;
    });
    var totalText = document.getElementById('totalAmountText');
    if (totalText) {
        totalText.innerText = Math.round(grandTotal).toLocaleString('vi-VN') + ' đ';
    }
    updateBadgeCount();
}

function updateBadgeCount() {
    var rows = document.querySelectorAll('#tableBody tr:not(#emptyRow)');
    var badge = document.getElementById('itemCountBadge');
    if (badge) {
        badge.innerText = rows.length + ' mặt hàng';
    }
}

function removeRow(rowId) {
    var r = document.getElementById(rowId);
    if (r) {
        r.remove();
        recalcTotals();
        var rows = document.querySelectorAll('#tableBody tr:not(#emptyRow)');
        if (rows.length === 0) {
            var tbody = document.getElementById('tableBody');
            tbody.innerHTML = '<tr id="emptyRow"><td colspan="11" class="text-center py-4 text-muted">Danh sách mặt hàng đang trống. Vui lòng chọn Kế hoạch hoặc thêm mặt hàng.</td></tr>';
        }
    }
}

function openAddNonPlanModal() {
    var sku = prompt('Nhập mã SKU nguyên vật liệu ngoài kế hoạch (vd: NVL002, NVL003, NVL007):');
    if (!sku) return;
    sku = sku.trim().toUpperCase();

    var mat = materialsMap[sku] || null;
    var defaultName = mat ? mat.tenHang : (sku + ' (Ngoài kế hoạch)');
    var dvt = mat ? mat.donViTinh : 'kg';
    var price = mat ? (parseFloat(mat.giaNhap) || 0) : 0;

    var qtyStr = prompt('Nhập số lượng mua đề xuất cho ' + sku + ' (' + defaultName + '):', '10');
    if (!qtyStr) return;
    var qty = parseFloat(qtyStr);
    if (isNaN(qty) || qty <= 0) {
        alert('Số lượng mua phải là số lớn hơn 0.');
        return;
    }

    var reason = prompt('Nhập lý do giải trình khi đề xuất NVL ngoài kế hoạch:');
    if (!reason || !reason.trim()) {
        alert('Vui lòng nhập lý do giải trình khi đề xuất mua NVL ngoài kế hoạch.');
        return;
    }

    if (document.getElementById('row_' + sku)) {
        alert('Mặt hàng ' + sku + ' đã có trong danh sách!');
        return;
    }

    var tbody = document.getElementById('tableBody');
    var emptyRow = document.getElementById('emptyRow');
    if (emptyRow) emptyRow.remove();

    var idx = document.querySelectorAll('#tableBody tr').length + 100;
    var lineTotal = qty * price;

    var tr = document.createElement('tr');
    tr.id = 'row_' + sku;
    tr.innerHTML = '<td><code>' + sku + '</code><span class="badge badge-warning d-block mt-1">Ngoài KH</span>' +
        '<input type="hidden" name="items[' + idx + '][maSKU]" value="' + sku + '">' +
        '<input type="hidden" name="items[' + idx + '][tenHang]" value="' + defaultName.replace(/"/g, '&quot;') + '">' +
        '<input type="hidden" name="items[' + idx + '][donViTinh]" value="' + dvt + '">' +
        '<input type="hidden" name="items[' + idx + '][nhuCauSX]" value="0">' +
        '<input type="hidden" name="items[' + idx + '][tonKhaDung]" value="' + (mat ? mat.tonKhaDung : 0) + '">' +
        '<input type="hidden" name="items[' + idx + '][tonToiThieu]" value="' + (mat ? mat.tonToiThieu : 0) + '">' +
        '<input type="hidden" name="items[' + idx + '][hangDangVe]" value="' + (mat ? mat.hangDangVe : 0) + '">' +
        '<input type="hidden" name="items[' + idx + '][soLuongDeXuat]" value="' + qty + '">' +
        '<input type="hidden" name="items[' + idx + '][giaNhap]" value="' + price + '">' +
        '<input type="hidden" name="items[' + idx + '][ngoaiKeHoach]" value="1">' +
        '<input type="hidden" name="items[' + idx + '][lyDoRieng]" value="' + reason.replace(/"/g, '&quot;') + '"></td>' +
        '<td><div class="font-weight-bold" style="line-height: 1.3;">' + defaultName + '</div><small class="text-muted">ĐVT: ' + dvt + '</small></td>' +
        '<td class="text-right">0.00</td><td class="text-right">' + (mat ? parseFloat(mat.tonKhaDung).toFixed(2) : '0.00') + '</td>' +
        '<td class="text-right">' + (mat ? parseFloat(mat.tonToiThieu).toFixed(2) : '0.00') + '</td><td class="text-right">' + (mat ? parseFloat(mat.hangDangVe).toFixed(2) : '0.00') + '</td>' +
        '<td class="text-right font-weight-bold text-info">' + qty.toFixed(2) + '</td>' +
        '<td class="text-right"><input type="number" min="0.01" step="0.01" name="items[' + idx + '][soLuongMua]" class="input text-right qty-input" value="' + qty + '" data-price="' + price + '" oninput="recalcTotals()" required></td>' +
        '<td class="text-right text-muted">' + Math.round(price).toLocaleString('vi-VN') + ' đ</td>' +
        '<td class="text-right font-weight-bold text-success line-total">' + Math.round(lineTotal).toLocaleString('vi-VN') + ' đ</td>' +
        '<td class="text-center"><button type="button" class="btn btn-sm btn-link text-danger p-0" title="Xóa dòng" onclick="removeRow(\'row_' + sku + '\')"><i class="bi bi-trash"></i></button></td>';

    tbody.appendChild(tr);
    recalcTotals();
}

function confirmSubmit() {
    var dateInput = document.querySelector('input[name="ngayCanHang"]');
    if (!dateInput || !dateInput.value) {
        alert('<?= \App\Config\Messages::DNM_EXC_8_1_INVALID ?>');
        if (dateInput) dateInput.focus();
        return false;
    }
    var rows = document.querySelectorAll('#tableBody tr:not(#emptyRow)');
    if (rows.length === 0) {
        alert('<?= \App\Config\Messages::DNM_EXC_8_1_INVALID ?>');
        return false;
    }
    var inputs = document.querySelectorAll('.qty-input');
    for (var i = 0; i < inputs.length; i++) {
        var val = parseFloat(inputs[i].value);
        if (isNaN(val) || val <= 0) {
            alert('<?= \App\Config\Messages::DNM_EXC_8_1_INVALID ?>');
            inputs[i].focus();
            return false;
        }
    }
    return confirm('Xác nhận lập đề nghị mua nguyên vật liệu và gửi phê duyệt?');
}

function confirmCancel(e) {
    if (!confirm('<?= \App\Config\Messages::DNM_CONFIRM_CANCEL ?>')) {
        e.preventDefault();
        return false;
    }
    return true;
}

// Phím tắt Ctrl+S và Esc
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        if (confirmSubmit()) {
            document.getElementById('proposalForm').submit();
        }
    }
    if (e.key === 'Escape') {
        if (confirm('<?= \App\Config\Messages::DNM_CONFIRM_CANCEL ?>')) {
            window.location.href = '?route=de-nghi-mua/index';
        }
    }
});
</script>
