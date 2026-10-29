<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;

/**
 * Phân hệ: Nhật ký hệ thống
 * Quyền xem: QTV, BGD
 * Quyền ghi: Không có (chỉ đọc)
 */
class NhatKyController extends Controller
{
    private const MODULE = 'nhat-ky';

    public function index(): void
    {
        Auth::requirePermission(self::MODULE, 'index');

        $perms = Auth::getPermissions();
        $moduleDef = $perms['modules'][self::MODULE] ?? [];

        $this->render('placeholders/developing', [
            'pageTitle'   => 'Nhật ký hệ thống',
            'moduleKey'   => self::MODULE,
            'moduleName'  => 'Nhật ký hệ thống',
            'description' => 'Theo dõi lịch sử truy cập, thay đổi dữ liệu và nhật ký bảo mật',
            'groupName'   => 'Hệ thống',
            'moduleDef'   => $moduleDef,
        ]);
    }
}

