<?php
use App\Core\View;
?>

<!-- 1. PAGE HEADER (SO VỚI ẢNH 01) -->
<?php View::partial('page_header', [
    'title' => 'Tra cứu tiến độ kế hoạch sản xuất',
    'subtitle' => 'Theo dõi và tra cứu tiến độ thực hiện các kế hoạch sản xuất theo thời gian thực.',
    'breadcrumb' => [
        'Kế hoạch sản xuất' => '#',
        'Tra cứu tiến độ' => null,
    ],
    'tagPill' => 'DPK Console',
]); ?>

<!-- 2. ĐIỀU KIỆN TRA CỨU & BỘ LỌC (SO VỚI ẢNH 01) -->
<div class="card">
    <div class="card-head">
        <div class="card-head-left">
            <div class="card-icon">
                <i class="bi bi-funnel"></i>
            </div>
            <div class="card-title-text">
                <strong>Điều kiện tra cứu</strong>
                <small>Nhập tiêu chí lọc đơn hàng sản xuất đang lưu trữ và phân bổ</small>
            </div>
        </div>
        <div class="card-head-right">
            <span style="font-size: 11px; font-weight: 800; color: var(--muted); letter-spacing: 0.8px;">
                CHẾ ĐỘ TRA CỨU ĐIỀU PHỐI
            </span>
        </div>
    </div>

    <div class="card-body">
        <div class="form-grid grid-4">
            <div class="form-group">
                <label class="form-label" for="maKeHoach">
                    Mã kế hoạch <span class="required">*</span>
                </label>
                <div class="input-icon-wrapper">
                    <i class="bi bi-search"></i>
                    <input type="text" id="maKeHoach" class="input" value="KH0001" placeholder="Tìm theo mã KH">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="xuongSx">Xưởng sản xuất</label>
                <div class="input-icon-wrapper">
                    <i class="bi bi-buildings"></i>
                    <select id="xuongSx" class="select">
                        <option value="">Tất cả xưởng</option>
                        <option value="X01">Xưởng 01 (Bánh quy)</option>
                        <option value="X02">Xưởng 02 (Kẹo dẻo)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="khoangThoiGian">Khoảng thời gian kế hoạch</label>
                <div class="input-icon-wrapper">
                    <i class="bi bi-calendar3"></i>
                    <input type="text" id="khoangThoiGian" class="input" value="01/09/2025 → 30/09/2025" readonly>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="trangThaiTh">Trạng thái thực hiện</label>
                <div class="input-icon-wrapper">
                    <i class="bi bi-check-circle"></i>
                    <select id="trangThaiTh" class="select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="DANG_THUC_HIEN">Đang thực hiện</option>
                        <option value="HOAN_THANH">Hoàn thành</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card-footer">
        <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--muted);">
            <i class="bi bi-info-circle" style="color: var(--primary);"></i>
            <span>Hệ thống chỉ thực hiện chức năng tra cứu dữ liệu (Chế độ Xem/Đọc dành cho Điều phối kho).</span>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <button type="button" class="btn btn-secondary">
                <i class="bi bi-arrow-counterclockwise"></i>
                Đặt lại bộ lọc
            </button>
            <button type="button" class="btn btn-primary">
                <i class="bi bi-search"></i>
                Tìm kiếm kế hoạch
            </button>
        </div>
    </div>
</div>

