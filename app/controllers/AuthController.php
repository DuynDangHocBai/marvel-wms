<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Flash;
use App\Repositories\NguoiDungRepository;

/**
 * Controller xử lý đăng nhập, đăng xuất và phân quyền truy cập.
 */
class AuthController extends Controller
{
    /**
     * Màn hình đăng nhập & Xử lý đăng nhập.
     */
    public function login(): void
    {
        // Nếu đã đăng nhập thì chuyển thẳng về dashboard
        if (Auth::check()) {
            $this->redirect('?route=dashboard/index');
        }

        $configFile = dirname(__DIR__, 2) . '/config/config.php';
        $config = file_exists($configFile) ? require $configFile : [];
        $appEnv = $config['app_env'] ?? 'dev';

        $repo = new NguoiDungRepository();
        $demoAccounts = ($appEnv === 'dev') ? $repo->getDemoAccounts() : [];

        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->render('auth/login', [
                    'pageTitle'    => 'Đăng nhập hệ thống',
                    'demoAccounts' => $demoAccounts,
                    'appEnv'       => $appEnv,
                    'username'     => $this->post('username', ''),
                ], 'auth');
                return;
            }

            $username = trim($this->post('username', ''));
            $password = (string)$this->post('password', '');

            if (empty($username) || empty($password)) {
                Flash::error('Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.');
                $this->render('auth/login', [
                    'pageTitle'    => 'Đăng nhập hệ thống',
                    'demoAccounts' => $demoAccounts,
                    'appEnv'       => $appEnv,
                    'username'     => $username,
                ], 'auth');
                return;
            }

            $result = Auth::login($username, $password);

            if ($result['success']) {
                $user = Auth::user();
                Flash::success("Đăng nhập thành công! Chào mừng {$user['hoTen']} ({$user['vaiTro']}).");
                $this->redirect('?route=dashboard/index');
            } else {
                Flash::error($result['message']);
                $this->render('auth/login', [
                    'pageTitle'    => 'Đăng nhập hệ thống',
                    'demoAccounts' => $demoAccounts,
                    'appEnv'       => $appEnv,
                    'username'     => $username,
                ], 'auth');
                return;
            }
        }

        $this->render('auth/login', [
            'pageTitle'    => 'Đăng nhập hệ thống',
            'demoAccounts' => $demoAccounts,
            'appEnv'       => $appEnv,
            'username'     => '',
        ], 'auth');
    }

    /**
     * Đăng xuất người dùng.
     */
    public function logout(): void
    {
        Auth::logout();
        Flash::info('Bạn đã đăng xuất khỏi hệ thống an toàn.');
        $this->redirect('?route=auth/login');
    }
}

