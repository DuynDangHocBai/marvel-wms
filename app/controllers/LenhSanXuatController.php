<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Flash;
use App\Config\Messages;
use App\Repositories\LenhSanXuatRepository;
use App\Services\LenhSanXuatService;

/**
 * Controller: Tạo lệnh sản xuất bánh kẹo theo BOM (Prompt 4 - LKH)
 * Quyền GHI: LKH
 * Quyền XEM: LKH, QLK, BGD
 */
class LenhSanXuatController extends Controller
{
    private const MODULE = 'lenh-san-xuat';
    private LenhSanXuatRepository $repo;
    private LenhSanXuatService $service;

    public function __construct()
    {
        $this->repo = new LenhSanXuatRepository();
        $this->service = new LenhSanXuatService($this->repo);
    }

    /**
     * Danh sách lệnh sản xuất (Mẫu 01: Tra cứu / Danh sách có cột thanh % tiến độ)
     */
    public function index(): void
    {
        Auth::requirePermission(self::MODULE, 'index');

        $filters = [
            'keyword'   => $this->get('keyword', ''),
            'trangThai' => $this->get('trangThai', ''),
            'maXuong'   => $this->get('maXuong', ''),
        ];

        $orders = $this->repo->getAllOrders($filters);
        $xuongList = $this->repo->getXuongList();
        $canCreate = Auth::can(self::MODULE, 'create');

        $this->render('lenh_san_xuat/index', [
            'pageTitle' => 'Danh sách lệnh sản xuất',
            'orders'    => $orders,
            'filters'   => $filters,
            'xuongList' => $xuongList,
            'canCreate' => $canCreate,
        ]);
    }

    /**
     * Tạo lệnh sản xuất theo Wizard 4 bước (Mẫu 04)
     */
    public function create(): void
    {
        Auth::requirePermission(self::MODULE, 'create');

        $approvedPlans = $this->repo->getApprovedPlans();
        $xuongList = $this->repo->getXuongList();
        $currentUser = Auth::user();

        $selectedPlanId = $this->get('plan_id') ?? $this->post('maKeHoach') ?? '';
        $planDetail = !empty($selectedPlanId) ? $this->repo->getPlanDetail($selectedPlanId) : null;

        // Nếu POST
        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->redirect('?route=lenh-san-xuat/create');
                return;
            }

            $formAction = $this->post('form_action', 'issue'); // 'check_bom', 'save_draft', 'issue'
            $rawItems = $this->post('items', []);

            $formData = [
                'maKeHoach'     => $selectedPlanId,
                'maXuong'       => $this->post('maXuong', ''),
                'ngayBatDau'    => $this->post('ngayBatDau', date('Y-m-d')),
                'ngayHoanThanh' => $this->post('ngayHoanThanh', date('Y-m-d', strtotime('+3 days'))),
                'ghiChu'        => $this->post('ghiChu', ''),
                'items'         => [],
            ];

            // Chuẩn hóa danh sách mặt hàng từ POST
            if (is_array($rawItems)) {
                foreach ($rawItems as $sku => $qty) {
                    if (is_numeric($qty) && (float)$qty > 0) {
                        $formData['items'][] = [
                            'maSKU'       => $sku,
                            'soLuongGiao' => (float)$qty,
                        ];
                    }
                }
            }

            $validation = $this->service->validateOrderForm($formData);

            if (!$validation['valid']) {
                Flash::error(implode('<br>', $validation['errors']));
                $this->render('lenh_san_xuat/create', [
                    'pageTitle'      => 'Tạo lệnh sản xuất',
                    'approvedPlans'  => $approvedPlans,
                    'xuongList'      => $xuongList,
                    'selectedPlanId' => $selectedPlanId,
                    'planDetail'     => $planDetail,
                    'formData'       => $formData,
                    'bomResult'      => null,
                    'currentStep'    => 2,
                ]);
                return;
            }

            // Tính toán nhu cầu NVL (BOM)
            $bomResult = $this->service->calculateNVLNeeds($formData['items']);

            // Nếu người dùng bấm "Xác định nhu cầu nguyên liệu"
            if ($formAction === 'check_bom') {
                Flash::info('Đã đối chiếu tồn kho khả dụng thành công.');
                $this->render('lenh_san_xuat/create', [
                    'pageTitle'      => 'Tạo lệnh sản xuất',
                    'approvedPlans'  => $approvedPlans,
                    'xuongList'      => $xuongList,
                    'selectedPlanId' => $selectedPlanId,
                    'planDetail'     => $planDetail,
                    'formData'       => $formData,
                    'bomResult'      => $bomResult,
                    'currentStep'    => 3,
                ]);
                return;
            }