<!-- 3. BẢNG DỮ LIỆU KẾT QUẢ TRA CỨU (SO VỚI ẢNH 01) -->
<div class="card">
    <div class="card-head">
        <div class="card-head-left">
            <div class="card-icon">
                <i class="bi bi-table"></i>
            </div>
            <div class="card-title-text">
                <strong>Kết quả tra cứu</strong>
                <span class="badge badge-ongoing" style="margin-left: 8px;">Tìm thấy 4 kế hoạch sản xuất</span>
            </div>
        </div>
        <div class="card-head-right">
            <button type="button" class="btn btn-secondary btn-sm">
                <i class="bi bi-file-earmark-arrow-down"></i>
                Xuất báo cáo Excel / PDF
            </button>
            <button type="button" class="btn btn-secondary btn-sm" title="Tải lại">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">STT</th>
                    <th>MÃ KẾ HOẠCH</th>
                    <th>TÊN SẢN PHẨM</th>
                    <th>XƯỞNG SẢN XUẤT</th>
                    <th style="text-align: right;">SL KẾ HOẠCH</th>
                    <th style="text-align: right;">SL ĐÃ THỰC HIỆN</th>
                    <th style="min-width: 180px;">TIẾN ĐỘ THỰC HIỆN</th>
                    <th>TRẠNG THÁI</th>
                    <th style="text-align: center;">THAO TÁC</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center; font-weight: 700;">1</td>
                    <td><span class="code-blue">KH0001</span></td>
                    <td>
                        <div class="row-title">Bánh quy bơ Marvel Gold</div>
                        <div class="row-subtitle">Quy cách: SP-BG-01 | Hộp 20 cái</div>
                    </td>
                    <td><span class="badge badge-draft"><i class="bi bi-tools"></i> Xưởng 01</span></td>
                    <td class="num" style="text-align: right; font-weight: 700;">5.000</td>
                    <td class="num" style="text-align: right; font-weight: 700; color: var(--primary);">3.000</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="progress">
                                <div class="progress-bar" style="width: 60%;"></div>
                            </div>
                            <span class="num" style="font-size: 12px; font-weight: 750;">60%</span>
                        </div>
                    </td>
                    <td><span class="badge badge-ongoing">Đang thực hiện</span></td>
                    <td style="text-align: center;">
                        <button type="button" class="btn btn-primary btn-sm" data-modal-target="detailModalSample">
                            Xem chi tiết →
                        </button>
                    </td>
                </tr>

                <tr>
                    <td style="text-align: center; font-weight: 700;">2</td>
                    <td><span class="code-blue">KH0002</span></td>
                    <td>
                        <div class="row-title">Kẹo dẻo hương dâu Marvel</div>
                        <div class="row-subtitle">Quy cách: SP-KD-02 | Gói 250g</div>
                    </td>
                    <td><span class="badge badge-draft"><i class="bi bi-tools"></i> Xưởng 02</span></td>
                    <td class="num" style="text-align: right; font-weight: 700;">2.000</td>
                    <td class="num" style="text-align: right; font-weight: 700; color: var(--success);">2.000</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="progress">
                                <div class="progress-bar success" style="width: 100%;"></div>
                            </div>
                            <span class="num" style="font-size: 12px; font-weight: 750; color: var(--success);">100%</span>
                        </div>
                    </td>
                    <td><span class="badge badge-completed">Hoàn thành</span></td>
                    <td style="text-align: center;">
                        <button type="button" class="btn btn-secondary btn-sm" data-modal-target="detailModalSample">
                            Xem chi tiết →
                        </button>
                    </td>
                </tr>

                <tr>
                    <td style="text-align: center; font-weight: 700;">3</td>
                    <td><span class="code-blue">KH0003</span></td>
                    <td>
                        <div class="row-title">Bánh bông lan sô-cô-la</div>
                        <div class="row-subtitle">Quy cách: SP-BL-03 | Thùng 12 hộp</div>
                    </td>
                    <td><span class="badge badge-draft"><i class="bi bi-tools"></i> Xưởng 01</span></td>
                    <td class="num" style="text-align: right; font-weight: 700;">1.500</td>
                    <td class="num" style="text-align: right; font-weight: 700; color: var(--primary);">800</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="progress">
                                <div class="progress-bar" style="width: 53%;"></div>
                            </div>
                            <span class="num" style="font-size: 12px; font-weight: 750;">53%</span>
                        </div>
                    </td>
                    <td><span class="badge badge-ongoing">Đang thực hiện</span></td>
                    <td style="text-align: center;">
                        <button type="button" class="btn btn-secondary btn-sm" data-modal-target="detailModalSample">
                            Xem chi tiết →
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="pagination-bar">
        <div class="pagination-info">
            Hiển thị 1 - 3 của 3 kết quả · Xem 10 / trang
        </div>
        <div class="pagination-controls">
            <span class="page-btn"><i class="bi bi-chevron-left"></i></span>
            <span class="page-btn active">1</span>
            <span class="page-btn">2</span>
            <span class="page-btn">3</span>
            <span class="page-btn"><i class="bi bi-chevron-right"></i></span>
        </div>
    </div>
</div>

<!-- 4. WIZARD ĐIỀU PHỐI NHẬP KHO 4 BƯỚC (SO VỚI ẢNH 04) -->
<div style="margin: 32px 0 16px;">
    <h3 style="font-size: 18px; font-weight: 800; color: var(--text); margin-bottom: 6px;">
        <i class="bi bi-arrow-left-right" style="color: var(--primary);"></i>
        Wizard Điều Phối Nhập Kho 4 Bước (Đối chiếu Ảnh 04)
    </h3>
    <p style="font-size: 13px; color: var(--muted); margin: 0 0 16px;">
        Phân bổ kho, khu vực và vị trí lưu trữ cho hàng hóa cần nhập kho đã được kiểm tra đạt yêu cầu.
    </p>
