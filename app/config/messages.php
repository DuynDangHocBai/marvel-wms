<?php

namespace App\Config;

/**
 * TẬP TRUNG TẤT CẢ CÂU THÔNG BÁO HỆ THỐNG MARVEL WMS
 * Nguồn duy nhất cho Controller, Service, Views và Tests.
 */
class Messages
{
    // ==========================================
    // UC 2.3.3: LẬP ĐỀ NGHỊ MUA NVL
    // ==========================================
    public const DNM_SUCCESS               = 'Lập đề nghị mua nguyên vật liệu thành công';
    public const DNM_EXC_5_1_NOT_NEEDED    = 'Lượng hàng tồn kho hiện tại đã đủ đáp ứng nhu cầu, không cần mua thêm.';
    public const DNM_EXC_8_1_INVALID       = 'Vui lòng nhập đầy đủ ngày cần hàng và số lượng mua hợp lệ.';
    public const DNM_CONFIRM_CANCEL        = 'Bạn có chắc chắn muốn hủy đề nghị mua nguyên vật liệu này?';
    public const DNM_EXC_SAVE_FAILED       = 'Không thể lưu đề nghị mua nguyên vật liệu.';

    // ==========================================
    // UC 2.1.3: TẠO LỆNH SẢN XUẤT
    // ==========================================
    public const LSX_SUCCESS               = 'Tạo và phát lệnh sản xuất thành công';
    public const LSX_SUCCESS_PREFIX        = 'Tạo và phát lệnh sản xuất thành công - Mã lệnh: ';
    public const LSX_EXC_6_1_OVER_LIMIT    = 'Số lượng nhập vượt quá hạn mức còn lại của kế hoạch sản xuất (Tối đa: %s)';
    public const LSX_EXC_9_1_INSUFFICIENT  = 'Kho nguyên liệu không đủ tồn khả dụng để cấp phát. Lượng thiếu: ';
    public const LSX_CONFIRM_CANCEL        = 'Bạn có chắc chắn muốn hủy bỏ lệnh sản xuất này?';
    public const LSX_LABEL_DRAFT           = 'Bản nháp';
    public const LSX_DRAFT_SUCCESS         = 'Đã lưu bản nháp lệnh sản xuất %s. Kế hoạch chưa bị trừ số lượng và chưa giữ chỗ NVL.';
    public const LSX_EXC_SAVE_FAILED       = 'Không thể tạo lệnh sản xuất.';

    // ==========================================
    // UC 2.2.3: PHÂN BỔ SỐ LƯỢNG
    // ==========================================
    public const PB_SUCCESS                = 'Phân bổ số lượng thành công';
    public const PB_EXC_4_1_LOAD_FAILED    = 'Không thể tải thông tin kho, vui lòng thử lại.';
    public const PB_EXC_8_1_OVER_AVAILABLE = 'Số lượng phân bổ vượt quá số lượng tồn khả dụng.';
    public const PB_EXC_11_1_SAVE_FAILED   = 'Không thể lưu thông tin phân bổ.';
    public const PB_CONFIRM_CANCEL         = 'Bạn có chắc chắn muốn hủy phân bổ số lượng này?';

    // ==========================================
    // UC 2.5.3: ĐIỀU PHỐI NHẬP KHO
    // ==========================================
    public const DP_SUCCESS                = 'Điều phối nhập kho thành công';
    public const DP_ALT_3_1_EMPTY_REQ      = 'Vui lòng chọn phiếu/yêu cầu nhập kho cần điều phối.';
    public const DP_ALT_5_1_NO_WH          = 'Không có kho/khu vực tiếp nhận phù hợp.';
    public const DP_ALT_9_1_CAPACITY       = 'Kho/khu vực không đủ sức chứa. Vui lòng chọn kho/khu vực khác.';
    public const DP_ALT_10_1_NO_BIN        = 'Không có vị trí lưu trữ phù hợp.';
    public const DP_ALT_12_1_BIN_UNSUITABLE= 'Vị trí lưu trữ không phù hợp. Vui lòng chọn vị trí khác.';
    public const DP_EXC_4_1_LOAD_FAILED    = 'Không thể tải thông tin phiếu/yêu cầu nhập kho. Vui lòng thử lại.';
    public const DP_EXC_9_2_CHECK_CAP_FAIL = 'Không thể kiểm tra sức chứa kho/khu vực. Vui lòng thử lại.';
    public const DP_EXC_12_2_CHECK_BIN_FAIL= 'Không thể kiểm tra vị trí lưu trữ. Vui lòng thử lại.';
    public const DP_EXC_16_1_DISPATCH_FAIL = 'Tạo lệnh điều phối không thành công. Vui lòng thử lại.';
    public const DP_EXC_16_2_ALREADY_DISPATCHED = 'Phiếu/yêu cầu nhập kho đã được điều phối.';
    public const DP_QC_REJECTED            = 'Lô hàng %s chưa đạt kiểm tra chất lượng (QA/QC) hoặc đang bị cách ly.';
    public const DP_CONFIRM_CANCEL         = 'Bạn có chắc chắn muốn hủy điều phối nhập kho này?';

    // ==========================================
    // UC 2.6.3: CẬP NHẬT CHÊNH LỆCH KIỂM KÊ
    // ==========================================
    public const KK_SUCCESS                = 'Cập nhật thành công.';
    public const KK_EXC_4_1_NO_ACTUAL      = 'Biên bản kiểm kê chưa có số lượng thực tế: %s.';
    public const KK_EXC_4_2_MISSING_INFO   = 'Không đủ thông tin để tính chênh lệch: %s (số lượng sổ sách hoặc đơn giá).';
    public const KK_EXC_10_1_SAVE_FAILED   = 'Không thể lưu thông tin chênh lệch';
    public const KK_ALT_5_1_MATCHED        = 'Không phát hiện chênh lệch giữa sổ sách và thực tế.';
    public const KK_CONFIRM_CANCEL         = 'Bạn có chắc chắn muốn hủy cập nhật chênh lệch kiểm kê này?';

