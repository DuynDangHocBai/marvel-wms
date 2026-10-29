<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * Repository truy xuất dữ liệu tài khoản và nhân viên từ cơ sở dữ liệu.
 */
class NguoiDungRepository
{
    /**
     * Tìm tài khoản theo tên đăng nhập kèm thông tin nhân viên và bộ phận.
     */
    public function findByTenDangNhap(string $tenDangNhap): ?array
    {
        $db = Database::getConnection();
        $sql = "SELECT tk.maTaiKhoan, tk.tenDangNhap, tk.matKhau, tk.email AS emailTaiKhoan,
                       tk.soDienThoai AS sdtTaiKhoan, tk.vaiTro, tk.trangThai AS trangThaiTaiKhoan,
                       tk.maNhanVien, tk.ngayTao,
                       nv.hoTen, nv.chucDanh, nv.email AS emailNhanVien, nv.soDienThoai AS sdtNhanVien,
                       nv.trangThai AS trangThaiNhanVien, nv.maBoPhan,
                       bp.tenBoPhan
                FROM tai_khoan_nguoi_dung tk
                LEFT JOIN nhan_vien nv ON tk.maNhanVien = nv.maNhanVien
                LEFT JOIN bo_phan bp ON nv.maBoPhan = bp.maBoPhan
                WHERE tk.tenDangNhap = :tenDangNhap
                LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->execute(['tenDangNhap' => $tenDangNhap]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * Tìm tài khoản theo mã tài khoản.
     */
    public function findByMaTaiKhoan(string $maTaiKhoan): ?array
    {
        $db = Database::getConnection();
        $sql = "SELECT tk.maTaiKhoan, tk.tenDangNhap, tk.vaiTro, tk.trangThai AS trangThaiTaiKhoan,
                       tk.maNhanVien, tk.ngayTao,
                       nv.hoTen, nv.chucDanh, nv.maBoPhan, bp.tenBoPhan
                FROM tai_khoan_nguoi_dung tk
                LEFT JOIN nhan_vien nv ON tk.maNhanVien = nv.maNhanVien
                LEFT JOIN bo_phan bp ON nv.maBoPhan = bp.maBoPhan
                WHERE tk.maTaiKhoan = :maTaiKhoan
                LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->execute(['maTaiKhoan' => $maTaiKhoan]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * Lấy danh sách tài khoản demo cho môi trường dev.
     */
    public function getDemoAccounts(): array
    {
        $db = Database::getConnection();
        $sql = "SELECT tk.tenDangNhap, tk.vaiTro, tk.trangThai, nv.hoTen, nv.chucDanh, bp.tenBoPhan
                FROM tai_khoan_nguoi_dung tk
                LEFT JOIN nhan_vien nv ON tk.maNhanVien = nv.maNhanVien
                LEFT JOIN bo_phan bp ON nv.maBoPhan = bp.maBoPhan
                ORDER BY tk.maTaiKhoan ASC";
        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }
}
