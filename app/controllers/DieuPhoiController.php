<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Flash;
use App\Config\Messages;
use App\Repositories\DieuPhoiRepository;
use App\Services\DieuPhoiService;

/**
 * Controller: Điều phối nhập kho (Prompt 6 - QLK, UC 2.5.3)
 * Quyền GHI: QLK
 * Quyền XEM: QLK, BGD, QC
 */
class DieuPhoiController extends Controller
{
    private const MODULE = 'dieu-phoi';
    private DieuPhoiRepository $repo;
    private DieuPhoiService $service;

    public function __construct()
    {
        $this->repo = new DieuPhoiRepository();
        $this->service = new DieuPhoiService($this->repo);
    }

    /**
     * Danh sách phiếu nhập kho (Mẫu 01: Tra cứu / Danh sách 2 Tab)
     */
    public function index(): void
    {
        Auth::requirePermission(self::MODULE, 'index');

        $activeTab = $this->get('tab', 'pending');
        $keyword = $this->get('keyword', '');
        $maKho = $this->get('maKho', '');
        $trangThai = $this->get('trangThai', '');

        $pendingList = [];
        $allList = [];
        $warehouses = $this->repo->getWarehouses();

        if ($activeTab === 'all') {
            $allList = $this->repo->getAllInboundRequests([
                'keyword'   => $keyword,
                'maKho'     => $maKho,
                'trangThai' => $trangThai,
            ]);
        } else {
            $pendingList = $this->repo->getPendingInboundRequests($keyword, $maKho);
        }

        $canCreate = Auth::can(self::MODULE, 'create');

        $this->render('dieu_phoi/index', [
            'pageTitle'   => 'Điều phối nhập kho',
            'activeTab'   => $activeTab,
            'keyword'     => $keyword,
            'maKho'       => $maKho,
            'trangThai'   => $trangThai,
            'pendingList' => $pendingList,
            'allList'     => $allList,
            'warehouses'  => $warehouses,
            'canCreate'   => $canCreate,
        ]);
    }

    /**
     * Tạo lệnh điều phối nhập kho (Mẫu 04: Wizard 4 bước)
     */
    public function create(): void
    {
        Auth::requirePermission(self::MODULE, 'create');

        $currentUser = Auth::user();
        $pendingList = $this->repo->getPendingInboundRequests();
        $warehouses = $this->repo->getWarehouses();
        $workers = $this->repo->getWorkersList();

        $selectedReqId = $this->get('req_id') ?? $this->post('maPhieuNhap') ?? '';
        $inboundReq = null;
        if (!empty($selectedReqId)) {
            try {
                $inboundReq = $this->repo->getInboundRequest($selectedReqId);
                if (!$inboundReq) {
                    Flash::error(Messages::DP_EXC_4_1_LOAD_FAILED);
                }
            } catch (\Throwable $e) {
                error_log("Lỗi tải thông tin phiếu nhập kho: " . $e->getMessage());
                Flash::error(Messages::DP_EXC_4_1_LOAD_FAILED);
            }
        }
        $selectedKho = $this->get('kho_id') ?? $this->post('maKho') ?? ($inboundReq['maKho'] ?? '');
        $targetWh = !empty($selectedKho) ? $this->repo->getWarehouse($selectedKho) : null;

        $availablePositions = [];
        if (!empty($selectedKho) && !empty($inboundReq)) {
            $loaiHang = $inboundReq['items'][0]['loaiHang'] ?? null;
            $availablePositions = $this->repo->getAvailablePositionsForWarehouse($selectedKho, $loaiHang);
        }

        $currentStep = 1;
        if (!empty($selectedReqId)) {
            $currentStep = 2;
        }
        if (!empty($selectedReqId) && !empty($selectedKho)) {
            $currentStep = 3;
        }

        // Xử lý POST
        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->redirect('?route=dieu-phoi/create');
                return;
            }

            $formAction = $this->post('form_action', 'save');
            $rawLines = $this->post('allocations', []);
            $maNVK = $this->post('maNhanVienThucHien', '');
            $ghiChu = $this->post('ghiChu', '');

