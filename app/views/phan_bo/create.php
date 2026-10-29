<?php
/**
 * View: Lập phiếu phân bổ số lượng tồn kho (Mẫu 04: Wizard 4 bước)
 * @var array $pendingOrders Danh sách lệnh chờ phân bổ
 * @var array $warehouses Danh sách kho
 * @var string $selectedOrder Mã lệnh đang chọn
 * @var array|null $orderHeader Header lệnh đang chọn
 * @var array $orderNeeds Danh sách NVL cần cấp của lệnh
 * @var string $selectedKho Mã kho đang chọn
 * @var array $availableLotsBySku Các lô khả dụng theo SKU trong kho
 * @var array $suggestedAlloc Danh sách gợi ý phân bổ
 * @var string $ghiChu Ghi chú
 * @var int $currentStep Bước hiện tại (1..4)
 */
use App\Core\Csrf;

$step = $currentStep ?? 1;

// Biến kiểm tra map giá trị gợi ý hoặc nhập cũ
$allocMap = [];
if (!empty($suggestedAlloc)) {
    foreach ($suggestedAlloc as $it) {
        $k = ($it['maLo'] ?? '') . '__' . ($it['maViTri'] ?? '');
        $allocMap[$k] = (float)($it['soLuongPhanBo'] ?? 0);
    }
}
?>

