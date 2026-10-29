<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Flash;
use App\Core\Database;
use App\Config\Messages;
use App\Repositories\KiemKeRepository;
use App\Services\KiemKeService;
use PDO;

/**
 * Controller: Cập nhật chênh lệch kiểm kê (UC 2.6.3)
 * Quyền GHI: QLK
 * Quyền XEM: QLK, BGD
 */
class KiemKeController extends Controller
{
    private const MODULE = 'kiem-ke';
    private KiemKeRepository $repo;
    private KiemKeService $service;

    public function __construct()
    {
        $this->repo = new KiemKeRepository();
        $this->service = new KiemKeService();
    }

    /**
     * Danh sách biên bản kiểm kê (Giao diện theo Mẫu 01: Tra cứu tiến độ / danh sách)
     */
    public function index(): void
    {
        Auth::requirePermission(self::MODULE, 'index');

        $filters = [
            'keyword'   => $this->get('keyword', ''),
            'maKho'     => $this->get('maKho', ''),
            'trangThai' => $this->get('trangThai', ''),
            'tuNgay'    => $this->get('tuNgay', ''),
            'denNgay'   => $this->get('denNgay', ''),
        ];

        $items = $this->repo->getAll($filters);

        // Danh sách kho để đổ vào dropdown lọc
        $db = Database::getConnection();
        $khoList = $db->query("SELECT maKho, tenKho FROM kho WHERE trangThai='HOAT_DONG' ORDER BY maKho ASC")->fetchAll(PDO::FETCH_ASSOC);

        $canWrite = Auth::can(self::MODULE, 'update');

        $this->render('kiem_ke/index', [
            'pageTitle' => 'Cập nhật chênh lệch kiểm kê',
            'items'     => $items,
            'filters'   => $filters,
            'khoList'   => $khoList,
            'canWrite'  => $canWrite,
        ]);
    }

    /**
     * Cập nhật chênh lệch kiểm kê (Giao diện theo Mẫu 03: Form lập chứng từ)
     */
    public function update(): void
    {
        Auth::requirePermission(self::MODULE, 'update');

        $maBienBan = $this->get('id') ?? $this->post('maBienBan') ?? '';
        if (empty($maBienBan)) {
            Flash::warning('Vui lòng chọn một biên bản kiểm kê để cập nhật chênh lệch.');
            $this->redirect('?route=kiem-ke/index');
            return;
        }

        $record = $this->repo->findByMa($maBienBan);
        if (!$record) {
            Flash::error("Không tìm thấy biên bản kiểm kê: {$maBienBan}.");
            $this->redirect('?route=kiem-ke/index');
            return;
        }

        // RÀNG BUỘC: Biên bản không ở trạng thái DANG_KIEM_KE chỉ được xem
        if ($record['trangThai'] !== 'DANG_KIEM_KE') {
            Flash::warning("Biên bản {$maBienBan} đang ở trạng thái \"{$record['trangThai']}\", chỉ được xem thông tin.");
            $this->redirect("?route=kiem-ke/detail&id={$maBienBan}");
            return;
        }

        $calculated = $this->service->calculate($record);

        // Xử lý POST (Tính chênh lệch hoặc Lưu kết quả)
        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->redirect("?route=kiem-ke/update&id={$maBienBan}");
                return;
            }

            $formAction = $this->post('form_action', 'save');

            if ($formAction === 'calculate') {
                Flash::info('Đã tính toán lại số liệu chênh lệch.');
                $this->render('kiem_ke/update', [
                    'pageTitle'  => 'Cập nhật chênh lệch kiểm kê',
                    'record'     => $record,
                    'calculated' => $calculated,
                ]);
                return;
            }

            if ($formAction === 'save') {
                if (!$calculated['valid']) {
                    Flash::error('Không thể lưu do số liệu chưa hợp lệ: ' . implode(' ', $calculated['errors']));
                    $this->render('kiem_ke/update', [
                        'pageTitle'  => 'Cập nhật chênh lệch kiểm kê',
                        'record'     => $record,
                        'calculated' => $calculated,
                    ]);
                    return;
                }

                try {
                    $user = Auth::user();
                    $this->repo->saveCalculated(
                        $maBienBan,
                        $calculated,
                        $user['maNhanVien'] ?? 'NV002',
                        $user['maTaiKhoan'] ?? null
                    );

                    Flash::success(Messages::KK_SUCCESS);
                    $this->redirect('?route=kiem-ke/index');
                    return;
                } catch (\Throwable $e) {
                    error_log('Lỗi khi lưu kết quả kiểm kê: ' . $e->getMessage());
                    Flash::error(Messages::KK_EXC_10_1_SAVE_FAILED);
                }
            }
        }

        $this->render('kiem_ke/update', [
            'pageTitle'  => 'Cập nhật chênh lệch kiểm kê',
            'record'     => $record,
            'calculated' => $calculated,
        ]);
    }

    /**
     * Alias create -> update để tương thích với whitelist ban đầu
     */
    public function create(): void
    {
        $this->update();
    }

    /**
     * Xem chi tiết biên bản kiểm kê (Giao diện theo Mẫu 02: Modal xem chi tiết chỉ đọc kèm KPI)
     */
    public function detail(): void
    {
        Auth::requirePermission(self::MODULE, 'detail');

        $maBienBan = $this->get('id', '');
        if (empty($maBienBan)) {
            Flash::warning('Mã biên bản kiểm kê không hợp lệ.');
            $this->redirect('?route=kiem-ke/index');
            return;
        }

        $record = $this->repo->findByMa($maBienBan);
        if (!$record) {
            Flash::error("Không tìm thấy biên bản kiểm kê {$maBienBan}.");
            $this->redirect('?route=kiem-ke/index');
            return;
        }

        $calculated = $this->service->calculate($record);

        // Nếu là yêu cầu AJAX trả về JSON hoặc HTML modal
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            $this->render('kiem_ke/detail_modal', [
                'record'     => $record,
                'calculated' => $calculated,
            ], false);
            return;
        }

        $this->render('kiem_ke/detail_modal', [
            'pageTitle'  => "Chi tiết biên bản kiểm kê {$maBienBan}",
            'record'     => $record,
            'calculated' => $calculated,
        ]);
    }
}
