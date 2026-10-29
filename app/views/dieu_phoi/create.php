<?php
/**
 * View: Lập lệnh điều phối nhập kho (Mẫu 04: Wizard 4 bước)
 * @var array $pendingList Danh sách phiếu nhập chờ điều phối
 * @var array $warehouses Danh sách kho
 * @var array $workers Danh sách nhân viên kho để phân công
 * @var string $selectedReqId Mã phiếu nhập đang chọn
 * @var array|null $inboundReq Chi tiết phiếu nhập
 * @var string $selectedKho Mã kho đang chọn
 * @var array|null $targetWh Thông tin kho đang chọn
 * @var array $availablePositions Các vị trí khả dụng trong kho
 * @var array $suggestedPositions Gợi ý vị trí phân bổ
 * @var string $maNVK Mã NVK được phân công
 * @var string $ghiChu Ghi chú
 * @var int $currentStep Bước hiện tại (1..4)
 */
use App\Core\Csrf;

$step = $currentStep ?? 1;

// Map gợi ý hoặc nhập cũ
$allocMap = [];
if (!empty($suggestedPositions)) {
    foreach ($suggestedPositions as $p) {
        $k = ($p['maViTri'] ?? '') . '__' . ($p['maLo'] ?? '');
        $allocMap[$k] = (float)($p['soLuongDieuPhoi'] ?? 0);
    }
}
?>

