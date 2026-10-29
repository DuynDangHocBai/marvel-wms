<?php
/**
 * View: Cập nhật chênh lệch kiểm kê (Mẫu 03: Form lập chứng từ)
 * @var array $record Thông tin biên bản và các dòng mặt hàng
 * @var array $calculated Kết quả tính toán chênh lệch từ Service
 */
use App\Core\Csrf;

$maBB = htmlspecialchars($record['maBienBan'] ?? '');
$isValid = $calculated['valid'] ?? false;
$errors = $calculated['errors'] ?? [];
$details = $calculated['chiTiet'] ?? ($record['chiTiet'] ?? []);
$tongThua = (float)($calculated['tongThua'] ?? 0.0);
$tongThieu = (float)($calculated['tongThieu'] ?? 0.0);
$tongChenhLech = (float)($calculated['tongChenhLech'] ?? 0.0);
$giaTriRong = (float)($calculated['giaTriRong'] ?? 0.0);
$isKhongChenhLech = $calculated['isKhongChenhLech'] ?? false;

// Ngưỡng phê duyệt theo BR-01 & BR-08
$thamQuyen = ($tongChenhLech <= 10000000.0) ? 'Quản lý kho duyệt (≤ 10.000.000 đ)' : 'Ban Giám Đốc duyệt (> 10.000.000 đ)';
$trangThaiSauLuu = ($tongChenhLech <= 10000000.0) ? 'CHO_QLK_DUYET' : 'CHO_BGD_DUYET';
?>

