<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Flash;
use App\Config\Messages;
use App\Repositories\PhanBoRepository;
use App\Services\PhanBoService;

/**
 * Controller: Phân bổ số lượng nguyên vật liệu lưu kho (Prompt 5 - QLK)
 * Quyền GHI: QLK
 * Quyền XEM: QLK, LKH, BGD
 */
class PhanBoController extends Controller
{
    private const MODULE = 'phan-bo';
    private PhanBoRepository $repo;
    private PhanBoService $service;

    public function __construct()
    {
        $this->repo = new PhanBoRepository();
        $this->service = new PhanBoService($this->repo);
    }

    /**
     * Màn hình danh sách (Mẫu 01: Tra cứu / Danh sách 2 Tab)
     * Tab 1: Lệnh chờ phân bổ (Pending Orders)
     * Tab 2: Lịch sử phiếu phân bổ (Allocation History)
     */
    public function index(): void
    {
        Auth::requirePermission(self::MODULE, 'index');

        $activeTab = $this->get('tab', 'pending');
        $keyword = $this->get('keyword', '');
        $maKho = $this->get('maKho', '');
        $trangThai = $this->get('trangThai', '');

        $pendingOrders = [];
        $history = [];
        $warehouses = $this->repo->getWarehouses();

        if ($activeTab === 'history') {
            $history = $this->repo->getAllocationHistory([
                'keyword'   => $keyword,
                'maKho'     => $maKho,
                'trangThai' => $trangThai,
            ]);
        } else {
            $pendingOrders = $this->repo->getPendingOrders($keyword);
        }

        $canCreate = Auth::can(self::MODULE, 'create');

        $this->render('phan_bo/index', [
            'pageTitle'     => 'Phân bổ số lượng tồn kho',
            'activeTab'     => $activeTab,
            'keyword'       => $keyword,
            'maKho'         => $maKho,
            'trangThai'     => $trangThai,
            'pendingOrders' => $pendingOrders,
            'history'       => $history,
            'warehouses'    => $warehouses,
            'canCreate'     => $canCreate,
        ]);
    }

    /**
     * Màn hình tạo phiếu phân bổ (Mẫu 04: Wizard 4 bước)
     */
    public function create(): void
    {
        Auth::requirePermission(self::MODULE, 'create');

        $currentUser = Auth::user();
        try {
            $pendingOrders = $this->repo->getPendingOrders();
            $warehouses = $this->repo->getWarehouses();
            if (empty($warehouses)) {
                Flash::error(Messages::PB_EXC_4_1_LOAD_FAILED);
            }
        } catch (\Throwable $e) {
            error_log("Lỗi tải thông tin kho: " . $e->getMessage());
            $pendingOrders = [];
            $warehouses = [];
            Flash::error(Messages::PB_EXC_4_1_LOAD_FAILED);
        }

        $selectedOrder = $this->get('order_id') ?? $this->post('maLenhSX') ?? '';
        $selectedKho = $this->get('kho_id') ?? $this->post('maKhoNguon') ?? '';
        $ghiChu = $this->post('ghiChu', '');

        // Danh sách NVL cần cấp của lệnh được chọn
        $orderNeeds = !empty($selectedOrder) ? $this->repo->getNVLNeedsForOrder($selectedOrder) : [];
        $orderHeader = !empty($selectedOrder) ? $this->repo->getOrderHeader($selectedOrder) : null;

        // Danh sách lô khả dụng cho các NVL cần cấp trong kho đã chọn
        $availableLotsBySku = [];
        if (!empty($selectedKho) && !empty($orderNeeds)) {
            foreach ($orderNeeds as $nvl) {
                if ($nvl['soLuongConLai'] > 0) {
                    $availableLotsBySku[$nvl['maSKU']] = $this->repo->getAvailableLotsForSKU($nvl['maSKU'], $selectedKho);
                }
            }
        }

        $currentStep = 1;
        if (!empty($selectedOrder)) {
            $currentStep = 2;
        }
        if (!empty($selectedOrder) && !empty($selectedKho)) {
            $currentStep = 3;
        }

        // Xử lý POST submit
        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->redirect('?route=phan-bo/create');
                return;
            }

            $formAction = $this->post('form_action', 'save');
            $rawAllocations = $this->post('allocations', []);