</div>

<div class="wizard-steps">
    <div class="wizard-step done">
        <div class="step-indicator">
            <i class="bi bi-check-lg"></i>
        </div>
        <div class="step-meta">
            <span class="step-num">BƯỚC 01</span>
            <strong class="step-title">Chọn phiếu nhập</strong>
            <span class="step-sub">Đã chọn: PN000123</span>
        </div>
    </div>

    <div class="wizard-step active">
        <div class="step-indicator">2</div>
        <div class="step-meta">
            <span class="step-num">BƯỚC 02</span>
            <strong class="step-title">Kho tiếp nhận</strong>
            <span class="step-sub">Kho nguyên liệu 01</span>
        </div>
    </div>

    <div class="wizard-step upcoming">
        <div class="step-indicator">3</div>
        <div class="step-meta">
            <span class="step-num">BƯỚC 03</span>
            <strong class="step-title">Chọn vị trí lưu trữ</strong>
            <span class="step-sub">Vị trí: A01-01</span>
        </div>
    </div>

    <div class="wizard-step upcoming">
        <div class="step-indicator">4</div>
        <div class="step-meta">
            <span class="step-num">BƯỚC 04</span>
            <strong class="step-title">Xác nhận điều phối</strong>
            <span class="step-sub">Kiểm tra & phát lệnh</span>
        </div>
    </div>
</div>

<!-- 5. STEP CARD & FORM GRID (SO VỚI ẢNH 03) -->
<div style="margin: 32px 0 16px;">
    <h3 style="font-size: 18px; font-weight: 800; color: var(--text); margin-bottom: 6px;">
        <i class="bi bi-card-checklist" style="color: var(--primary);"></i>
        Form lập chứng từ dạng Step Card & Form Grid (Đối chiếu Ảnh 03)
    </h3>
</div>

<div class="card step-card">
    <div class="card-head">
        <div class="card-head-left">
            <div class="card-icon step-number">1</div>
            <div class="card-title-text">
                <strong>Thông tin phiếu nhập kho</strong>
                <small>Xác định nguồn gốc chứng từ và kho tiếp nhận</small>
            </div>
        </div>
        <div class="card-head-right">
            <span class="badge badge-passed">Tự động xác thực tài khoản đăng nhập</span>
        </div>
    </div>

    <div class="card-body">
        <div class="form-grid grid-3">
            <div class="form-group">
                <div class="form-label">
                    <span>Phiếu yêu cầu nhập kho <span class="required">*</span></span>
                    <a href="#" class="label-action">Tra cứu phiếu</a>
                </div>
                <select class="select">
                    <option>-- Chọn phiếu yêu cầu nhập kho --</option>
                    <option selected>YCNK-2025-001 (Bột mì đa dụng hoa ngọc lan)</option>
                </select>
                <span class="form-hint">Vui lòng chọn phiếu yêu cầu để hiển thị danh sách hàng hóa và người duyệt</span>
            </div>

            <div class="form-group">
                <label class="form-label">Mã phiếu nhập kho</label>
                <div class="input-icon-wrapper">
                    <i class="bi bi-lock"></i>
                    <input type="text" class="input is-readonly" value="Hệ thống cấp tự động khi lưu [NK-Auto]" readonly>
                </div>
                <span class="form-hint">Quy tắc sinh mã: NK-YYYY-XXXXXX</span>
            </div>

            <div class="form-group">
                <label class="form-label">Nhân viên kho phụ trách</label>
                <div class="input-icon-wrapper">
                    <i class="bi bi-person-badge"></i>
                    <input type="text" class="input is-readonly" value="Ngô Đình Trí (NVK-003)" readonly>
                </div>
                <span class="form-hint">Khu vực phân bổ: Line tiếp nhận Dock 03</span>
            </div>
        </div>
    </div>
</div>