<div class="page-container">
    <div class="page-header-box">
        <div>
            <div class="breadcrumb-nav">
                <a href="?route=phan-bo/index">Kho vận</a>
                <span class="sep">›</span>
                <a href="?route=phan-bo/index">Phân bổ số lượng</a>
                <span class="sep">›</span>
                <span class="current">Lập phiếu phân bổ</span>
            </div>
            <h1 class="page-title">Phân bổ tồn kho theo lô & vị trí (FEFO)</h1>
            <p class="page-subtitle">Thực hiện giữ chỗ NVL cấp lệnh theo chuẩn FEFO (Hạn dùng sớm xuất trước, loại trừ hàng cách ly/lỗi)</p>
        </div>
        <div class="header-actions">
            <span class="badge badge-ongoing">WIZARD PHÂN BỔ</span>
        </div>
    </div>

    <!-- WIZARD STEPPER -->
    <div class="wizard-stepper mb-4">
        <div class="step-card <?= $step === 1 ? 'active' : ($step > 1 ? 'completed' : '') ?>" onclick="goToStep(1)">
            <div class="step-num"><?= $step > 1 ? '<i class="bi bi-check-lg"></i>' : '1' ?></div>
            <div class="step-text">
                <div class="step-title">Bước 1: Nơi nhận</div>
                <div class="step-desc">Chọn lệnh sản xuất</div>
            </div>
        </div>
        <div class="step-card <?= $step === 2 ? 'active' : ($step > 2 ? 'completed' : '') ?>" onclick="goToStep(2)">
            <div class="step-num"><?= $step > 2 ? '<i class="bi bi-check-lg"></i>' : '2' ?></div>
            <div class="step-text">
                <div class="step-title">Bước 2: Kho nguồn</div>
                <div class="step-desc">Chọn kho xuất NVL</div>
            </div>
        </div>
        <div class="step-card <?= $step === 3 ? 'active' : ($step > 3 ? 'completed' : '') ?>" onclick="goToStep(3)">
            <div class="step-num"><?= $step > 3 ? '<i class="bi bi-check-lg"></i>' : '3' ?></div>
            <div class="step-text">
                <div class="step-title">Bước 3: Chọn lô & vị trí</div>
                <div class="step-desc">Gợi ý phân bổ FEFO</div>
            </div>
        </div>
        <div class="step-card <?= $step === 4 ? 'active' : '' ?>" onclick="goToStep(4)">
            <div class="step-num">4</div>
            <div class="step-text">
                <div class="step-title">Bước 4: Xác nhận</div>
                <div class="step-desc">Sơ đồ luồng & Hoàn tất</div>
            </div>
        </div>
    </div>

    <form id="allocationForm" method="POST" action="?route=phan-bo/create">
        <?= Csrf::field() ?>
        <input type="hidden" name="form_action" id="formActionInput" value="save">
        <input type="hidden" name="maLenhSX" id="hiddenOrderInput" value="<?= htmlspecialchars($selectedOrder) ?>">
        <input type="hidden" name="maKhoNguon" id="hiddenKhoInput" value="<?= htmlspecialchars($selectedKho) ?>">

        <!-- BƯỚC 1: CHỌN NƠI NHẬN (LỆNH SẢN XUẤT) -->
        <div class="card mb-4 wizard-step-panel" id="panelStep1" style="<?= $step !== 1 ? 'display: none;' : '' ?>">
            <div class="card-head">
                <div class="card-head-title"><i class="bi bi-journal-text"></i> Bước 1: Chọn nơi nhận (Lệnh sản xuất chờ phân bổ)</div>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Chọn lệnh sản xuất đang ở trạng thái <span class="badge badge-pending">DA_PHAT</span> hoặc <span class="badge badge-ongoing">DANG_PHAN_BO</span> cần cấp phát nguyên vật liệu.</p>

                <div class="form-group mb-4" style="max-width: 500px;">
                    <label class="form-label font-weight-bold">Chọn lệnh sản xuất <span class="text-danger">*</span></label>
                    <select id="orderSelect" class="input" onchange="onOrderSelectChange(this.value)">
                        <option value="">-- Chọn lệnh sản xuất --</option>
                        <?php foreach ($pendingOrders as $p): ?>
                            <option value="<?= htmlspecialchars($p['maLenhSX']) ?>" <?= $selectedOrder === $p['maLenhSX'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['maLenhSX']) ?> - <?= htmlspecialchars($p['maKeHoach']) ?> (Xưởng: <?= htmlspecialchars($p['tenXuong'] ?? $p['maXuong']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if (!empty($orderNeeds)): ?>
                    <h6 class="font-weight-bold mt-4 mb-2">Nguyên vật liệu cần cấp cho lệnh <?= htmlspecialchars($selectedOrder) ?>:</h6>
                    <div class="table-wrapper">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="width: 120px;">Mã NVL</th>
                                    <th class="col-text-long">Tên nguyên vật liệu</th>
                                    <th>ĐVT</th>
                                    <th class="text-right">Tổng cần cấp (BOM)</th>
                                    <th class="text-right">Đã phân bổ</th>
                                    <th class="text-right">Còn thiếu</th>
                                    <th class="text-center" style="width: 140px;">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orderNeeds as $nvl): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($nvl['maSKU']) ?></code></td>
                                        <td class="font-weight-bold col-text-long"><?= htmlspecialchars($nvl['tenHang']) ?></td>
                                        <td><?= htmlspecialchars($nvl['donViTinh']) ?></td>
                                        <td class="text-right"><?= formatSo($nvl['soLuongCanCap']) ?></td>
                                        <td class="text-right text-success"><?= formatSo($nvl['soLuongDaPhanBo']) ?></td>
                                        <td class="text-right font-weight-bold <?= $nvl['soLuongConLai'] > 0 ? 'text-danger' : 'text-muted' ?>">
                                            <?= formatSo($nvl['soLuongConLai']) ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($nvl['trangThai'] === 'DA_PHAN_BO_DU'): ?>
                                                <span class="badge badge-passed">Đã phân bổ đủ</span>
                                            <?php elseif ($nvl['trangThai'] === 'DANG_PHAN_BO'): ?>
                                                <span class="badge badge-ongoing">Đang phân bổ</span>
                                            <?php else: ?>
                                                <span class="badge badge-pending">Chờ phân bổ</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-right mt-3">
                        <button type="button" class="btn btn-primary" onclick="goToStep(2)">
                            Tiếp tục: Chọn kho nguồn <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- BƯỚC 2: CHỌN KHO NGUỒN XUẤT HÀNG -->
        <div class="card mb-4 wizard-step-panel" id="panelStep2" style="<?= $step !== 2 ? 'display: none;' : '' ?>">
            <div class="card-head">
                <div class="card-head-title"><i class="bi bi-building"></i> Bước 2: Chọn Kho nguồn xuất nguyên vật liệu</div>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Chọn kho lưu trữ đang có sẵn các nguyên vật liệu phục vụ sản xuất:</p>

                <div class="grid-3 mb-4">
                    <?php foreach ($warehouses as $wh): ?>
                        <?php 
                            $isWhSelected = ($selectedKho === $wh['maKho']);
                            $pctUsed = ($wh['sucChuaToiDa'] > 0) ? round(($wh['sucChuaDaSuDung'] / $wh['sucChuaToiDa']) * 100, 1) : 0;
                        ?>
                        <div class="card p-3 cursor-pointer <?= $isWhSelected ? 'border-primary' : '' ?>" 
                             style="cursor: pointer; border-width: 2px; transition: all 0.2s;"
                             onclick="onKhoSelectChange('<?= htmlspecialchars($wh['maKho']) ?>')">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="font-weight-bold mb-0 text-primary">
                                    <i class="bi bi-box-seam mr-1"></i> <?= htmlspecialchars($wh['tenKho']) ?>
                                </h6>
                                <span class="badge <?= $isWhSelected ? 'badge-primary' : 'badge-light' ?>">
                                    <?= htmlspecialchars($wh['maKho']) ?>
                                </span>
                            </div>
                            <div class="text-muted small mb-2"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($wh['diaDiem']) ?></div>
                            <div class="progress mb-1" style="height: 6px;">
                                <div class="progress-bar bg-info" style="width: <?= min(100, $pctUsed) ?>%;"></div>
                            </div>
                            <div class="d-flex justify-content-between text-muted small">
                                <span>Đã dùng: <?= number_format($wh['sucChuaDaSuDung']) ?></span>
                                <span><?= $pctUsed ?>%</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-secondary" onclick="goToStep(1)">
                        <i class="bi bi-arrow-left"></i> Quay lại Bước 1
                    </button>
                    <?php if (!empty($selectedKho)): ?>
                        <button type="button" class="btn btn-primary" onclick="goToStep(3)">
                            Tiếp tục: Chọn lô & vị trí (FEFO) <i class="bi bi-arrow-right"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- BƯỚC 3: CHỌN LÔ & VỊ TRÍ THEO NGUYÊN TẮC FEFO -->
        <div class="card mb-4 wizard-step-panel" id="panelStep3" style="<?= $step !== 3 ? 'display: none;' : '' ?>">
            <div class="card-head space-between">
                <div class="card-head-title">
                    <i class="bi bi-sort-numeric-down"></i> Bước 3: Chọn Lô & Vị trí lưu kho (Ưu tiên FEFO)
                </div>
                <div>
                    <button type="button" class="btn btn-info btn-sm" onclick="triggerSuggestFEFO()">
                        <i class="bi bi-magic"></i> Gợi ý tự động (FEFO)
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-4">
                    <i class="bi bi-lightbulb mr-1"></i>
                    <strong>Nguyên tắc FEFO (First Expired, First Out):</strong> Các lô có ngày hết hạn sớm nhất sẽ được hệ thống xếp lên đầu và ưu tiên xuất trước. Lô hàng cách ly hoặc hết hạn tự động bị ẩn (BR-02, BR-09).
                </div>

                <?php 
                    $rowIdx = 0;
                    $hasAnyLot = false;
                ?>

                <?php if (!empty($orderNeeds)): ?>
                    <?php foreach ($orderNeeds as $nvl): ?>
                        <?php 
                            $sku = $nvl['maSKU'];
                            $remainNeed = (float)$nvl['soLuongConLai'];
                            if ($remainNeed <= 0) continue;
                            $lots = $availableLotsBySku[$sku] ?? [];
                            if (!empty($lots)) $hasAnyLot = true;
                        ?>
                        <div class="card mb-4 border">
                            <div class="card-head space-between py-2" style="background-color: var(--bg-hover);">
                                <div>
                                    <strong class="text-primary"><?= htmlspecialchars($sku) ?> - <?= htmlspecialchars($nvl['tenHang']) ?></strong>
                                    <span class="text-muted ml-2">(ĐVT: <?= htmlspecialchars($nvl['donViTinh']) ?>)</span>
                                </div>
                                <div>
                                    <span class="badge badge-ongoing">Cần cấp còn lại: <?= number_format($remainNeed, 2) ?> <?= htmlspecialchars($nvl['donViTinh']) ?></span>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-wrapper">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th style="width: 140px;">Mã Lô</th>
                                                <th>Vị trí lưu kho</th>
                                                <th>Hạn sử dụng</th>
                                                <th class="text-right">Tồn thực tế</th>
                                                <th class="text-right">Đang giữ chỗ</th>
                                                <th class="text-right">Tồn khả dụng</th>
                                                <th class="text-right" style="width: 140px;">SL phân bổ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($lots)): ?>
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted py-3">
                                                        <i class="bi bi-exclamation-circle text-warning"></i> Không có lô hàng khả dụng nào trong kho này.
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($lots as $lot): ?>
                                                    <?php 
                                                        $rowIdx++;
                                                        $loKey = $lot['maLo'] . '__' . $lot['maViTri'];
                                                        $val = $allocMap[$loKey] ?? '';
                                                        $daysLeft = (int)$lot['soNgayConLai'];
                                                        $isExpSoon = ($daysLeft <= 7);
                                                    ?>
                                                    <tr class="<?= $isExpSoon ? 'table-warning-light' : '' ?>">
                                                        <td>
                                                            <strong><?= htmlspecialchars($lot['maLo']) ?></strong>
                                                            <?php if ($isExpSoon): ?>
                                                                <span class="badge badge-warning ml-1" title="Cận date (còn <?= $daysLeft ?> ngày)">
                                                                    <i class="bi bi-clock-history"></i> CẬN DATE
                                                                </span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <span><?= htmlspecialchars($lot['tenViTri']) ?></span>
                                                        </td>
                                                        <td>
                                                            <?= htmlspecialchars($lot['ngayHetHan'] ?? 'Không giới hạn') ?>
                                                            <?php if ($daysLeft > 0): ?>
                                                                <small class="text-muted d-block">(còn <?= $daysLeft ?> ngày)</small>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-right text-muted"><?= formatSo($lot['soLuongThucTe']) ?></td>
                                                        <td class="text-right text-muted"><?= formatSo($lot['soLuongGiuCho']) ?></td>
                                                        <td class="text-right font-weight-bold text-primary">
                                                            <?= formatSo($lot['tonKhaDungLo']) ?>
                                                        </td>
                                                        <td class="text-right">
                                                            <input type="hidden" name="allocations[<?= $rowIdx ?>][maLo]" value="<?= htmlspecialchars($lot['maLo']) ?>">
                                                            <input type="hidden" name="allocations[<?= $rowIdx ?>][maViTri]" value="<?= htmlspecialchars($lot['maViTri']) ?>">
                                                            <input type="hidden" name="allocations[<?= $rowIdx ?>][maSKU]" value="<?= htmlspecialchars($sku) ?>">
                                                            <input type="number" 
                                                                   name="allocations[<?= $rowIdx ?>][soLuongPhanBo]" 
                                                                   class="input text-right lot-alloc-input" 
                                                                   style="width: 100px; min-width: 90px; display: inline-block; font-weight: 600;"
                                                                   min="0" 
                                                                   max="<?= $lot['tonKhaDungLo'] ?>" 
                                                                   step="0.01" 
                                                                   value="<?= htmlspecialchars((string)$val) ?>" 
                                                                   placeholder="0"
                                                                   data-available="<?= $lot['tonKhaDungLo'] ?>"
                                                                   data-sku="<?= htmlspecialchars($sku) ?>">
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="form-group mt-3">
                    <label class="form-label">Ghi chú phiếu phân bổ</label>
                    <input type="text" name="ghiChu" class="input" placeholder="Ghi chú về phân bổ đợt này..." value="<?= htmlspecialchars($ghiChu) ?>">
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-secondary" onclick="goToStep(2)">
                        <i class="bi bi-arrow-left"></i> Quay lại Bước 2
                    </button>
                    <button type="button" class="btn btn-primary" onclick="goToStep(4)">
                        Tiếp tục: Xác nhận & Hoàn tất <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- BƯỚC 4: SƠ ĐỒ LUỒNG & XÁC NHẬN PHÂN BỔ -->
        <div class="card mb-4 wizard-step-panel" id="panelStep4" style="<?= $step !== 4 ? 'display: none;' : '' ?>">
            <div class="card-head">
                <div class="card-head-title"><i class="bi bi-check2-circle"></i> Bước 4: Sơ đồ luồng & Xác nhận phân bổ</div>
            </div>
            <div class="card-body">
                <!-- SƠ ĐỒ LUỒNG PHÂN BỔ -->
                <div class="p-3 bg-light rounded border mb-4">
                    <h6 class="font-weight-bold mb-3 text-primary"><i class="bi bi-diagram-2"></i> Sơ đồ điều phối luồng phân bổ kho:</h6>
                    <div class="d-flex flex-wrap align-items-center justify-content-around text-center py-2">
                        <div class="p-2">
                            <div class="badge badge-ongoing p-2 mb-1"><i class="bi bi-building fs-5 d-block"></i> KHO NGUỒN XUẤT</div>
                            <div class="font-weight-bold"><?= htmlspecialchars($selectedKho) ?></div>
                        </div>
                        <div class="text-muted"><i class="bi bi-arrow-right fs-4"></i></div>
                        <div class="p-2">
                            <div class="badge badge-warning p-2 mb-1"><i class="bi bi-boxes fs-5 d-block"></i> LÔ / VỊ TRÍ (FEFO)</div>
                            <div class="font-weight-bold">Giữ chỗ theo Lô</div>
                        </div>
                        <div class="text-muted"><i class="bi bi-arrow-right fs-4"></i></div>
                        <div class="p-2">
                            <div class="badge badge-success p-2 mb-1"><i class="bi bi-gear-wide-connected fs-5 d-block"></i> LỆNH SẢN XUẤT</div>
                            <div class="font-weight-bold"><?= htmlspecialchars($selectedOrder) ?></div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning mb-4">
                    <h6 class="font-weight-bold mb-1"><i class="bi bi-shield-check"></i> Chuyển đổi cơ chế giữ chỗ (BR-06):</h6>
                    <p class="mb-0">
                        Khi lưu phiếu phân bổ, số lượng giữ chỗ cấp lệnh sẽ được <b>chuyển thành giữ chỗ cấp lô</b> trong bảng <code>ton_kho</code>. Hệ thống sẽ đảm bảo tồn kho khả dụng không bị trừ hai lần và loại bỏ rủi ro tranh chấp tồn giữa các ca kíp.
                    </p>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-secondary" onclick="goToStep(3)">
                        <i class="bi bi-arrow-left"></i> Quay lại Bước 3
                    </button>
                    <button type="button" class="btn btn-primary" onclick="submitAllocation()">
                        <i class="bi bi-check2-circle"></i> Xác nhận & Lưu phiếu phân bổ (Ctrl+S)
                    </button>
                </div>
            </div>
        </div>

        <!-- STICKY ACTION BAR -->
        <div class="sticky-action-bar">
            <div class="action-bar-left">
                <a href="?route=phan-bo/index" class="btn btn-light" onclick="return confirmCancel(event)">
                    <i class="bi bi-x-circle"></i> Hủy bỏ (Esc)
                </a>
            </div>
            <div class="action-bar-right">
                <button type="button" class="btn btn-info" onclick="triggerSuggestFEFO()">
                    <i class="bi bi-magic"></i> Gợi ý tự động
                </button>
                <button type="button" class="btn btn-primary" onclick="submitAllocation()">
                    <i class="bi bi-check2-circle"></i> Lưu phiếu phân bổ (Ctrl+S)
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function onOrderSelectChange(orderId) {
    if (orderId) {
        window.location.href = '?route=phan-bo/create&order_id=' + encodeURIComponent(orderId);
    }
}

