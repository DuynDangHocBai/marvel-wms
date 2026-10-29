<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Flash;
use App\Config\Messages;
use App\Repositories\DeNghiMuaRepository;
use App\Services\DeNghiMuaService;

/**
 * Controller: Lập và theo dõi đề nghị mua NVL (Prompt 3 - LKH)
 * Quyền GHI: LKH
 * Quyền XEM: LKH, QLK, BGD
 */
class DeNghiMuaController extends Controller
{
    private const MODULE = 'de-nghi-mua';
    private DeNghiMuaRepository $repo;
    private DeNghiMuaService $service;

    public function __construct()
    {
        $this->repo = new DeNghiMuaRepository();
        $this->service = new DeNghiMuaService($this->repo);
    }

    /**
     * Màn hình danh sách đề nghị mua (Mẫu 01: Tra cứu / Danh sách)
     */
    public function index(): void
    {
        Auth::requirePermission(self::MODULE, 'index');

        $filters = [
            'keyword'   => $this->get('keyword', ''),
            'trangThai' => $this->get('trangThai', ''),
            'maKeHoach' => $this->get('maKeHoach', ''),
        ];

        $proposals = $this->repo->getAllProposals($filters);
        $approvedPlans = $this->repo->getApprovedPlans();
        $canCreate = Auth::can(self::MODULE, 'create');

        $this->render('de_nghi_mua/index', [
            'pageTitle'     => 'Đề nghị mua nguyên vật liệu',
            'proposals'     => $proposals,
            'filters'       => $filters,
            'approvedPlans' => $approvedPlans,
            'canCreate'     => $canCreate,
        ]);
    }

    /**
     * Màn hình lập đề nghị mua NVL (Mẫu 03: Form lập chứng từ)
     */
    public function create(): void
    {
        Auth::requirePermission(self::MODULE, 'create');

        $currentUser = Auth::user();
        $approvedPlans = $this->repo->getApprovedPlans();
        $allMaterials = $this->repo->getAllMaterials();

        $selectedPlanId = $this->get('plan_id') ?? $this->post('maKeHoach') ?? '';
        $planDetail = null;
        $calculatedItems = [];

        if (!empty($selectedPlanId)) {
            $calc = $this->service->calculateProposalFromPlan($selectedPlanId);
            if ($calc['valid']) {
                $planDetail = $calc['plan'];
                $calculatedItems = $calc['items'];
            } else {
                Flash::error(implode('<br>', $calc['errors']));
            }
        }

        // Xử lý POST submit
        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->redirect('?route=de-nghi-mua/create');
                return;
            }

            $rawItems = $this->post('items', []);
            $formData = [
                'maKeHoach'   => $selectedPlanId,
                'ngayCanHang' => $this->post('ngayCanHang', date('Y-m-d', strtotime('+7 days'))),
                'lyDo'        => $this->post('lyDo', ''),
                'items'       => [],
            ];

            if (is_array($rawItems)) {
                foreach ($rawItems as $it) {
                    $qty = isset($it['soLuongMua']) && $it['soLuongMua'] !== '' ? (float)$it['soLuongMua'] : 0.0;
                    $price = (float)($it['giaNhap'] ?? 0);
                    $formData['items'][] = [
                        'maSKU'         => $it['maSKU'] ?? '',
                        'tenHang'       => $it['tenHang'] ?? '',
                        'donViTinh'     => $it['donViTinh'] ?? '',
                        'nhuCauSX'      => (float)($it['nhuCauSX'] ?? 0),
                        'tonKhaDung'    => (float)($it['tonKhaDung'] ?? 0),
                        'tonToiThieu'   => (float)($it['tonToiThieu'] ?? 0),
                        'hangDangVe'    => (float)($it['hangDangVe'] ?? 0),
                        'soLuongDeXuat' => (float)($it['soLuongDeXuat'] ?? 0),
                        'soLuongMua'    => $qty,
                        'giaNhap'       => $price,
                        'thanhTien'     => $qty * $price,
                        'ngoaiKeHoach'  => !empty($it['ngoaiKeHoach']) ? 1 : 0,
                        'lyDoRieng'     => $it['lyDoRieng'] ?? '',
                    ];
                }
            }