<!-- 6. AUDIT ENGINE & HỘP QUY TẮC NGHIỆP VỤ (SO VỚI ẢNH 02 & 03) -->
<div class="card">
    <div class="card-head">
        <div class="card-head-left">
            <div class="card-icon">
                <i class="bi bi-shield-check"></i>
            </div>
            <div class="card-title-text">
                <strong>Kiểm tra tính hợp lệ dữ liệu phiếu (Auto-Audit Engine)</strong>
                <small>Tự động đối soát ràng buộc nghiệp vụ trước khi phát lệnh</small>
            </div>
        </div>
        <div class="card-head-right">
            <span class="badge badge-ongoing">4/4 Tiêu chuẩn đã đạt</span>
        </div>
    </div>

    <div class="card-body">
        <div class="form-grid grid-4">
            <div style="padding: 16px; background: #F0FDF4; border: 1px solid var(--success-border); border-radius: var(--radius-md);">
                <div style="display: flex; align-items: center; gap: 8px; color: var(--success); font-weight: 750; margin-bottom: 4px;">
                    <i class="bi bi-check-circle-fill"></i> Phiếu yêu cầu: Hợp lệ
                </div>
                <div style="font-size: 11px; color: var(--muted);">Đã được Ban Giám Đốc phê duyệt đúng thẩm quyền.</div>
            </div>

            <div style="padding: 16px; background: #F0FDF4; border: 1px solid var(--success-border); border-radius: var(--radius-md);">
                <div style="display: flex; align-items: center; gap: 8px; color: var(--success); font-weight: 750; margin-bottom: 4px;">
                    <i class="bi bi-check-circle-fill"></i> Sức chứa ô kệ: Đủ chỗ
                </div>
                <div style="font-size: 11px; color: var(--muted);">Khu vực kho A01 còn trống 800 m3 khả dụng.</div>
            </div>

            <div style="padding: 16px; background: #F0FDF4; border: 1px solid var(--success-border); border-radius: var(--radius-md);">
                <div style="display: flex; align-items: center; gap: 8px; color: var(--success); font-weight: 750; margin-bottom: 4px;">
                    <i class="bi bi-check-circle-fill"></i> Số lượng thực nhập: Chuẩn
                </div>
                <div style="font-size: 11px; color: var(--muted);">Số lượng kiểm đếm khớp 100% với đơn giao hàng.</div>
            </div>

            <div style="padding: 16px; background: #F0FDF4; border: 1px solid var(--success-border); border-radius: var(--radius-md);">
                <div style="display: flex; align-items: center; gap: 8px; color: var(--success); font-weight: 750; margin-bottom: 4px;">
                    <i class="bi bi-check-circle-fill"></i> Lô hàng & HSD: Đạt chuẩn
                </div>
                <div style="font-size: 11px; color: var(--muted);">Hạn dùng trên 12 tháng, đạt quy tắc FEFO.</div>
            </div>
        </div>

        <div class="rule-box">
            <i class="bi bi-shield-fill-check"></i>
            <div>
                <strong>Quy tắc nghiệp vụ WMS Điều Phối:</strong> Trường hợp <code>SL kế hoạch = 0</code>, hệ thống sẽ tự động hiển thị tỷ lệ tiến độ dạng ký tự trung lập <code>"—"</code> nhằm tránh lỗi chia cho số 0 và thông báo tới ban quản đốc điều chỉnh lại kế hoạch gốc. Màn hình hoạt động ở cơ chế chỉ đọc (Read-only) bảo toàn tính toàn vẹn dữ liệu điều hành kho.
            </div>
        </div>
    </div>
</div>

