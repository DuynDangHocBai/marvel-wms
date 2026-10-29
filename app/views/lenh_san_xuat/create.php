<?php
/**
 * View: Tạo lệnh sản xuất bánh kẹo theo BOM (Mẫu 04: Wizard 4 bước)
 * @var array $approvedPlans Danh sách kế hoạch sản xuất đã duyệt
 * @var array $xuongList Danh sách xưởng sản xuất
 * @var string $selectedPlanId Mã kế hoạch đang chọn
 * @var array|null $planDetail Chi tiết kế hoạch (bao gồm thành phẩm)
 * @var array $formData Dữ liệu form nhập liệu
 * @var array|null $bomResult Kết quả tính toán BOM và tồn kho khả dụng
 * @var int $currentStep Bước hiện tại (1, 2, 3, 4)
 */
use App\Core\Csrf;

$step = $currentStep ?? 1;
if (!empty($bomResult)) {
    $step = 3;
}
?>

<div class="page-container">
    <div class="page-header-box">
        <div>
            <div class="breadcrumb-nav">
                <a href="?route=lenh-san-xuat/index">Kho vận</a>
                <span class="sep">›</span>
                <a href="?route=lenh-san-xuat/index">Lệnh sản xuất</a>
                <span class="sep">›</span>
                <span class="current">Tạo lệnh sản xuất mới</span>
            </div>
            <h1 class="page-title">Tạo lệnh sản xuất bánh kẹo theo BOM</h1>
            <p class="page-subtitle">Thiết lập lệnh sản xuất từ kế hoạch đã duyệt, phân bổ theo xưởng và kiểm tra tồn kho khả dụng nguyên vật liệu (BR-06)</p>
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
                <div class="step-title">Bước 1: Kế hoạch</div>
                <div class="step-desc">Chọn KHSX đã duyệt</div>
            </div>
        </div>
        <div class="step-card <?= $step === 2 ? 'active' : ($step > 2 ? 'completed' : '') ?>" onclick="goToStep(2)">
            <div class="step-num"><?= $step > 2 ? '<i class="bi bi-check-lg"></i>' : '2' ?></div>
            <div class="step-text">
                <div class="step-title">Bước 2: Xưởng & Số lượng</div>
                <div class="step-desc">Phân bổ bánh kẹo sản xuất</div>
            </div>
        </div>
        <div class="step-card <?= $step === 3 ? 'active' : ($step > 3 ? 'completed' : '') ?>" onclick="goToStep(3)">
            <div class="step-num"><?= $step > 3 ? '<i class="bi bi-check-lg"></i>' : '3' ?></div>
            <div class="step-text">
                <div class="step-title">Bước 3: Nhu cầu NVL (BOM)</div>
                <div class="step-desc">Đối chiếu tồn kho khả dụng</div>
            </div>
        </div>
        <div class="step-card <?= $step === 4 ? 'active' : '' ?>" onclick="goToStep(4)">
            <div class="step-num">4</div>
            <div class="step-text">
                <div class="step-title">Bước 4: Xác nhận & Phát lệnh</div>
                <div class="step-desc">Sơ đồ luồng & Hoàn tất</div>
            </div>
        </div>
    </div>

    <form id="orderForm" method="POST" action="?route=lenh-san-xuat/create">
        <?= Csrf::field() ?>
        <input type="hidden" name="form_action" id="formActionInput" value="check_bom">

        <!-- BƯỚC 1: CHỌN KẾ HOẠCH SẢN XUẤT -->
        <div class="card mb-4 wizard-step-panel" id="panelStep1" style="<?= $step !== 1 ? 'display: none;' : '' ?>">
            <div class="card-head">
                <div class="card-head-title">
                    <i class="bi bi-calendar-check"></i> Bước 1: Chọn Kế hoạch sản xuất đã phê duyệt
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Chỉ các kế hoạch sản xuất có trạng thái <span class="badge badge-success">DA_DUYET</span> và còn số lượng thành phẩm chưa giao lệnh mới được hiển thị.</p>
                
                <div class="form-group mb-4" style="max-width: 500px;">
                    <label class="form-label font-weight-bold">Chọn kế hoạch sản xuất <span class="text-danger">*</span></label>
                    <select name="maKeHoach" id="planSelect" class="input" onchange="onPlanSelectChange(this.value)">
                        <option value="">-- Chọn kế hoạch sản xuất --</option>
                        <?php foreach ($approvedPlans as $p): ?>
                            <option value="<?= htmlspecialchars($p['maKeHoach']) ?>" <?= $selectedPlanId === $p['maKeHoach'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['maKeHoach']) ?> - <?= htmlspecialchars($p['tenKeHoach']) ?> (Tháng <?= date('m/Y', strtotime($p['ngayBatDau'])) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($planDetail): ?>
                    <div class="alert alert-info mt-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle-fill fs-4 mr-3"></i>
                            <div>
                                <h6 class="mb-1 font-weight-bold">Thông tin kế hoạch: <?= htmlspecialchars($planDetail['tenKeHoach']) ?> (<?= htmlspecialchars($planDetail['maKeHoach']) ?>)</h6>
                                <div>Thời gian dự kiến: <?= date('d/m/Y', strtotime($planDetail['ngayBatDau'])) ?> đến <?= date('d/m/Y', strtotime($planDetail['ngayHoanThanh'])) ?> · Xưởng mặc định: <?= htmlspecialchars($planDetail['tenXuong'] ?? 'Chưa chỉ định') ?></div>
                            </div>
                        </div>
                    </div>

                    <h6 class="font-weight-bold mt-4 mb-2">Thành phẩm trong kế hoạch này:</h6>
                    <div class="table-wrapper">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Mã SKU</th>
                                    <th>Tên thành phẩm</th>
                                    <th>ĐVT</th>
                                    <th class="text-right">Kế hoạch</th>
                                    <th class="text-right">Đã giao lệnh</th>
                                    <th class="text-right">Còn lại</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($planDetail['items'] as $item): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($item['maSKU']) ?></code></td>
                                        <td class="font-weight-bold col-text-long"><?= htmlspecialchars($item['tenHang']) ?></td>
                                        <td><?= htmlspecialchars($item['donViTinh']) ?></td>
                                        <td class="text-right"><?= formatSo($item['soLuongSanXuat']) ?></td>
                                        <td class="text-right text-muted"><?= formatSo($item['soLuongDaGiaoLenh']) ?></td>
                                        <td class="text-right font-weight-bold <?= $item['soLuongConLai'] > 0 ? 'text-primary' : 'text-danger' ?>">
                                            <?= formatSo($item['soLuongConLai']) ?>
                                        </td>
                                        <td>
                                            <?php if ($item['soLuongConLai'] <= 0): ?>
                                                <span class="badge badge-secondary">ĐÃ GIAO ĐỦ</span>
                                            <?php else: ?>
                                                <span class="badge badge-ongoing">CÒN CẦN GIAO</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-right mt-3">
                        <button type="button" class="btn btn-primary" onclick="goToStep(2)">
                            Tiếp tục: Phân bổ xưởng & số lượng <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- BƯỚC 2: CHỌN XƯỞNG & PHÂN BỔ SỐ LƯỢNG SẢN XUẤT -->
        <div class="card mb-4 wizard-step-panel" id="panelStep2" style="<?= $step !== 2 ? 'display: none;' : '' ?>">
            <div class="card-head">
                <div class="card-head-title">
                    <i class="bi bi-gear-wide-connected"></i> Bước 2: Chọn Xưởng sản xuất & Phân bổ số lượng
                </div>
            </div>
            <div class="card-body">
                <div class="form-grid grid-3 mb-4">
                    <div class="form-group">
                        <label class="form-label font-weight-bold">Xưởng sản xuất nhận lệnh <span class="text-danger">*</span></label>
                        <select name="maXuong" id="xuongSelect" class="input" required>
                            <option value="">-- Chọn xưởng sản xuất --</option>
                            <?php foreach ($xuongList as $x): ?>
                                <option value="<?= htmlspecialchars($x['maXuong']) ?>" <?= ($formData['maXuong'] ?? '') === $x['maXuong'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($x['tenXuong']) ?> (Kho NVL phụ trách: <?= htmlspecialchars($x['tenKhoNVL']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted mt-1 d-block">Có thể chia 1 kế hoạch cho nhiều xưởng khác nhau.</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label font-weight-bold">Ngày bắt đầu sản xuất <span class="text-danger">*</span></label>
                        <input type="date" name="ngayBatDau" class="input" value="<?= htmlspecialchars($formData['ngayBatDau'] ?? date('Y-m-d')) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label font-weight-bold">Ngày hoàn thành dự kiến <span class="text-danger">*</span></label>
                        <input type="date" name="ngayHoanThanh" class="input" value="<?= htmlspecialchars($formData['ngayHoanThanh'] ?? date('Y-m-d', strtotime('+3 days'))) ?>" required>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label">Ghi chú lệnh sản xuất</label>
                    <input type="text" name="ghiChu" class="input" placeholder="Ghi chú thêm về quy cách đóng gói, ca kíp sản xuất..." value="<?= htmlspecialchars($formData['ghiChu'] ?? '') ?>">
                </div>

                <h6 class="font-weight-bold mb-2">Nhập số lượng giao cho xưởng đợt này:</h6>
                <p class="text-muted mb-3">Nhập số lượng lớn hơn 0 và không vượt quá số lượng còn lại của kế hoạch.</p>

                <?php if ($planDetail && !empty($planDetail['items'])): ?>
                    <div class="table-wrapper">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="width: 120px;">Mã SKU</th>
                                    <th>Tên thành phẩm bánh kẹo</th>
                                    <th>ĐVT</th>
                                    <th class="text-right">Kế hoạch</th>
                                    <th class="text-right">Đã giao</th>
                                    <th class="text-right">Còn lại tối đa</th>
                                    <th class="text-right" style="width: 180px;">Số lượng giao đợt này</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($planDetail['items'] as $item): ?>
                                    <?php 
                                        $sku = $item['maSKU'];
                                        $remain = (float)$item['soLuongConLai'];
                                        // Tìm giá trị cũ nếu có
                                        $currentVal = '';
                                        if (!empty($formData['items'])) {
                                            foreach ($formData['items'] as $fItem) {
                                                if ($fItem['maSKU'] === $sku) {
                                                    $currentVal = $fItem['soLuongGiao'];
                                                    break;
                                                }
                                            }
                                        }
                                        if ($currentVal === '' && $remain > 0) {
                                            $currentVal = $remain; // mặc định đề xuất giao hết số còn lại
                                        }
                                    ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($sku) ?></code></td>
                                        <td class="font-weight-bold col-text-long"><?= htmlspecialchars($item['tenHang']) ?></td>
                                        <td><?= htmlspecialchars($item['donViTinh']) ?></td>
                                        <td class="text-right"><?= formatSo($item['soLuongSanXuat']) ?></td>
                                        <td class="text-right text-muted"><?= formatSo($item['soLuongDaGiaoLenh']) ?></td>
                                        <td class="text-right font-weight-bold <?= $remain > 0 ? 'text-primary' : 'text-danger' ?>">
                                            <?= formatSo($remain) ?>
                                        </td>
                                        <td class="text-right">
                                            <?php if ($remain > 0): ?>
                                                <input type="number" 
                                                       name="items[<?= htmlspecialchars($sku) ?>]" 
                                                       class="input text-right qty-input" 
                                                       min="0" 
                                                       max="<?= $remain ?>" 
                                                       step="1" 
                                                       value="<?= htmlspecialchars((string)$currentVal) ?>" 
                                                       placeholder="0"
                                                       data-max="<?= $remain ?>">
                                            <?php else: ?>
                                                <input type="text" class="input text-right bg-light" value="0" readonly>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-secondary" onclick="goToStep(1)">
                            <i class="bi bi-arrow-left"></i> Quay lại Bước 1
                        </button>
                        <button type="button" class="btn btn-primary" onclick="submitCheckBOM()">
                            <i class="bi bi-calculator"></i> Xác định nhu cầu nguyên liệu (BOM) <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">Vui lòng chọn Kế hoạch sản xuất ở Bước 1 trước.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- BƯỚC 3: ĐỐI CHIẾU NHU CẦU NGUYÊN VẬT LIỆU (BOM) -->
        <div class="card mb-4 wizard-step-panel" id="panelStep3" style="<?= $step !== 3 ? 'display: none;' : '' ?>">
            <div class="card-head">
                <div class="card-head-title">
                    <i class="bi bi-diagram-3"></i> Bước 3: Nhu cầu Nguyên vật liệu theo BOM & Đối chiếu tồn khả dụng
                </div>
            </div>
            <div class="card-body">
                <?php if ($bomResult): ?>
                    <?php if ($bomResult['canIssue']): ?>
                        <div class="alert alert-success d-flex align-items-center mb-4">
                            <i class="bi bi-check-circle-fill fs-3 mr-3"></i>
                            <div>
                                <h6 class="font-weight-bold mb-1">Tồn kho khả dụng ĐỦ để cấp phát lệnh sản xuất!</h6>
                                <span>Tất cả các nguyên vật liệu theo định mức BOM đều đáp ứng đủ. Khi phát lệnh, hệ thống sẽ thực hiện giữ chỗ (BR-06).</span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger d-flex align-items-center mb-4">
                            <i class="bi bi-exclamation-triangle-fill fs-3 mr-3"></i>
                            <div>
                                <h6 class="font-weight-bold mb-1"><?= htmlspecialchars(!empty($bomResult['canhBaoThieu']) ? $bomResult['canhBaoThieu'] : \App\Config\Messages::LSX_EXC_9_1_INSUFFICIENT) ?></h6>
                                <span>Một số nguyên vật liệu bị thiếu so với nhu cầu BOM. Bạn có thể <b>Lưu bản nháp</b> để chờ mua NVL bổ sung hoặc điều chỉnh giảm số lượng sản xuất.</span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="table-wrapper">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="width: 120px;">Mã NVL</th>
                                    <th>Tên nguyên vật liệu</th>
                                    <th>ĐVT</th>
                                    <th class="text-right">Nhu cầu BOM</th>
                                    <th class="text-right">Tồn khả dụng</th>
                                    <th class="text-right">Chênh lệch</th>
                                    <th class="text-center" style="width: 140px;">Đánh giá</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bomResult['nvlNeeds'] as $nvl): ?>
                                    <?php 
                                        $isSufficient = $nvl['tonKhaDung'] >= $nvl['soLuongCan'];
                                        $diff = $nvl['tonKhaDung'] - $nvl['soLuongCan'];
                                    ?>
                                    <tr class="<?= !$isSufficient ? 'table-danger-light' : '' ?>">
                                        <td><code><?= htmlspecialchars($nvl['maSKU']) ?></code></td>
                                        <td class="font-weight-bold col-text-long"><?= htmlspecialchars($nvl['tenHang']) ?></td>
                                        <td><?= htmlspecialchars($nvl['donViTinh']) ?></td>
                                        <td class="text-right font-weight-bold"><?= formatSo($nvl['soLuongCan']) ?></td>
                                        <td class="text-right"><?= formatSo($nvl['tonKhaDung']) ?></td>
                                        <td class="text-right font-weight-bold <?= $diff >= 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= ($diff >= 0 ? '+' : '') . formatSo($diff) ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($isSufficient): ?>
                                                <span class="badge badge-success"><i class="bi bi-check"></i> ĐỦ TỒN</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger"><i class="bi bi-x"></i> THIẾU <?= formatSo(abs($diff)) ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-secondary" onclick="goToStep(2)">
                            <i class="bi bi-arrow-left"></i> Điều chỉnh số lượng
                        </button>
                        <button type="button" class="btn btn-primary" onclick="goToStep(4)">
                            Tiếp tục: Xác nhận phát lệnh <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">Vui lòng nhập số lượng ở Bước 2 và bấm "Xác định nhu cầu nguyên liệu".</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- BƯỚC 4: SƠ ĐỒ LUỒNG & XÁC NHẬN PHÁT LỆNH -->
        <div class="card mb-4 wizard-step-panel" id="panelStep4" style="<?= $step !== 4 ? 'display: none;' : '' ?>">
            <div class="card-head">
                <div class="card-head-title">
                    <i class="bi bi-check2-circle"></i> Bước 4: Sơ đồ luồng & Xác nhận phát lệnh sản xuất
                </div>
            </div>
            <div class="card-body">
                <div class="p-3 bg-light rounded border mb-4">
                    <h6 class="font-weight-bold mb-3 text-primary"><i class="bi bi-diagram-2"></i> Sơ đồ điều phối luồng sản xuất:</h6>
                    <div class="d-flex flex-wrap align-items-center justify-content-around text-center py-2">
                        <div class="p-2">
                            <div class="badge badge-draft p-2 mb-1"><i class="bi bi-journal-text fs-5 d-block"></i> KẾ HOẠCH</div>
                            <div class="font-weight-bold"><?= htmlspecialchars($selectedPlanId) ?></div>
                        </div>
                        <div class="text-muted"><i class="bi bi-arrow-right fs-4"></i></div>
                        <div class="p-2">
                            <div class="badge badge-ongoing p-2 mb-1"><i class="bi bi-building-gear fs-5 d-block"></i> XƯỞNG SẢN XUẤT</div>
                            <div class="font-weight-bold" id="confirmXuongText">-</div>
                        </div>
                        <div class="text-muted"><i class="bi bi-arrow-right fs-4"></i></div>
                        <div class="p-2">
                            <div class="badge badge-success p-2 mb-1"><i class="bi bi-box-seam fs-5 d-block"></i> KHO NVL GIỮ CHỖ</div>
                            <div class="font-weight-bold" id="confirmKhoNVLText">-</div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning">
                    <h6 class="font-weight-bold mb-1"><i class="bi bi-shield-exclamation"></i> Quy tắc nghiệp vụ BR-06 (Giữ chỗ NVL):</h6>
                    <ul class="mb-0 pl-3">
                        <li>Khi bấm <b>"Phát lệnh sản xuất"</b>, lệnh sẽ chuyển sang trạng thái <code>DA_PHAT</code>. Số lượng tồn kho khả dụng của các NVL tương ứng sẽ bị <b>trừ ngay lập tức</b> để tránh cấp trùng cho xưởng khác.</li>
                        <li>Nếu bấm <b>"Lưu bản nháp"</b>, lệnh sẽ ở trạng thái <code>CHO_PHAT</code>, kế hoạch chưa bị trừ số lượng và chưa giữ chỗ NVL.</li>
                    </ul>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-secondary" onclick="goToStep(3)">
                        <i class="bi bi-arrow-left"></i> Quay lại Bước 3
                    </button>
                    <div>
                        <button type="button" class="btn btn-secondary mr-2" onclick="submitDraft()">
                            <i class="bi bi-save"></i> Lưu bản nháp (CHO_PHAT)
                        </button>
                        <?php if (!empty($bomResult) && $bomResult['canIssue']): ?>
                            <button type="button" class="btn btn-primary" onclick="submitIssue()">
                                <i class="bi bi-send-check"></i> Phát lệnh sản xuất (DA_PHAT)
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-primary" disabled title="Tồn kho khả dụng không đủ nguyên vật liệu">
                                <i class="bi bi-slash-circle"></i> Không thể phát lệnh (Thiếu NVL)
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- STICKY ACTION BAR -->
        <div class="sticky-action-bar">
            <div class="action-bar-left">
                <a href="?route=lenh-san-xuat/index" class="btn btn-light" onclick="return confirmCancel(event)">
                    <i class="bi bi-x-circle"></i> Hủy bỏ (Esc)
                </a>
            </div>
            <div class="action-bar-right">
                <button type="button" class="btn btn-secondary" onclick="submitDraft()">
                    <i class="bi bi-save"></i> Lưu bản nháp
                </button>
                <button type="button" class="btn btn-info" onclick="submitCheckBOM()">
                    <i class="bi bi-calculator"></i> Tính nhu cầu NVL (BOM)
                </button>
                <?php if (!empty($bomResult) && $bomResult['canIssue']): ?>
                    <button type="button" class="btn btn-primary" onclick="submitIssue()">
                        <i class="bi bi-check2-circle"></i> Phát lệnh sản xuất (Ctrl+S)
                    </button>
                <?php else: ?>
                    <button type="button" class="btn btn-primary" onclick="submitIssue()" <?= empty($bomResult) ? '' : 'disabled' ?>>
                        <i class="bi bi-check2-circle"></i> Phát lệnh sản xuất (Ctrl+S)
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<script>
function onPlanSelectChange(planId) {
    if (planId) {
        window.location.href = '?route=lenh-san-xuat/create&plan_id=' + encodeURIComponent(planId);
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

    // Cập nhật stepper header
    var stepCards = document.querySelectorAll('.wizard-stepper .step-card');
    stepCards.forEach(function(card, idx) {
        card.classList.remove('active');
        if (idx + 1 === stepNumber) {
            card.classList.add('active');
        }
    });

    if (stepNumber === 4) {
        updateStep4Info();
    }
}

function updateStep4Info() {
    var xuongSelect = document.getElementById('xuongSelect');
    if (xuongSelect && xuongSelect.selectedIndex >= 0) {
        var opt = xuongSelect.options[xuongSelect.selectedIndex];
        document.getElementById('confirmXuongText').innerText = opt.text.split('(')[0].trim();
        var khoMatch = opt.text.match(/Kho NVL phụ trách:\s*([^)]+)/);
        document.getElementById('confirmKhoNVLText').innerText = khoMatch ? khoMatch[1] : 'Kho NVL';
    }
}

function submitCheckBOM() {
    document.getElementById('formActionInput').value = 'check_bom';
    document.getElementById('orderForm').submit();
}

function submitDraft() {
    if (confirm('Lưu bản nháp lệnh sản xuất? Kế hoạch chưa bị trừ số lượng và chưa giữ chỗ NVL.')) {
        document.getElementById('formActionInput').value = 'save_draft';
        document.getElementById('orderForm').submit();
    }
}

function submitIssue() {
    if (confirm('Xác nhận phát lệnh sản xuất chính thức? Hệ thống sẽ trừ tồn kho khả dụng để giữ chỗ NVL (BR-06).')) {
        document.getElementById('formActionInput').value = 'issue';
        document.getElementById('orderForm').submit();
    }
}

function confirmCancel(e) {
    if (!confirm('<?= \App\Config\Messages::LSX_CONFIRM_CANCEL ?>')) {
        e.preventDefault();
        return false;
    }
    return true;
}

// Phím tắt bàn phím
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        submitIssue();
    }
    if (e.key === 'Escape') {
        if (confirm('<?= \App\Config\Messages::LSX_CONFIRM_CANCEL ?>')) {
            window.location.href = '?route=lenh-san-xuat/index';
        }
    }
});
</script>