function onKhoSelectChange(khoId) {
    var orderId = document.getElementById('hiddenOrderInput').value;
    if (khoId && orderId) {
        window.location.href = '?route=phan-bo/create&order_id=' + encodeURIComponent(orderId) + '&kho_id=' + encodeURIComponent(khoId);
    }
}

function goToStep(stepNumber) {
    document.querySelectorAll('.wizard-step-panel').forEach(function(el) {
        el.style.display = 'none';
    });
    var targetPanel = document.getElementById('panelStep' + stepNumber);
    if (targetPanel) {
        targetPanel.style.display = 'block';
    }

    var stepCards = document.querySelectorAll('.wizard-stepper .step-card');
    stepCards.forEach(function(card, idx) {
        card.classList.remove('active');
        if (idx + 1 === stepNumber) {
            card.classList.add('active');
        }
    });
}

function triggerSuggestFEFO() {
    document.getElementById('formActionInput').value = 'suggest';
    document.getElementById('allocationForm').submit();
}

function submitAllocation() {
    if (confirm('Xác nhận tạo phiếu phân bổ số lượng tồn kho cho lệnh sản xuất?')) {
        document.getElementById('formActionInput').value = 'save';
        document.getElementById('allocationForm').submit();
    }
}

function confirmCancel(e) {
    if (!confirm('<?= \App\Config\Messages::PB_CONFIRM_CANCEL ?>')) {
        e.preventDefault();
        return false;
    }
    return true;
}

// Phím tắt bàn phím
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        submitAllocation();
    }
    if (e.key === 'Escape') {
        if (confirm('<?= \App\Config\Messages::PB_CONFIRM_CANCEL ?>')) {
            window.location.href = '?route=phan-bo/index';
        }
    }
});
</script>