<div class="page-container">
    <div class="page-header-box">
        <div>
            <div class="breadcrumb-nav">
                <a href="?route=dieu-phoi/index">Kho vận</a>
                <span class="sep">›</span>
                <a href="?route=dieu-phoi/index">Điều phối nhập kho</a>
                <span class="sep">›</span>
                <span class="current">Lập lệnh điều phối</span>
            </div>
            <h1 class="page-title">Điều phối vị trí lưu trữ nhập kho</h1>
            <p class="page-subtitle">Kiểm tra kết quả QA/QC, sức chứa kho và phân bổ vị trí ô/kệ cho lô hàng theo quy trình điều phối</p>
        </div>
        <div class="header-actions">
            <span class="badge badge-ongoing">WIZARD ĐIỀU PHỐI</span>
        </div>
    </div>

    <!-- WIZARD STEPPER -->
    <div class="wizard-stepper mb-4">
        <div class="step-card <?= $step === 1 ? 'active' : ($step > 1 ? 'completed' : '') ?>" onclick="goToStep(1)">
            <div class="step-num"><?= $step > 1 ? '<i class="bi bi-check-lg"></i>' : '1' ?></div>
            <div class="step-text">
                <div class="step-title">Bước 1: Phiếu nhập</div>
                <div class="step-desc">Chọn lô hàng đã kiểm định</div>
            </div>
        </div>
        <div class="step-card <?= $step === 2 ? 'active' : ($step > 2 ? 'completed' : '') ?>" onclick="goToStep(2)">
            <div class="step-num"><?= $step > 2 ? '<i class="bi bi-check-lg"></i>' : '2' ?></div>
            <div class="step-text">
                <div class="step-title">Bước 2: Kho tiếp nhận</div>
                <div class="step-desc">Đối chiếu sức chứa kho</div>
            </div>
        </div>
        <div class="step-card <?= $step === 3 ? 'active' : ($step > 3 ? 'completed' : '') ?>" onclick="goToStep(3)">
            <div class="step-num"><?= $step > 3 ? '<i class="bi bi-check-lg"></i>' : '3' ?></div>
            <div class="step-text">
                <div class="step-title">Bước 3: Vị trí ô / kệ</div>
                <div class="step-desc">Phân bổ vị trí lưu trữ</div>
            </div>
        </div>
        <div class="step-card <?= $step === 4 ? 'active' : '' ?>" onclick="goToStep(4)">
            <div class="step-num">4</div>
            <div class="step-text">
                <div class="step-title">Bước 4: Xác nhận</div>
                <div class="step-desc">Giao NVK & Phát lệnh</div>
            </div>
        </div>
    </div>

    <form id="dispatchForm" method="POST" action="?route=dieu-phoi/create">
        <?= Csrf::field() ?>
        <input type="hidden" name="form_action" id="formActionInput" value="save">
        <input type="hidden" name="maPhieuNhap" id="hiddenReqInput" value="<?= htmlspecialchars($selectedReqId) ?>">
        <input type="hidden" name="maKho" id="hiddenKhoInput" value="<?= htmlspecialchars($selectedKho) ?>">

        <!-- BƯỚC 1: CHỌN PHIẾU NHẬP KHO CHỜ ĐIỀU PHỐI -->
        <div class="card mb-4 wizard-step-panel" id="panelStep1" style="<?= $step !== 1 ? 'display: none;' : '' ?>">
            <div class="card-head">
                <div class="card-head-title"><i class="bi bi-box-arrow-in-down"></i> Bước 1: Chọn Phiếu nhập kho chờ điều phối</div>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Chỉ các phiếu nhập kho ở trạng thái <span class="badge badge-pending">CHO_DIEU_PHOI</span> và đạt kết quả kiểm định chất lượng (QA/QC) mới được phép phân bổ vị trí.</p>

                <div class="form-group mb-4" style="max-width: 500px;">
                    <label class="form-label font-weight-bold">Chọn phiếu nhập kho <span class="text-danger">*</span></label>
                    <select id="reqSelect" class="input" onchange="onReqSelectChange(this.value)">
                        <option value="">-- Chọn phiếu nhập kho --</option>
                        <?php foreach ($pendingList as $p): ?>
                            <option value="<?= htmlspecialchars($p['maPhieuNhap']) ?>" <?= $selectedReqId === $p['maPhieuNhap'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['maPhieuNhap']) ?> - <?= htmlspecialchars($p['tenKho']) ?> (SL: <?= formatSo($p['tongSoLuong']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($inboundReq): ?>
                    <h6 class="font-weight-bold mt-4 mb-2">Hàng hóa & Kết quả kiểm tra chất lượng (QA/QC):</h6>
                    <div class="table-wrapper">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="width: 120px;">Mã SKU</th>
                                    <th>Tên hàng hóa</th>
                                    <th>Số Lô</th>
                                    <th>Hạn sử dụng</th>
                                    <th class="text-right">Số lượng</th>
                                    <th class="text-center" style="width: 140px;">Kết quả QA/QC</th>
                                    <th class="text-center" style="width: 130px;">Đánh giá</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inboundReq['items'] as $item): ?>
                                    <?php 
                                        $qcOk = ($item['trangThaiChatLuong'] === 'DAT' && $item['trangThaiLo'] === 'HOAT_DONG');
                                        $expOk = empty($item['ngayHetHan']) || strtotime($item['ngayHetHan']) >= strtotime(date('Y-m-d'));
                                        $eligible = $qcOk && $expOk;
                                    ?>
                                    <tr class="<?= !$eligible ? 'table-danger-light' : '' ?>">
                                        <td><code><?= htmlspecialchars($item['maSKU']) ?></code></td>
                                        <td class="font-weight-bold col-text-long"><?= htmlspecialchars($item['tenHang']) ?></td>
                                        <td><strong><?= htmlspecialchars($item['maLo']) ?></strong></td>
                                        <td><?= htmlspecialchars($item['ngayHetHan'] ?? 'Không giới hạn') ?></td>
                                        <td class="text-right font-weight-bold"><?= formatSo($item['soLuong']) ?></td>
                                        <td class="text-center">
                                            <?php if ($item['trangThaiChatLuong'] === 'DAT'): ?>
                                                <span class="badge badge-passed"><i class="bi bi-check"></i> ĐẠT QC</span>
                                            <?php else: ?>
                                                <span class="badge badge-failed"><i class="bi bi-x"></i> <?= htmlspecialchars($item['trangThaiChatLuong']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($eligible): ?>
                                                <span class="badge badge-success">HỢP LỆ</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">CHẶN NHẬP</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-right mt-3">
                        <button type="button" class="btn btn-primary" onclick="goToStep(2)">
                            Tiếp tục: Chọn kho tiếp nhận <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- BƯỚC 2: CHỌN KHO TIẾP NHẬN & ĐỐI CHIẾU SỨC CHỨA -->
        <div class="card mb-4 wizard-step-panel" id="panelStep2" style="<?= $step !== 2 ? 'display: none;' : '' ?>">
            <div class="card-head">
                <div class="card-head-title"><i class="bi bi-building"></i> Bước 2: Chọn Kho tiếp nhận & Đối chiếu sức chứa</div>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Thẻ sức chứa kho với thanh xếp chồng thể hiện dung lượng: Đã dùng, Đã hẹn nhập và Còn lại:</p>

                <div class="form-grid grid-3 mb-4">
                    <?php foreach ($warehouses as $wh): ?>
                        <?php 
                            $isWhSelected = ($selectedKho === $wh['maKho']);
                            $maxCap = (float)$wh['sucChuaToiDa'];
                            $usedCap = (float)$wh['sucChuaDaSuDung'];
                            $henCap = (float)$wh['sucChuaDaHenNhap'];
                            $availCap = max(0.0, $maxCap - $usedCap - $henCap);

                            $pctUsed = $maxCap > 0 ? round(($usedCap / $maxCap) * 100, 1) : 0;
                            $pctHen = $maxCap > 0 ? round(($henCap / $maxCap) * 100, 1) : 0;
                            $isMaint = ($wh['trangThai'] === 'BAO_TRI');
                        ?>
                        <div class="card p-3 <?= $isWhSelected ? 'border-primary' : '' ?> <?= $isMaint ? 'bg-light' : 'cursor-pointer' ?>"
                             style="border-width: 2px; transition: all 0.2s; cursor: <?= $isMaint ? 'not-allowed' : 'pointer' ?>;"
                             onclick="<?= $isMaint ? "alert('" . \App\Config\Messages::DP_ALT_5_1_NO_WH . "')" : "onKhoSelectChange('" . htmlspecialchars($wh['maKho']) . "')" ?>">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="font-weight-bold mb-0 <?= $isMaint ? 'text-muted' : 'text-primary' ?>">
                                    <?= htmlspecialchars($wh['tenKho']) ?>
                                </h6>
                                <?php if ($isMaint): ?>
                                    <span class="badge badge-danger">BẢO TRÌ</span>
                                <?php else: ?>
                                    <span class="badge <?= $isWhSelected ? 'badge-primary' : 'badge-light' ?>">
                                        <?= htmlspecialchars($wh['maKho']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- THANH SỨC CHỨA XẾP CHỒNG -->
                            <div class="progress mb-2" style="height: 12px; background-color: var(--border-color); border-radius: 6px; overflow: hidden;">
                                <div class="progress-bar bg-info" style="width: <?= min(100, $pctUsed) ?>%;" title="Đã dùng: <?= $usedCap ?>"></div>
                                <div class="progress-bar bg-warning" style="width: <?= min(100 - $pctUsed, $pctHen) ?>%;" title="Đã hẹn nhập: <?= $henCap ?>"></div>
                            </div>

                            <div class="d-flex justify-content-between text-muted small">
                                <span>Khả dụng: <strong class="text-success"><?= formatSo($availCap) ?></strong></span>
                                <span>Tối đa: <?= formatSo($maxCap) ?></span>
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
                            Tiếp tục: Phân bổ vị trí ô / kệ <i class="bi bi-arrow-right"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- BƯỚC 3: PHÂN BỔ VỊ TRÍ LƯU TRỮ (Ô / KỆ) -->
        <div class="card mb-4 wizard-step-panel" id="panelStep3" style="<?= $step !== 3 ? 'display: none;' : '' ?>">
            <div class="card-head space-between">
                <div class="card-head-title">
                    <i class="bi bi-grid-3x3"></i> Bước 3: Phân bổ Vị trí lưu trữ trong kho <?= htmlspecialchars($selectedKho) ?>
                </div>
                <div>
                    <button type="button" class="btn btn-info btn-sm" onclick="triggerSuggestPositions()">
                        <i class="bi bi-magic"></i> Gợi ý vị trí tự động
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-4">
                    <i class="bi bi-info-circle mr-1"></i>
                    Nhập số lượng hàng hóa phân bổ vào từng ô/kệ lưu trữ. Cho phép chia một lô hàng vào nhiều vị trí khác nhau. Các vị trí trạng thái <code>BAO_TRI</code> hoặc đã <code>DAY</code> tự động bị loại bỏ.
                </div>

                <?php 
                    $rowIdx = 0;
                    $item0 = $inboundReq['items'][0] ?? null;
                    $neededQty = $item0 ? (float)$item0['soLuong'] : 0.0;
                ?>

                <?php if ($item0): ?>
                    <div class="card mb-4 border">
                        <div class="card-head space-between py-2" style="background-color: var(--bg-hover);">
                            <div>
                                <strong class="text-primary"><?= htmlspecialchars($item0['maSKU']) ?> - <?= htmlspecialchars($item0['tenHang']) ?></strong>
                                <span class="text-muted ml-2">(Lô: <strong><?= htmlspecialchars($item0['maLo']) ?></strong>)</span>
                            </div>
                            <div>
                                <span class="badge badge-ongoing">Tổng cần nhập: <?= formatSo($neededQty) ?> <?= htmlspecialchars($item0['donViTinh']) ?></span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-wrapper">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th style="width: 140px;">Mã vị trí</th>
                                            <th>Kệ / Tầng / Ô</th>
                                            <th>Loại hàng lưu trữ</th>
                                            <th class="text-right">Sức chứa tối đa</th>
                                            <th class="text-right">Đã lưu trữ</th>
                                            <th class="text-right">Sức chứa khả dụng</th>
                                            <th class="text-right" style="width: 160px;">Số lượng điều phối</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($availablePositions)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-3">
                                                    Không tìm thấy vị trí lưu kho khả dụng nào trong kho này.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($availablePositions as $pos): ?>
                                                <?php 
                                                    $rowIdx++;
                                                    $posKey = $pos['maViTri'] . '__' . $item0['maLo'];
                                                    $val = $allocMap[$posKey] ?? '';
                                                ?>
                                                <tr>
                                                    <td><strong><?= htmlspecialchars($pos['maViTri']) ?></strong></td>
                                                    <td>Kệ <?= htmlspecialchars($pos['maKe'] ?? '-') ?> · Tầng <?= htmlspecialchars($pos['tang'] ?? '-') ?> · Ô <?= htmlspecialchars($pos['oChua'] ?? '-') ?></td>
                                                    <td><span class="badge badge-light"><?= htmlspecialchars($pos['loaiHangLuuTru'] ?? 'CHUNG') ?></span></td>
                                                    <td class="text-right text-muted"><?= formatSo($pos['sucChuaToiDa']) ?></td>
                                                    <td class="text-right text-muted"><?= formatSo($pos['sucChuaDaSuDung']) ?></td>
                                                    <td class="text-right font-weight-bold text-success"><?= formatSo($pos['sucChuaKhaDung']) ?></td>
                                                    <td class="text-right">
                                                        <input type="hidden" name="allocations[<?= $rowIdx ?>][maLo]" value="<?= htmlspecialchars($item0['maLo']) ?>">
                                                        <input type="hidden" name="allocations[<?= $rowIdx ?>][maSKU]" value="<?= htmlspecialchars($item0['maSKU']) ?>">
                                                        <input type="hidden" name="allocations[<?= $rowIdx ?>][maViTri]" value="<?= htmlspecialchars($pos['maViTri']) ?>">
                                                        <input type="number" 
                                                               name="allocations[<?= $rowIdx ?>][soLuongDieuPhoi]" 
                                                               class="input text-right" 
                                                               min="0" 
                                                               max="<?= $pos['sucChuaKhaDung'] ?>" 
                                                               step="1" 
                                                               value="<?= htmlspecialchars((string)$val) ?>" 
                                                               placeholder="0">
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-grid grid-2 mt-3">
                    <div class="form-group">
                        <label class="form-label font-weight-bold">Nhân viên kho (NVK) phụ trách thực hiện <span class="text-danger">*</span></label>
                        <select name="maNhanVienThucHien" class="input" required>
                            <option value="">-- Chọn nhân viên kho thực hiện --</option>
                            <?php foreach ($workers as $w): ?>
                                <option value="<?= htmlspecialchars($w['maNhanVien']) ?>" <?= $maNVK === $w['maNhanVien'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($w['hoTen']) ?> (<?= htmlspecialchars($w['maNhanVien']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ghi chú điều phối</label>
                        <input type="text" name="ghiChu" class="input" placeholder="Ghi chú về ca kíp bốc dỡ, lưu trữ..." value="<?= htmlspecialchars($ghiChu) ?>">
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-secondary" onclick="goToStep(2)">
                        <i class="bi bi-arrow-left"></i> Quay lại Bước 2
                    </button>
                    <button type="button" class="btn btn-primary" onclick="goToStep(4)">
                        Tiếp tục: Xác nhận & Phát lệnh <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- BƯỚC 4: SƠ ĐỒ LUỒNG & XÁC NHẬN PHÁT LỆNH -->
        <div class="card mb-4 wizard-step-panel" id="panelStep4" style="<?= $step !== 4 ? 'display: none;' : '' ?>">
            <div class="card-head">
                <div class="card-head-title"><i class="bi bi-check2-circle"></i> Bước 4: Sơ đồ luồng & Xác nhận phát lệnh điều phối</div>
            </div>
            <div class="card-body">
                <!-- SƠ ĐỒ LUỒNG ĐIỀU PHỐI -->
                <div class="p-3 bg-light rounded border mb-4">
                    <h6 class="font-weight-bold mb-3 text-primary"><i class="bi bi-diagram-2"></i> Sơ đồ luồng điều phối nhập kho:</h6>
                    <div class="d-flex flex-wrap align-items-center justify-content-around text-center py-2">
                        <div class="p-2">
                            <div class="badge badge-pending p-2 mb-1"><i class="bi bi-box-arrow-in-down fs-5 d-block"></i> PHIẾU NHẬP</div>
                            <div class="font-weight-bold"><?= htmlspecialchars($selectedReqId) ?></div>
                        </div>
                        <div class="text-muted"><i class="bi bi-arrow-right fs-4"></i></div>
                        <div class="p-2">
                            <div class="badge badge-ongoing p-2 mb-1"><i class="bi bi-building fs-5 d-block"></i> KHO TIẾP NHẬN</div>
                            <div class="font-weight-bold"><?= htmlspecialchars($selectedKho) ?></div>
                        </div>
                        <div class="text-muted"><i class="bi bi-arrow-right fs-4"></i></div>
                        <div class="p-2">
                            <div class="badge badge-warning p-2 mb-1"><i class="bi bi-grid-3x3 fs-5 d-block"></i> Ô / KỆ LƯU TRỮ</div>
                            <div class="font-weight-bold">Vị trí chỉ định</div>
                        </div>
                        <div class="text-muted"><i class="bi bi-arrow-right fs-4"></i></div>
                        <div class="p-2">
                            <div class="badge badge-success p-2 mb-1"><i class="bi bi-person-badge fs-5 d-block"></i> NVK THỰC HIỆN</div>
                            <div class="font-weight-bold">Bốc dỡ & Xếp hàng</div>
                        </div>
                    </div>
                </div>

                <div class="card p-3 mb-4" style="border-left: 4px solid var(--primary-color);">
                    <h6 class="font-weight-bold text-primary mb-1"><i class="bi bi-shield-check"></i> Thông tin điều phối chính thức:</h6>
                    <p class="mb-0 text-muted">
                        Khi bấm <b>"Phát lệnh điều phối"</b>, phiếu nhập sẽ chuyển sang trạng thái <code>DA_DIEU_PHOI</code>. Sức chứa đã hẹn nhập của kho tiếp nhận sẽ được cộng thêm và lệnh điều phối <code>LDP-YYMMDD-NNNN</code> sẽ xuất hiện trong giao diện của Nhân viên kho được chỉ định.
                    </p>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-secondary" onclick="goToStep(3)">
                        <i class="bi bi-arrow-left"></i> Quay lại Bước 3
                    </button>
                    <button type="button" class="btn btn-primary" onclick="submitDispatch()">
                        <i class="bi bi-send-check"></i> Phát lệnh điều phối (Ctrl+S)
                    </button>
                </div>
            </div>
        </div>

        <!-- STICKY ACTION BAR -->
        <div class="sticky-action-bar">
            <div class="action-bar-left">
                <a href="?route=dieu-phoi/index" class="btn btn-light" onclick="return confirmCancel(event)">
                    <i class="bi bi-x-circle"></i> Hủy bỏ (Esc)
                </a>
            </div>
            <div class="action-bar-right">
                <button type="button" class="btn btn-info" onclick="triggerSuggestPositions()">
                    <i class="bi bi-magic"></i> Gợi ý vị trí
                </button>
                <button type="button" class="btn btn-primary" onclick="submitDispatch()">
                    <i class="bi bi-send-check"></i> Phát lệnh điều phối (Ctrl+S)
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function onReqSelectChange(reqId) {
    if (reqId) {
        window.location.href = '?route=dieu-phoi/create&req_id=' + encodeURIComponent(reqId);
    }
}

function onKhoSelectChange(khoId) {
    var reqId = document.getElementById('hiddenReqInput').value;
    if (khoId && reqId) {
        window.location.href = '?route=dieu-phoi/create&req_id=' + encodeURIComponent(reqId) + '&kho_id=' + encodeURIComponent(khoId);
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

function triggerSuggestPositions() {
    document.getElementById('formActionInput').value = 'suggest';
    document.getElementById('dispatchForm').submit();
}

function submitDispatch() {
    var reqId = document.getElementById('hiddenReqInput') ? document.getElementById('hiddenReqInput').value : '';
    if (!reqId) {
        alert('<?= \App\Config\Messages::DP_ALT_3_1_EMPTY_REQ ?>');
        return;
    }
    if (confirm('Xác nhận phát lệnh điều phối nhập kho và giao nhiệm vụ cho nhân viên kho?')) {
        document.getElementById('formActionInput').value = 'save';
        document.getElementById('dispatchForm').submit();
    }
}

function confirmCancel(e) {
    if (!confirm('<?= \App\Config\Messages::DP_CONFIRM_CANCEL ?>')) {
        e.preventDefault();
        return false;
    }
    return true;
}

// Phím tắt bàn phím
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        submitDispatch();
    }
    if (e.key === 'Escape') {
        if (confirm('<?= \App\Config\Messages::DP_CONFIRM_CANCEL ?>')) {
            window.location.href = '?route=dieu-phoi/index';
        }
    }
});
</script>