            // Chuẩn hóa dữ liệu phân bổ từ POST
            $allocations = [];
            if (is_array($rawAllocations)) {
                foreach ($rawAllocations as $item) {
                    $qty = isset($item['soLuongPhanBo']) ? (float)$item['soLuongPhanBo'] : 0.0;
                    if ($qty > 0) {
                        $allocations[] = [
                            'maLo'          => $item['maLo'] ?? '',
                            'maViTri'       => $item['maViTri'] ?? '',
                            'maSKU'         => $item['maSKU'] ?? '',
                            'soLuongPhanBo' => $qty,
                        ];
                    }
                }
            }

            // Nếu người dùng bấm nút "Gợi ý tự động" (FEFO)
            if ($formAction === 'suggest') {
                $suggested = $this->service->suggestFEFO($selectedOrder, $selectedKho);
                Flash::info('Đã tạo gợi ý phân bổ tự động theo nguyên tắc FEFO (Hạn sử dụng sớm xếp trước).');

                $this->render('phan_bo/create', [
                    'pageTitle'          => 'Phân bổ số lượng',
                    'pendingOrders'      => $pendingOrders,
                    'warehouses'         => $warehouses,
                    'selectedOrder'      => $selectedOrder,
                    'orderHeader'        => $orderHeader,
                    'orderNeeds'         => $orderNeeds,
                    'selectedKho'        => $selectedKho,
                    'availableLotsBySku' => $availableLotsBySku,
                    'suggestedAlloc'     => $suggested,
                    'ghiChu'             => $ghiChu,
                    'currentStep'        => 3,
                ]);
                return;
            }

            // Nếu thực hiện lưu phân bổ (save)
            if ($formAction === 'save') {
                $validation = $this->service->validateAllocation($selectedOrder, $selectedKho, $allocations);

                if (!$validation['valid']) {
                    Flash::error(implode('<br>', $validation['errors']));
                    $this->render('phan_bo/create', [
                        'pageTitle'          => 'Phân bổ số lượng',
                        'pendingOrders'      => $pendingOrders,
                        'warehouses'         => $warehouses,
                        'selectedOrder'      => $selectedOrder,
                        'orderHeader'        => $orderHeader,
                        'orderNeeds'         => $orderNeeds,
                        'selectedKho'        => $selectedKho,
                        'availableLotsBySku' => $availableLotsBySku,
                        'suggestedAlloc'     => $allocations,
                        'ghiChu'             => $ghiChu,
                        'currentStep'        => 3,
                    ]);
                    return;
                }

                try {
                    $code = $this->repo->createAllocation(
                        $selectedOrder,
                        $selectedKho,
                        $allocations,
                        $currentUser['maNhanVien'] ?? 'NV002',
                        $ghiChu,
                        $currentUser['maTaiKhoan'] ?? null
                    );

                    Flash::success(Messages::PB_SUCCESS);
                    $this->redirect('?route=phan-bo/index&tab=history');
                    return;
                } catch (\Throwable $e) {
                    error_log("Không thể lưu thông tin phân bổ: " . $e->getMessage());
                    Flash::error(Messages::PB_EXC_11_1_SAVE_FAILED);
                }
            }
        }

        $this->render('phan_bo/create', [
            'pageTitle'          => 'Phân bổ số lượng',
            'pendingOrders'      => $pendingOrders,
            'warehouses'         => $warehouses,
            'selectedOrder'      => $selectedOrder,
            'orderHeader'        => $orderHeader,
            'orderNeeds'         => $orderNeeds,
            'selectedKho'        => $selectedKho,
            'availableLotsBySku' => $availableLotsBySku,
            'suggestedAlloc'     => [],
            'ghiChu'             => $ghiChu,
            'currentStep'        => $currentStep,
        ]);
    }

    /**
     * Màn hình chi tiết phiếu phân bổ (Mẫu 02: Modal chi tiết chỉ đọc kèm KPI)
     */
    public function detail(): void
    {
        Auth::requirePermission(self::MODULE, 'detail');

        $id = $this->get('id', '');
        if (empty($id)) {
            Flash::warning('Mã phiếu phân bổ không hợp lệ.');
            $this->redirect('?route=phan-bo/index&tab=history');
            return;
        }

        $allocation = $this->repo->getAllocationDetail($id);
        if (!$allocation) {
            Flash::error("Không tìm thấy phiếu phân bổ {$id}.");
            $this->redirect('?route=phan-bo/index&tab=history');
            return;
        }

        $this->render('phan_bo/detail_modal', [
            'pageTitle'  => "Chi tiết phiếu phân bổ {$id}",
            'allocation' => $allocation,
        ]);
    }
}
