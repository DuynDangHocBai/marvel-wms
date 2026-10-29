<?php
/**
 * Partial: status_badge
 * @var string $status Mã trạng thái
 * @var string|null $label Nhãn hiển thị tiếng Việt (nếu null tự map)
 */

$status = $status ?? 'BAN_NHAP';
$label = $label ?? null;

$class = 'badge-draft';
$defaultLabel = 'Bản nháp';

switch (strtoupper($status)) {
    case 'HOAT_DONG':
    case 'DAT':
    case 'PHU_HOP':
    case 'DU_SUC_CHUA':
        $class = 'badge-passed';
        $defaultLabel = 'Hoạt động';
        break;

    case 'HOAN_THANH':
        $class = 'badge-completed';
        $defaultLabel = 'Hoàn thành';
        break;

    case 'DANG_THUC_HIEN':
    case 'DANG_XU_LY':
    case 'DANG_SOAN_THAO':
        $class = 'badge-ongoing';
        $defaultLabel = 'Đang thực hiện';
        break;

    case 'CHO_DIEU_PHOI':
    case 'CHO_XAC_NHAN':
    case 'CHO_KIEM':
    case 'CHO_DUYET':
        $class = 'badge-pending';
        $defaultLabel = 'Chờ xử lý';
        break;

    case 'KHOA':
    case 'KHONG_DAT':
    case 'KHONG_DU':
    case 'TU_CHOI':
    case 'DA_HUY':
        $class = 'badge-failed';
        $defaultLabel = 'Đã khóa / Từ chối';
        break;

    case 'BAN_NHAP':
    default:
        $class = 'badge-draft';
        $defaultLabel = 'Bản nháp';
        break;
}

$displayText = $label ?? $defaultLabel;
?>
<span class="badge <?= htmlspecialchars($class) ?>">
    <?= htmlspecialchars($displayText) ?>
</span>

