<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

/**
 * Phân hệ: Quản lý tài khoản
 * Quyền xem: QTV
 * Quyền ghi: QTV
 */
class TaiKhoanController extends Controller
{
    private const MODULE = 'tai-khoan';

    public function index(): void
    {
        Auth::requirePermission(self::MODULE, 'index');

        $perms = Auth::getPermissions();
        $moduleDef = $perms['modules'][self::MODULE] ?? [];

        $this->render('placeholders/developing', [
            'pageTitle'   => 'Quản lý tài khoản',
            'moduleKey'   => self::MODULE,
            'moduleName'  => 'Quản lý tài khoản',
            'description' => 'Cấp phát, phân quyền và khóa tài khoản người dùng hệ thống',
            'groupName'   => 'Hệ thống',
            'moduleDef'   => $moduleDef,
        ]);
    }
}

