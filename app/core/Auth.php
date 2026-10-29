<?php

namespace App\Core;

use App\Repositories\NguoiDungRepository;
use App\Services\NhatKyService;

/**
 * Lớp quản lý phiên xác thực, đăng nhập, đăng xuất và phân quyền truy cập.
 */
class Auth
{
    private const SESSION_USER_KEY = 'auth_user';
    private const SESSION_TIME_KEY = 'last_activity';
    private const TIMEOUT_SECONDS  = 900; // 15 phút không thao tác

    private static ?array $permissionsConfig = null;

    /**
     * Khởi tạo cấu hình phiên làm việc an toàn.
     */
    public static function initSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Thiết lập bảo mật Cookie Session theo chuẩn OWASP & yêu cầu đề bài
            if (!headers_sent()) {
                ini_set('session.use_strict_mode', '1');
                ini_set('session.cookie_httponly', '1');
                ini_set('session.cookie_samesite', 'Lax');
                session_start();
            } else {
                @session_start();
            }
        }

        // Tự động kiểm tra timeout 15 phút nếu đã đăng nhập
        self::checkTimeout();
    }

    /**
     * Tự động đăng xuất sau 15 phút không thao tác (NFR-04).
     */
    public static function checkTimeout(): void
    {
        if (self::check()) {
            $lastActivity = $_SESSION[self::SESSION_TIME_KEY] ?? null;
            if ($lastActivity && (time() - $lastActivity > self::TIMEOUT_SECONDS)) {
                $user = self::user();
                unset($_SESSION[self::SESSION_USER_KEY]);
                unset($_SESSION[self::SESSION_TIME_KEY]);
                NhatKyService::ghi(
                    $user['maTaiKhoan'] ?? null,
                    'Xác thực',
                    'HET_PHIEN',
                    'Hết phiên làm việc do không thao tác sau 15 phút'
                );
                if (!headers_sent() && session_status() === PHP_SESSION_ACTIVE) {
                    @session_regenerate_id(true);
                }
                Flash::warning('Phiên làm việc đã hết hạn do không thao tác trong 15 phút. Vui lòng đăng nhập lại.');
                header('Location: ?route=auth/login');
                exit;
            }
            // Cập nhật lại mốc thời gian hoạt động mới nhất
            $_SESSION[self::SESSION_TIME_KEY] = time();
        }
    }

    /**
     * Thực hiện đăng nhập người dùng.
     */
    public static function login(string $username, string $password): array
    {
        self::initSession();

        $repo = new NguoiDungRepository();
        $account = $repo->findByTenDangNhap($username);

        if (!$account) {
            return [
                'success' => false,
                'message' => 'Tên đăng nhập hoặc mật khẩu không chính xác.',
            ];
        }

        // Kiểm tra trạng thái tài khoản
        if ($account['trangThaiTaiKhoan'] === 'KHOA') {
            NhatKyService::ghi(
                $account['maTaiKhoan'],
                'Xác thực',
                'DANG_NHAP_THAT_BAI',
                'Thử đăng nhập vào tài khoản đã bị khóa: ' . $username
            );
            return [
                'success' => false,
                'message' => 'Tài khoản của bạn đang bị KHÓA. Vui lòng liên hệ Quản trị viên để được hỗ trợ.',
            ];
        }

        // Kiểm tra mật khẩu băm
        if (!password_verify($password, $account['matKhau'])) {
            NhatKyService::ghi(
                $account['maTaiKhoan'],
                'Xác thực',
                'DANG_NHAP_THAT_BAI',
                'Nhập sai mật khẩu cho tài khoản: ' . $username
            );
            return [
                'success' => false,
                'message' => 'Tên đăng nhập hoặc mật khẩu không chính xác.',
            ];
        }

        // Tái tạo session ID để chống Session Fixation (chỉ gọi khi chưa gửi headers)
        if (!headers_sent() && session_status() === PHP_SESSION_ACTIVE) {
            @session_regenerate_id(true);
        }

        // Lưu thông tin người dùng vào Session
        $_SESSION[self::SESSION_USER_KEY] = [
            'maTaiKhoan'  => $account['maTaiKhoan'],
            'tenDangNhap' => $account['tenDangNhap'],
            'vaiTro'      => $account['vaiTro'],
            'maNhanVien'  => $account['maNhanVien'],
            'hoTen'       => $account['hoTen'] ?? $account['tenDangNhap'],
            'chucDanh'    => $account['chucDanh'] ?? 'Nhân viên',
            'maBoPhan'    => $account['maBoPhan'] ?? '',
            'tenBoPhan'   => $account['tenBoPhan'] ?? 'Chưa phân bổ',
            'email'       => $account['emailTaiKhoan'] ?? $account['emailNhanVien'] ?? '',
        ];

        $_SESSION[self::SESSION_TIME_KEY] = time();

        // Ghi nhật ký đăng nhập thành công
        NhatKyService::ghi(
            $account['maTaiKhoan'],
            'Xác thực',
            'DANG_NHAP_THANH_CONG',
            'Đăng nhập hệ thống thành công với vai trò ' . $account['vaiTro']
        );

        return ['success' => true];
    }

    /**
     * Đăng xuất khỏi hệ thống.
     */
    public static function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            if (!headers_sent()) {
                session_start();
            } else {
                @session_start();
            }
        }

        if (self::check()) {
            $user = self::user();
            NhatKyService::ghi(
                $user['maTaiKhoan'] ?? null,
                'Xác thực',
                'DANG_XUAT',
                'Đăng xuất khỏi hệ thống'
            );
        }

        unset($_SESSION[self::SESSION_USER_KEY]);
        unset($_SESSION[self::SESSION_TIME_KEY]);

        if (!headers_sent() && session_status() === PHP_SESSION_ACTIVE) {
            @session_regenerate_id(true);
        }
    }

    /**
     * Kiểm tra trạng thái đã đăng nhập.
     */
    public static function check(): bool
    {
        return !empty($_SESSION[self::SESSION_USER_KEY]);
    }

    /**
     * Lấy thông tin tài khoản đang đăng nhập.
     */
    public static function user(): ?array
    {
        return $_SESSION[self::SESSION_USER_KEY] ?? null;
    }

    /**
     * Lấy vai trò của người dùng hiện tại (QTV, BGD, LKH, QLK, NVK, QC).
     */
    public static function role(): ?string
    {
        return $_SESSION[self::SESSION_USER_KEY]['vaiTro'] ?? null;
    }

    /**
     * Kiểm tra người dùng có một trong các vai trò chỉ định hay không.
     *
     * @param string|array $roles
     */
    public static function hasRole(string|array $roles): bool
    {
        if (!self::check()) {
            return false;
        }

        $currentRole = self::role();
        if (is_array($roles)) {
            return in_array($currentRole, $roles, true);
        }

        return $currentRole === $roles;
    }

    /**
     * Yêu cầu phải đăng nhập. Nếu chưa thì chuyển hướng tới trang login.
     */
    public static function requireLogin(): void
    {
        self::initSession();

        if (!self::check()) {
            Flash::warning('Vui lòng đăng nhập để tiếp tục.');
            header('Location: ?route=auth/login');
            exit;
        }
    }

    /**
     * Yêu cầu người dùng phải có một trong các vai trò được phép.
     */
    public static function requireRole(array $allowedRoles): void
    {
        self::requireLogin();

        if (!self::hasRole($allowedRoles)) {
            http_response_code(403);
            header('Location: ?route=error/forbidden');
            exit;
        }
    }

    /**
     * Đọc cấu hình phân quyền từ app/config/permissions.php (Single Source of Truth).
     */
    public static function getPermissions(): array
    {
        if (self::$permissionsConfig === null) {
            $file = dirname(__DIR__) . '/config/permissions.php';
            self::$permissionsConfig = file_exists($file) ? require $file : [];
        }
        return self::$permissionsConfig;
    }

    /**
     * Kiểm tra vai trò hiện tại có quyền thực hiện hành động trên phân hệ không.
     *
     * @param string $module Tên phân hệ (vd: 'de-nghi-mua', 'lenh-san-xuat', 'phan-bo'...)
     * @param string $action Tên hành động (vd: 'index', 'create', 'update'...)
     */
    public static function can(string $module, string $action = 'index'): bool
    {
        if (!self::check()) {
            return false;
        }

        $role = self::role();
        $perms = self::getPermissions();
        $moduleDef = $perms['modules'][$module] ?? null;

        if (!$moduleDef) {
            return false;
        }

        $writeActions = $perms['action_types']['write'] ?? ['create', 'store', 'update', 'cancel', 'delete', 'confirm'];

        if (in_array($action, $writeActions, true)) {
            return in_array($role, $moduleDef['write'] ?? [], true);
        }

        // Mặc định là quyền xem
        return in_array($role, $moduleDef['view'] ?? [], true);
    }

    /**
     * Kiểm tra quyền GHI của vai trò hiện tại đối với module.
     */
    public static function canWrite(string $module): bool
    {
        return self::can($module, 'create');
    }

    /**
     * Yêu cầu kiểm tra quyền truy cập theo module và action từ permissions.php.
     */
    public static function requirePermission(string $module, string $action = 'index'): void
    {
        self::requireLogin();

        if (!self::can($module, $action)) {
            http_response_code(403);
            header('Location: ?route=error/forbidden');
            exit;
        }
    }
}
