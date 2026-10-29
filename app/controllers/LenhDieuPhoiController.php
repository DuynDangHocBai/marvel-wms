<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Flash;
use App\Repositories\DieuPhoiRepository;

/**
 * Controller: Lệnh điều phối được giao (Dành riêng cho NVK và QLK giám sát)
 * Quyền GHI: NVK (xác nhận nhận lệnh)
 * Quyền XEM: NVK, QLK
 */
class LenhDieuPhoiController extends Controller
{
    private const MODULE = 'lenh-dieu-phoi';
    private DieuPhoiRepository $repo;

    public function __construct()
    {
        $this->repo = new DieuPhoiRepository();
    }

    /**
     * Danh sách lệnh điều phối được giao (Mẫu 01: Tra cứu / Danh sách)
     */
    public function index(): void
    {
        Auth::requirePermission(self::MODULE, 'index');

        $currentUser = Auth::user();
        $userRole = Auth::role();
        $trangThai = $this->get('trangThai', '');

        // NVK chỉ xem lệnh giao cho chính mình; QLK xem được toàn bộ
        $workerId = ($userRole === 'NVK') ? ($currentUser['maNhanVien'] ?? '') : null;

        $orders = $this->repo->getAssignedOrdersForWorker($workerId, ['trangThai' => $trangThai]);
        $canConfirm = Auth::can(self::MODULE, 'confirm');

        $this->render('lenh_dieu_phoi/index', [
            'pageTitle'   => 'Lệnh điều phối được giao',
            'orders'      => $orders,
            'trangThai'   => $trangThai,
            'userRole'    => $userRole,
            'currentUser' => $currentUser,
            'canConfirm'  => $canConfirm,
        ]);
    }

    /**
     * Chi tiết lệnh điều phối (Mẫu 02: Modal chi tiết chỉ đọc)
     */
    public function detail(): void
    {
        Auth::requirePermission(self::MODULE, 'detail');

        $id = $this->get('id', '');
        if (empty($id)) {
            Flash::warning('Mã lệnh điều phối không hợp lệ.');
            $this->redirect('?route=lenh-dieu-phoi/index');
            return;
        }

        $order = $this->repo->getDispatchDetail($id);
        if (!$order) {
            Flash::error("Không tìm thấy lệnh điều phối {$id}.");
            $this->redirect('?route=lenh-dieu-phoi/index');
            return;
        }

        $canConfirm = Auth::can(self::MODULE, 'confirm');

        $this->render('lenh_dieu_phoi/detail_modal', [
            'pageTitle'  => "Chi tiết lệnh điều phối {$id}",
            'order'      => $order,
            'canConfirm' => $canConfirm,
        ]);
    }

    /**
     * Xác nhận "Đã nhận lệnh" từ phía NVK
     */
    public function confirm(): void
    {
        Auth::requirePermission(self::MODULE, 'confirm');

        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('?route=lenh-dieu-phoi/index');
            return;
        }

        $currentUser = Auth::user();
        $id = $this->post('maLenhDieuPhoi', '');

        if (empty($id)) {
            Flash::error('Mã lệnh điều phối không hợp lệ.');
            $this->redirect('?route=lenh-dieu-phoi/index');
            return;
        }

        $ok = $this->repo->confirmReceiptByWorker(
            $id,
            $currentUser['maNhanVien'] ?? 'NVK',
            $currentUser['maTaiKhoan'] ?? null
        );

        if ($ok) {
            Flash::success("Đã xác nhận nhận lệnh điều phối <strong>{$id}</strong>. Trạng thái đã chuyển sang Đang thực hiện.");
        } else {
            Flash::warning("Lệnh {$id} không ở trạng thái Chờ thực hiện hoặc đã được xác nhận trước đó.");
        }

        $this->redirect('?route=lenh-dieu-phoi/index');
    }
}