            // Nếu phát lệnh chính thức (DA_PHAT)
            if ($formAction === 'issue') {
                if (!$bomResult['canIssue']) {
                    Flash::error('Không thể phát lệnh sản xuất do tồn kho khả dụng không đủ nguyên vật liệu!');
                    $this->render('lenh_san_xuat/create', [
                        'pageTitle'      => 'Tạo lệnh sản xuất',
                        'approvedPlans'  => $approvedPlans,
                        'xuongList'      => $xuongList,
                        'selectedPlanId' => $selectedPlanId,
                        'planDetail'     => $planDetail,
                        'formData'       => $formData,
                        'bomResult'      => $bomResult,
                        'currentStep'    => 3,
                    ]);
                    return;
                }

                try {
                    $code = $this->repo->createOrder(
                        $formData,
                        'DA_PHAT',
                        $currentUser['maNhanVien'] ?? 'NV001',
                        $currentUser['maTaiKhoan'] ?? null
                    );

                    Flash::success(Messages::LSX_SUCCESS_PREFIX . $code);
                    $this->redirect('?route=lenh-san-xuat/index');
                    return;
                } catch (\Throwable $e) {
                    error_log('Lỗi khi phát lệnh sản xuất: ' . $e->getMessage());
                    Flash::error(Messages::LSX_EXC_SAVE_FAILED);
                }
            }

            // Nếu lưu bản nháp (CHO_PHAT / BAN_NHAP)
            if ($formAction === 'save_draft') {
                try {
                    $code = $this->repo->createOrder(
                        $formData,
                        'CHO_PHAT',
                        $currentUser['maNhanVien'] ?? 'NV001',
                        $currentUser['maTaiKhoan'] ?? null
                    );

                    Flash::success(sprintf(Messages::LSX_DRAFT_SUCCESS, $code));
                    $this->redirect('?route=lenh-san-xuat/index');
                    return;
                } catch (\Throwable $e) {
                    error_log('Lỗi khi lưu bản nháp lệnh sản xuất: ' . $e->getMessage());
                    Flash::error(Messages::LSX_EXC_SAVE_FAILED);
                }
            }
        }

        $this->render('lenh_san_xuat/create', [
            'pageTitle'      => 'Tạo lệnh sản xuất',
            'approvedPlans'  => $approvedPlans,
            'xuongList'      => $xuongList,
            'selectedPlanId' => $selectedPlanId,
            'planDetail'     => $planDetail,
            'formData'       => [
                'maXuong'       => $this->get('maXuong', $planDetail['maXuong'] ?? ''),
                'ngayBatDau'    => $this->get('ngayBatDau', $planDetail['ngayBatDau'] ?? date('Y-m-d')),
                'ngayHoanThanh' => $this->get('ngayHoanThanh', $planDetail['ngayHoanThanh'] ?? date('Y-m-d', strtotime('+3 days'))),
                'ghiChu'        => $this->get('ghiChu', ''),
                'items'         => [],
            ],
            'bomResult'      => null,
            'currentStep'    => !empty($planDetail) ? 2 : 1,
        ]);
    }

    /**
     * Chi tiết lệnh sản xuất (Mẫu 02: 3 thẻ KPI & tiến độ phân bổ NVL)
     */
    public function detail(): void
    {
        Auth::requirePermission(self::MODULE, 'detail');

        $maLenhSX = $this->get('id', '');
        if (empty($maLenhSX)) {
            Flash::warning('Mã lệnh sản xuất không hợp lệ.');
            $this->redirect('?route=lenh-san-xuat/index');
            return;
        }

        $order = $this->repo->getOrderDetail($maLenhSX);
        if (!$order) {
            Flash::error("Không tìm thấy lệnh sản xuất {$maLenhSX}.");
            $this->redirect('?route=lenh-san-xuat/index');
            return;
        }

        $this->render('lenh_san_xuat/detail_modal', [
            'pageTitle' => "Chi tiết lệnh sản xuất {$maLenhSX}",
            'order'     => $order,
        ]);
    }
}