    // ==========================================
    // UC 2.4.3: ĐỀ XUẤT XỬ LÝ HÀNG LỖI HỎNG
    // ==========================================
    public const HL_SUCCESS                = 'Đề xuất xử lý hàng lỗi hỏng thành công';
    public const HL_EXC_6_1_OVER_QTY       = 'Số lượng xử lý vượt quá số lượng hàng lỗi, hỏng.';
    public const HL_EXC_10_1_INVALID       = 'Thông tin đề xuất không hợp lệ: %s';
    public const HL_EXC_12_1_MISSING_INFO  = 'Không đủ thông tin để tổng hợp giá trị.';
    public const HL_EXC_15_1_SAVE_FAILED   = 'Không thể lưu đề xuất xử lý hàng lỗi hỏng.';
    public const HL_CONFIRM_CANCEL         = 'Bạn có chắc chắn muốn hủy lập đề xuất xử lý hàng lỗi hỏng này?';

    /**
     * Lấy mảng toàn bộ thông báo
     */
    public static function all(): array
    {
        return [
            // UC 2.3.3
            'dnm_success'            => self::DNM_SUCCESS,
            'dnm_exc_5_1_not_needed' => self::DNM_EXC_5_1_NOT_NEEDED,
            'dnm_exc_8_1_invalid'    => self::DNM_EXC_8_1_INVALID,
            'dnm_confirm_cancel'     => self::DNM_CONFIRM_CANCEL,
            'dnm_exc_save_failed'    => self::DNM_EXC_SAVE_FAILED,

            // UC 2.1.3
            'lsx_success'            => self::LSX_SUCCESS,
            'lsx_success_prefix'     => self::LSX_SUCCESS_PREFIX,
            'lsx_exc_6_1_over_limit' => self::LSX_EXC_6_1_OVER_LIMIT,
            'lsx_exc_9_1_insufficient' => self::LSX_EXC_9_1_INSUFFICIENT,
            'lsx_confirm_cancel'     => self::LSX_CONFIRM_CANCEL,
            'lsx_label_draft'        => self::LSX_LABEL_DRAFT,
            'lsx_draft_success'      => self::LSX_DRAFT_SUCCESS,
            'lsx_exc_save_failed'    => self::LSX_EXC_SAVE_FAILED,

            // UC 2.2.3
            'pb_success'             => self::PB_SUCCESS,
            'pb_exc_4_1_load_failed' => self::PB_EXC_4_1_LOAD_FAILED,
            'pb_exc_8_1_over_available' => self::PB_EXC_8_1_OVER_AVAILABLE,
            'pb_exc_11_1_save_failed' => self::PB_EXC_11_1_SAVE_FAILED,
            'pb_confirm_cancel'      => self::PB_CONFIRM_CANCEL,

            // UC 2.5.3
            'dp_success'             => self::DP_SUCCESS,
            'dp_alt_3_1_empty_req'   => self::DP_ALT_3_1_EMPTY_REQ,
            'dp_alt_5_1_no_wh'       => self::DP_ALT_5_1_NO_WH,
            'dp_alt_9_1_capacity'    => self::DP_ALT_9_1_CAPACITY,
            'dp_alt_10_1_no_bin'     => self::DP_ALT_10_1_NO_BIN,
            'dp_alt_12_1_bin_unsuitable' => self::DP_ALT_12_1_BIN_UNSUITABLE,
            'dp_exc_4_1_load_failed' => self::DP_EXC_4_1_LOAD_FAILED,
            'dp_exc_9_2_check_cap_fail' => self::DP_EXC_9_2_CHECK_CAP_FAIL,
            'dp_exc_12_2_check_bin_fail' => self::DP_EXC_12_2_CHECK_BIN_FAIL,
            'dp_exc_16_1_dispatch_fail' => self::DP_EXC_16_1_DISPATCH_FAIL,
            'dp_exc_16_2_already_dispatched' => self::DP_EXC_16_2_ALREADY_DISPATCHED,
            'dp_qc_rejected'         => self::DP_QC_REJECTED,
            'dp_confirm_cancel'      => self::DP_CONFIRM_CANCEL,

            // UC 2.6.3
            'kk_success'             => self::KK_SUCCESS,
            'kk_exc_4_1_no_actual'   => self::KK_EXC_4_1_NO_ACTUAL,
            'kk_exc_4_2_missing_info' => self::KK_EXC_4_2_MISSING_INFO,
            'kk_exc_10_1_save_failed' => self::KK_EXC_10_1_SAVE_FAILED,
            'kk_alt_5_1_matched'     => self::KK_ALT_5_1_MATCHED,
            'kk_confirm_cancel'      => self::KK_CONFIRM_CANCEL,

            // UC 2.4.3
            'hl_success'             => self::HL_SUCCESS,
            'hl_exc_6_1_over_qty'    => self::HL_EXC_6_1_OVER_QTY,
            'hl_exc_10_1_invalid'    => self::HL_EXC_10_1_INVALID,
            'hl_exc_12_1_missing_info' => self::HL_EXC_12_1_MISSING_INFO,
            'hl_exc_15_1_save_failed' => self::HL_EXC_15_1_SAVE_FAILED,
            'hl_confirm_cancel'      => self::HL_CONFIRM_CANCEL,
        ];
    }
}

// Trả về mảng để hỗ trợ cú pháp $messages = require 'app/config/messages.php';
return Messages::all();