<!-- 7. MODAL XEM CHI TIẾT (SO VỚI ẢNH 02) -->
<div class="modal-backdrop" id="detailModalSample">
    <div class="modal-dialog">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div class="card-icon">
                    <i class="bi bi-card-heading"></i>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <strong style="font-size: 16px; color: var(--text);">Chi tiết kế hoạch sản xuất - KH0001</strong>
                        <span class="badge badge-ongoing">Đang thực hiện</span>
                    </div>
                    <small style="color: var(--muted);">Dữ liệu đồng bộ trực tiếp từ phân hệ Kế hoạch & Dây chuyền kho vận</small>
                </div>
            </div>
            <button type="button" class="modal-close" data-modal-close>
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="modal-body">
            <!-- Thông tin chung -->
            <div style="background: #F8FAFD; border: 1px solid var(--border); border-radius: var(--radius-md); padding: 16px; margin-bottom: 20px;">
                <div style="font-size: 11px; font-weight: 800; color: var(--primary); margin-bottom: 12px; letter-spacing: 0.5px;">
                    <i class="bi bi-info-circle-fill"></i> THÔNG TIN CHUNG KẾ HOẠCH
                </div>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; font-size: 13px;">
                    <div>Mã kế hoạch: <strong class="code-blue">KH0001</strong></div>
                    <div>Tên sản phẩm: <strong>Bánh quy bơ Marvel Gold</strong></div>
                    <div>Xưởng sản xuất: <strong>Xưởng 01 (Dây chuyền dập & gia công)</strong></div>
                    <div>Ngày bắt đầu: <strong>01/09/2025</strong></div>
                    <div>Ngày kết thúc dự kiến: <strong>15/09/2025</strong></div>
                    <div>Đơn vị phụ trách: <strong>Ngô Đình Trí (DPK)</strong></div>
                </div>
            </div>

            <!-- 3 Thẻ KPI trong modal -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 20px;">
                <div class="kpi-card tint-blue" style="padding: 14px;">
                    <span style="font-size: 11px; font-weight: 700; color: var(--muted);"><i class="bi bi-table"></i> Số lượng kế hoạch</span>
                    <div class="kpi-value-row">
                        <span class="kpi-number tabular-nums" style="font-size: 28px;">5.000</span>
                        <span class="kpi-unit">SP</span>
                    </div>
                    <small style="color: var(--muted); font-size: 11px;">Chỉ tiêu bàn giao kho</small>
                </div>

                <div class="kpi-card tint-green" style="padding: 14px;">
                    <span style="font-size: 11px; font-weight: 700; color: var(--success);"><i class="bi bi-check-circle"></i> Đã thực hiện</span>
                    <div class="kpi-value-row">
                        <span class="kpi-number tabular-nums" style="font-size: 28px; color: var(--success);">3.000</span>
                        <span class="kpi-unit">SP</span>
                    </div>
                    <small style="color: var(--muted); font-size: 11px;">Đã nhập kho chuẩn KCS</small>
                </div>

                <div class="kpi-card tint-purple" style="padding: 14px;">
                    <span style="font-size: 11px; font-weight: 700; color: var(--purple);"><i class="bi bi-hourglass-split"></i> Số lượng còn lại</span>
                    <div class="kpi-value-row">
                        <span class="kpi-number tabular-nums" style="font-size: 28px; color: var(--purple);">2.000</span>
                        <span class="kpi-unit">SP</span>
                    </div>
                    <small style="color: var(--muted); font-size: 11px;">Đang tiếp tục gia công</small>
                </div>
            </div>

            <!-- Tiến độ thực hiện -->
            <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 16px; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <div>
                        <strong style="font-size: 14px; color: var(--text);">Tiến độ thực hiện kế hoạch</strong>
                        <span class="badge badge-draft" style="margin-left: 8px;">Tự động tính toán</span>
                    </div>
                    <strong class="tabular-nums" style="font-size: 24px; color: var(--primary);">60%</strong>
                </div>
                <div class="progress" style="height: 10px; margin-bottom: 10px;">
                    <div class="progress-bar" style="width: 60%;"></div>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; color: var(--muted);">
                    <span>3.000 / 5.000 sản phẩm đã thực hiện (còn lại 2.000 sản phẩm)</span>
                    <span class="badge badge-ongoing" style="font-family: monospace;">Tiến độ = (3.000 / 5.000) × 100% = 60%</span>
                </div>
            </div>

            <div class="rule-box" style="margin-top: 0;">
                <i class="bi bi-shield-check"></i>
                <div>
                    <strong>Quy tắc nghiệp vụ WMS Điều Phối:</strong> Dữ liệu đã đồng bộ theo thời gian thực từ cơ sở dữ liệu nhà máy bánh kẹo Marvel Sweet.
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <div style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--muted);">
                <i class="bi bi-lock-fill"></i>
                <span>Dữ liệu chỉ đọc · Bản ghi phiên làm việc WMS Enterprise</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="window.print()">
                    <i class="bi bi-printer"></i>
                    In phiếu tiến độ
                </button>
                <button type="button" class="btn btn-primary btn-sm" data-modal-close>
                    Đóng
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 8. STICKY ACTIONS BAR (CHÂN FORM - ĐỐI CHIẾU ẢNH 03 & 04) -->
<div class="sticky-actions">
    <div class="shortcuts-hint">
        <i class="bi bi-keyboard"></i>
        <span>Phím tắt nhanh: <span class="kbd">Ctrl + S</span> để Lưu · <span class="kbd">Esc</span> để Hủy</span>
    </div>
    <div style="display: flex; align-items: center; gap: 12px;">
        <button type="button" class="btn btn-secondary">
            Hủy bỏ
        </button>
        <button type="button" class="btn btn-secondary">
            <i class="bi bi-bookmark"></i>
            Lưu bản nháp
        </button>
        <button type="button" class="btn btn-primary" data-hotkey-save onclick="alert('Đã kích hoạt hành động Lưu chứng từ!')">
            <i class="bi bi-check2-circle"></i>
            Lưu chứng từ (Ctrl + S)
        </button>
    </div>
</div>