<div class="page-container">
    <!-- Header trang & Breadcrumb -->
    <div class="page-header-box">
        <div>
            <div class="breadcrumb-nav">
                <a href="?route=kiem-ke/index">Kho vận</a>
                <span class="sep">›</span>
                <a href="?route=kiem-ke/index">Kiểm kê</a>
                <span class="sep">›</span>
                <span class="current">Cập nhật chênh lệch: <?= $maBB ?></span>
            </div>
            <h1 class="page-title">Cập nhật kết quả chênh lệch kiểm kê</h1>
            <p class="page-subtitle">So sánh số liệu thực tế đã kiểm với sổ sách, tính giá trị chênh lệch và chuyển cấp phê duyệt</p>
        </div>
        <div class="header-actions">
            <span class="badge badge-ongoing">ĐANG KIỂM KÊ</span>
        </div>
    </div>

    <form id="kiemKeForm" method="POST" action="?route=kiem-ke/update&id=<?= urlencode($record['maBienBan']) ?>">
        <?= Csrf::field() ?>
        <input type="hidden" name="maBienBan" value="<?= $maBB ?>">
        <input type="hidden" name="form_action" id="formActionInput" value="save">

        <!-- PHẦN 1: THÔNG TIN BIÊN BẢN CHỈ ĐỌC -->
        <div class="card mb-4">
            <div class="card-head">
                <div class="card-head-title">
                    <i class="bi bi-info-circle"></i> 1. Thông tin biên bản kiểm kê (Chỉ đọc)
                </div>
            </div>
            <div class="card-body">
                <div class="form-grid grid-4">
                    <div class="form-group">
                        <label class="form-label text-muted">Mã biên bản</label>
                        <div class="fw-bold fs-5 text-primary"><?= $maBB ?></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label text-muted">Kho kiểm kê</label>
                        <div class="fw-bold"><?= htmlspecialchars($record['maKho']) ?> - <?= htmlspecialchars($record['tenKho'] ?? '') ?></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label text-muted">Ngày kiểm kê</label>
                        <div><?= htmlspecialchars(date('d/m/Y', strtotime($record['ngayKiemKe']))) ?></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label text-muted">Người lập biên bản</label>
                        <div><?= htmlspecialchars($record['tenNhanVienLap'] ?? $record['maNhanVienLap']) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PHẦN 2: BẢNG MẶT HÀNG KIỂM KÊ (CHỈ ĐỌC) VÀ TÍNH CHÊNH LỆCH -->
        <div class="card mb-4">
            <div class="card-head space-between">
                <div class="card-head-title">
                    <i class="bi bi-box-seam"></i> 2. Danh sách mặt hàng & Chi tiết chênh lệch
                    <span class="badge badge-draft ml-2"><?= count($details) ?> mặt hàng</span>
                </div>
                <div>
                    <span class="text-muted small">Số thực tế đã được nhập ở bước lập biên bản kiểm kê</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 50px;" class="text-center">STT</th>
                                <th style="width: 120px;">Mã SKU</th>
                                <th>Tên bánh kẹo / Mặt hàng</th>
                                <th>Lô / HSD / Vị trí</th>
                                <th class="text-right" style="width: 110px;">Sổ sách</th>
                                <th class="text-right" style="width: 110px;">Thực tế</th>
                                <th class="text-right" style="width: 120px;">Đơn giá</th>
                                <th class="text-right" style="width: 110px;">Chênh lệch</th>
                                <th class="text-center" style="width: 130px;">Phân loại</th>
                                <th class="text-right" style="width: 150px;">Giá trị chênh lệch</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($details as $idx => $line): ?>
                                <?php
                                $diff = $line['soLuongChenhLech'] ?? null;
                                $phanLoai = $line['phanLoai'] ?? 'KHOP';
                                $val = $line['giaTriChenhLech'] ?? 0.0;
                                ?>
                                <tr>
                                    <td class="text-center text-muted"><?= $idx + 1 ?></td>
                                    <td><strong><?= htmlspecialchars($line['maSKU']) ?></strong></td>
                                    <td class="col-text-long">
                                        <div class="fw-bold"><?= htmlspecialchars($line['tenHang'] ?? '') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($line['donViTinh'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <div><strong><?= htmlspecialchars($line['soLo'] ?? $line['maLo'] ?? 'N/A') ?></strong></div>
                                        <?php if (!empty($line['ngayHetHan'])): ?>
                                            <small class="text-muted">HSD: <?= htmlspecialchars(date('d/m/Y', strtotime($line['ngayHetHan']))) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right">
                                        <?= $line['soLuongSoSach'] !== null ? formatSo($line['soLuongSoSach']) : '<span class="text-danger fw-bold">Chưa có</span>' ?>
                                    </td>
                                    <td class="text-right">
                                        <?= $line['soLuongThucTe'] !== null ? formatSo($line['soLuongThucTe']) : '<span class="text-danger fw-bold">Chưa có</span>' ?>
                                    </td>
                                    <td class="text-right">
                                        <?= $line['donGia'] !== null ? formatSo($line['donGia'], true) : '<span class="text-danger fw-bold">Chưa có</span>' ?>
                                    </td>
                                    <td class="text-right fw-bold">
                                        <?php if ($diff !== null): ?>
                                            <?php if ($diff > 0): ?>
                                                <span class="text-success">+<?= formatSo($diff) ?></span>
                                            <?php elseif ($diff < 0): ?>
                                                <span class="text-danger"><?= formatSo($diff) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">0</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">--</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($phanLoai === 'THUA'): ?>
                                            <span class="badge badge-passed">Thừa</span>
                                        <?php elseif ($phanLoai === 'THIEU'): ?>
                                            <span class="badge badge-failed">Thiếu</span>
                                        <?php elseif ($phanLoai === 'KHOP'): ?>
                                            <span class="badge badge-draft">Khớp</span>
                                        <?php else: ?>
                                            <span class="badge badge-draft">Chưa tính</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right fw-bold">
                                        <?php if ($val > 0): ?>
                                            <span class="text-success">+<?= formatSo($val, true) ?></span>
                                        <?php elseif ($val < 0): ?>
                                            <span class="text-danger"><?= formatSo($val, true) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted"><?= formatSo(0, true) ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-active">
                                <td colspan="7" class="text-right fw-bold">TỔNG GIÁ TRỊ HÀNG THỪA:</td>
                                <td colspan="3" class="text-right fw-bold text-success">
                                    +<?= formatSo($tongThua, true) ?>
                                </td>
                            </tr>
                            <tr class="table-active">
                                <td colspan="7" class="text-right fw-bold">TỔNG GIÁ TRỊ HÀNG THIẾU:</td>
                                <td colspan="3" class="text-right fw-bold text-danger">
                                    -<?= formatSo($tongThieu, true) ?>
                                </td>
                            </tr>
                            <tr class="table-active fs-6" style="background: #EEF1F9;">
                                <td colspan="7" class="text-right fw-bold text-primary">TỔNG ĐỘ LỚN CHÊNH LỆCH (|Thừa| + |Thiếu|):</td>
                                <td colspan="3" class="text-right fw-bold text-primary fs-5">
                                    <?= formatSo($tongChenhLech, true) ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="7" class="text-right text-muted">Giá trị chênh lệch ròng (Thừa - Thiếu):</td>
                                <td colspan="3" class="text-right fw-bold">
                                    <?= ($giaTriRong >= 0 ? '+' : '') . formatSo($giaTriRong, true) ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- PHẦN 3: THẺ KIỂM TRA HỢP LỆ & QUY TẮC NGHIỆP VỤ -->
        <div class="card mb-5">
            <div class="card-head">
                <div class="card-head-title">
                    <i class="bi bi-shield-check"></i> 3. Kiểm tra tính hợp lệ & Thẩm quyền phê duyệt (BR-01, BR-08)
                </div>
            </div>
            <div class="card-body">
                <?php if (!$isValid): ?>
                    <div class="alert alert-danger mb-3">
                        <h4 class="alert-heading"><i class="bi bi-exclamation-triangle-fill"></i> Dữ liệu kiểm kê chưa đủ điều kiện lưu:</h4>
                        <ul class="mb-0 mt-2 pl-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php elseif ($isKhongChenhLech): ?>
                    <div class="alert alert-success mb-3">
                        <i class="bi bi-check-circle-fill"></i> <strong>Không chênh lệch:</strong> Số lượng thực tế khớp 100% với sổ sách kho.
                    </div>
                <?php else: ?>
                    <div class="alert alert-success mb-3">
                        <i class="bi bi-check-circle-fill"></i> <strong>Dữ liệu hợp lệ:</strong> Toàn bộ mặt hàng đã có đủ số lượng sổ sách, số lượng thực tế và đơn giá. Sẵn sàng lưu kết quả.
                    </div>
                <?php endif; ?>

                <div class="rule-box">
                    <div class="rule-box-title">
                        <i class="bi bi-journal-text"></i> Quy định phê duyệt chênh lệch kiểm kê (Bảng 13 BR-01 & BR-08):
                    </div>
                    <ul class="rule-box-list">
                        <li><strong>Thẩm quyền dự kiến:</strong> <span class="badge badge-ongoing"><?= htmlspecialchars($thamQuyen) ?></span></li>
                        <li><strong>Trạng thái sau khi lưu:</strong> Biên bản sẽ tự động chuyển sang trạng thái <code><?= $trangThaiSauLuu ?></code> để người có thẩm quyền phê duyệt.</li>
                        <li><strong>Ràng buộc phạm vi:</strong> Thao tác này <em>tuyệt đối không làm thay đổi tồn kho thực tế</em> (tồn kho chỉ được điều chỉnh sau khi được phê duyệt chính thức).</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- THANH NÚT HÀNH ĐỘNG DÍNH DƯỚI (STICKY-ACTIONS) -->
        <div class="sticky-actions">
            <div class="sticky-actions-inner space-between">
                <button type="button" class="btn btn-ghost" onclick="confirmCancel()">
                    <i class="bi bi-x-circle"></i> Hủy bỏ (Esc)
                </button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary" onclick="submitForm('calculate')">
                        <i class="bi bi-calculator"></i> Tính chênh lệch
                    </button>
                    <button type="button" class="btn btn-primary" <?= !$isValid ? 'disabled' : '' ?> onclick="submitForm('save')">
                        <i class="bi bi-check-lg"></i> Lưu kết quả kiểm kê (Ctrl + S)
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
            <p><?= \App\Config\Messages::KK_CONFIRM_CANCEL ?></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeCancelModal()">Tiếp tục ở lại</button>
            <a href="?route=kiem-ke/index" class="btn btn-danger">Đồng ý hủy</a>
        </div>
    </div>
</div>

<script>
function submitForm(action) {
    document.getElementById('formActionInput').value = action;
    document.getElementById('kiemKeForm').submit();
}

function confirmCancel() {
    document.getElementById('cancelModal').style.display = 'flex';
}

function closeCancelModal() {
    document.getElementById('cancelModal').style.display = 'none';
}

// Phím tắt Ctrl + S và Esc
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        confirmCancel();
    }
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        <?php if ($isValid): ?>
            submitForm('save');
        <?php else: ?>
            alert('Số liệu biên bản chưa đầy đủ hoặc không hợp lệ để lưu.');
        <?php endif; ?>
    }
});
</script>