            $validation = $this->service->validateProposalForm($formData);

            if (!$validation['valid']) {
                Flash::error(implode('<br>', $validation['errors']));
                $this->render('de_nghi_mua/create', [
                    'pageTitle'       => 'Lập đề nghị mua NVL mới',
                    'approvedPlans'   => $approvedPlans,
                    'allMaterials'    => $allMaterials,
                    'selectedPlanId'  => $selectedPlanId,
                    'planDetail'      => $planDetail,
                    'calculatedItems' => $calculatedItems,
                    'formData'        => $formData,
                    'currentUser'     => $currentUser,
                ]);
                return;
            }

            try {
                $code = $this->repo->createProposal(
                    $formData,
                    $currentUser['maNhanVien'] ?? 'NV001',
                    $currentUser['maTaiKhoan'] ?? null
                );

                Flash::success(Messages::DNM_SUCCESS);
                $this->redirect('?route=de-nghi-mua/index');
                return;
            } catch (\Throwable $e) {
                error_log("Lỗi khi lưu đề nghị mua: " . $e->getMessage());
                Flash::error(Messages::DNM_EXC_SAVE_FAILED);
            }
        }

        $this->render('de_nghi_mua/create', [
            'pageTitle'       => 'Lập đề nghị mua NVL mới',
            'approvedPlans'   => $approvedPlans,
            'allMaterials'    => $allMaterials,
            'selectedPlanId'  => $selectedPlanId,
            'planDetail'      => $planDetail,
            'calculatedItems' => $calculatedItems,
            'formData'        => [
                'ngayCanHang' => date('Y-m-d', strtotime('+7 days')),
                'lyDo'        => '',
                'items'       => [],
            ],
            'currentUser'     => $currentUser,
        ]);
    }

    /**
     * Màn hình chi tiết đề nghị mua (Mẫu 02: Modal chi tiết chỉ đọc)
     */
    public function detail(): void
    {
        Auth::requirePermission(self::MODULE, 'detail');

        $id = $this->get('id', '');
        if (empty($id)) {
            Flash::warning('Mã đề nghị mua không hợp lệ.');
            $this->redirect('?route=de-nghi-mua/index');
            return;
        }

        $proposal = $this->repo->getProposalDetail($id);
        if (!$proposal) {
            Flash::error("Không tìm thấy đề nghị mua {$id}.");
            $this->redirect('?route=de-nghi-mua/index');
            return;
        }

        $canCancel = Auth::can(self::MODULE, 'cancel');

        $this->render('de_nghi_mua/detail_modal', [
            'pageTitle' => "Chi tiết đề nghị mua {$id}",
            'proposal'  => $proposal,
            'canCancel' => $canCancel,
        ]);
    }

    /**
     * Hủy đề nghị mua khi trạng thái còn CHO_DUYET
     */
    public function cancel(): void
    {
        Auth::requirePermission(self::MODULE, 'cancel');

        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('?route=de-nghi-mua/index');
            return;
        }

        $currentUser = Auth::user();
        $id = $this->post('maDeNghi', '');
        $lyDo = $this->post('lyDoHuy', 'Người lập hủy đề nghị');

        if (empty($id)) {
            Flash::error('Mã đề nghị không hợp lệ.');
            $this->redirect('?route=de-nghi-mua/index');
            return;
        }

        $res = $this->service->cancelProposal(
            $id,
            $currentUser['maNhanVien'] ?? 'NV001',
            $lyDo,
            $currentUser['maTaiKhoan'] ?? null
        );

        if ($res['success']) {
            Flash::success("Đã hủy đề nghị mua nguyên vật liệu <strong>{$id}</strong> (Trạng thái: Đã hủy).");
        } else {
            Flash::warning($res['error'] ?? "Không thể hủy đề nghị {$id}.");
        }

        $this->redirect('?route=de-nghi-mua/index');
    }
}