            // Nếu người dùng bấm "Gợi ý tự động"
            if ($formAction === 'suggest') {
                $suggestRes = $this->service->suggestPositions($selectedReqId, $selectedKho);
                if (!$suggestRes['valid']) {
                    Flash::error(implode('<br>', $suggestRes['errors']));
                } else {
                    Flash::info('Đã tạo gợi ý vị trí lưu kho tự động thành công.');
                }

                $this->render('dieu_phoi/create', [
                    'pageTitle'          => 'Lập lệnh điều phối nhập kho',
                    'pendingList'        => $pendingList,
                    'warehouses'         => $warehouses,
                    'workers'            => $workers,
                    'selectedReqId'      => $selectedReqId,
                    'inboundReq'         => $inboundReq,
                    'selectedKho'        => $selectedKho,
                    'targetWh'           => $targetWh,
                    'availablePositions' => $availablePositions,
                    'suggestedPositions' => $suggestRes['positions'] ?? [],
                    'maNVK'              => $maNVK,
                    'ghiChu'             => $ghiChu,
                    'currentStep'        => 3,
                ]);
                return;
            }

            // Nếu lưu chính thức lệnh điều phối (save)
            if ($formAction === 'save') {
                $allocLines = [];
                if (is_array($rawLines)) {
                    foreach ($rawLines as $line) {
                        $qty = isset($line['soLuongDieuPhoi']) ? (float)$line['soLuongDieuPhoi'] : 0.0;
                        if ($qty > 0) {
                            $allocLines[] = [
                                'maLo'            => $line['maLo'] ?? '',
                                'maSKU'           => $line['maSKU'] ?? '',
                                'maViTri'         => $line['maViTri'] ?? '',
                                'soLuongDieuPhoi' => $qty,
                            ];
                        }
                    }
                }

                $validation = $this->service->validateDispatchPlan($selectedReqId, $selectedKho, $allocLines, $maNVK);

                if (!$validation['valid']) {
                    Flash::error(implode('<br>', $validation['errors']));
                    $this->render('dieu_phoi/create', [
                        'pageTitle'          => 'Lập lệnh điều phối nhập kho',
                        'pendingList'        => $pendingList,
                        'warehouses'         => $warehouses,
                        'workers'            => $workers,
                        'selectedReqId'      => $selectedReqId,
                        'inboundReq'         => $inboundReq,
                        'selectedKho'        => $selectedKho,
                        'targetWh'           => $targetWh,
                        'availablePositions' => $availablePositions,
                        'suggestedPositions' => $allocLines,
                        'maNVK'              => $maNVK,
                        'ghiChu'             => $ghiChu,
                        'currentStep'        => 3,
                    ]);
                    return;
                }

                try {
                    $code = $this->repo->createDispatch(
                        $selectedReqId,
                        $selectedKho,
                        $allocLines,
                        $currentUser['maNhanVien'] ?? 'NV002',
                        $maNVK,
                        $ghiChu,
                        $currentUser['maTaiKhoan'] ?? null
                    );

                    Flash::success(Messages::DP_SUCCESS);
                    $this->redirect('?route=dieu-phoi/index&tab=all');
                    return;
                } catch (\Throwable $e) {
                    error_log("Lỗi khi phát lệnh điều phối: " . $e->getMessage());
                    Flash::error(Messages::DP_EXC_16_1_DISPATCH_FAIL);
                }
            }
        }

        $this->render('dieu_phoi/create', [
            'pageTitle'          => 'Lập lệnh điều phối nhập kho',
            'pendingList'        => $pendingList,
            'warehouses'         => $warehouses,
            'workers'            => $workers,
            'selectedReqId'      => $selectedReqId,
            'inboundReq'         => $inboundReq,
            'selectedKho'        => $selectedKho,
            'targetWh'           => $targetWh,
            'availablePositions' => $availablePositions,
            'suggestedPositions' => [],
            'maNVK'              => '',
            'ghiChu'             => '',
            'currentStep'        => $currentStep,
        ]);
    }

    /**
     * Chi tiết phiếu nhập kho (Mẫu 02: Modal chi tiết chỉ đọc)
     */
    public function detail(): void
    {
        Auth::requirePermission(self::MODULE, 'detail');

        $id = $this->get('id', '');
        if (empty($id)) {
            Flash::warning('Mã phiếu nhập không hợp lệ.');
            $this->redirect('?route=dieu-phoi/index');
            return;
        }

        $req = $this->repo->getInboundRequest($id);
        if (!$req) {
            Flash::error("Không tìm thấy phiếu nhập kho {$id}.");
            $this->redirect('?route=dieu-phoi/index');
            return;
        }

        $this->render('dieu_phoi/detail_modal', [
            'pageTitle'  => "Chi tiết phiếu nhập {$id}",
            'inboundReq' => $req,
        ]);
    }
}
