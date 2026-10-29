<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Flash;
use App\Config\Messages;
use App\Repositories\HangLoiRepository;
use App\Services\HangLoiService;

/**
 * Controller: Đề xuất xử lý hàng lỗi hỏng (UC 2.4.3)
 * Quyền GHI: QLK
 * Quyền XEM: QLK, QC, BGD
 */
class HangLoiController extends Controller
{
    private const MODULE = 'hang-loi';
    private HangLoiRepository $repo;
    private HangLoiService $service;

    public function __construct()
    {
        $this->repo = new HangLoiRepository();
        $this->service = new HangLoiService($this->repo);
    }

    /**
     * Danh sách đề xuất xử lý hàng lỗi hỏng (Mẫu 01: Tra cứu / Danh sách)
     */
    public function index(): void
    {
        Auth::requirePermission(self::MODULE, 'index');

        $filters = [
            'keyword'   => $this->get('keyword', ''),
            'phuongAn'  => $this->get('phuongAn', ''),
            'trangThai' => $this->get('trangThai', ''),
            'tuNgay'    => $this->get('tuNgay', ''),
            'denNgay'   => $this->get('denNgay', ''),
        ];

        $items = $this->repo->getAllDeXuat($filters);
        $canCreate = Auth::can(self::MODULE, 'create');

        $this->render('hang_loi/index', [
            'pageTitle' => 'Đề xuất xử lý hàng lỗi hỏng',
            'items'     => $items,
            'filters'   => $filters,
            'canCreate' => $canCreate,
        ]);
    }

    /**
     * Lập đề xuất xử lý hàng lỗi hỏng mới (Mẫu 03: Form lập chứng từ)
     */
    public function create(): void
    {
        Auth::requirePermission(self::MODULE, 'create');

        $items = $this->repo->getDanhSachLoi([], true);
        $currentUser = Auth::user();

        $input = [
            'selected' => $this->post('selected', []),
            'qty'      => $this->post('qty', []),
            'phuongAn' => $this->post('phuongAn', 'TIEU_HUY'),
            'lyDo'     => $this->post('lyDo', ''),
        ];

        $preview = null;

        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->redirect('?route=hang-loi/create');
                return;
            }

            $action = $this->post('action', 'save');
            $validated = $this->service->validate($input);

            if ($action === 'calculate') {
                if ($validated['valid']) {
                    Flash::info('Đã tổng hợp giá trị dự kiến thành công.');
                    $preview = $validated;
                } else {
                    Flash::error(implode('<br>', $validated['errors']));
                }

                $this->render('hang_loi/create', [
                    'pageTitle'   => 'Lập đề xuất xử lý hàng lỗi hỏng',
                    'items'       => $items,
                    'input'       => $input,
                    'preview'     => $preview,
                    'currentUser' => $currentUser,
                ]);
                return;
            }

            if ($action === 'save') {
                if (!$validated['valid']) {
                    Flash::error('Không thể lưu do thông tin chưa hợp lệ:<br>' . implode('<br>', $validated['errors']));
                    $this->render('hang_loi/create', [
                        'pageTitle'   => 'Lập đề xuất xử lý hàng lỗi hỏng',
                        'items'       => $items,
                        'input'       => $input,
                        'preview'     => null,
                        'currentUser' => $currentUser,
                    ]);
                    return;
                }

                try {
                    $maDeXuat = $this->repo->saveDeXuat(
                        $validated,
                        $currentUser['maNhanVien'] ?? 'NV002',
                        $currentUser['maTaiKhoan'] ?? null
                    );

                    Flash::success(Messages::HL_SUCCESS);
                    $this->redirect('?route=hang-loi/index');
                    return;
                } catch (\Throwable $e) {
                    error_log('Lỗi khi lưu đề xuất: ' . $e->getMessage());
                    Flash::error(Messages::HL_EXC_15_1_SAVE_FAILED);
                }
            }
        }

        $this->render('hang_loi/create', [
            'pageTitle'   => 'Lập đề xuất xử lý hàng lỗi hỏng',
            'items'       => $items,
            'input'       => $input,
            'preview'     => $preview,
            'currentUser' => $currentUser,
        ]);
    }

    /**
     * Chi tiết đề xuất xử lý hàng lỗi hỏng (Mẫu 02: Modal chi tiết chỉ đọc kèm KPI)
     */
    public function detail(): void
    {
        Auth::requirePermission(self::MODULE, 'detail');

        $maDeXuat = $this->get('id', '');
        if (empty($maDeXuat)) {
            Flash::warning('Mã đề xuất không hợp lệ.');
            $this->redirect('?route=hang-loi/index');
            return;
        }

        $proposal = $this->repo->findDeXuatByMa($maDeXuat);
        if (!$proposal) {
            Flash::error("Không tìm thấy đề xuất xử lý: {$maDeXuat}.");
            $this->redirect('?route=hang-loi/index');
            return;
        }

        // Nếu là yêu cầu AJAX
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            $this->render('hang_loi/detail_modal', [
                'proposal' => $proposal,
            ], false);
            return;
        }

        $this->render('hang_loi/detail_modal', [
            'pageTitle' => "Chi tiết đề xuất {$maDeXuat}",
            'proposal'  => $proposal,
        ]);
    }
}
