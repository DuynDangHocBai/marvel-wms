<?php

namespace App\Services;

use App\Core\Database;
use PDO;

/**
 * Service ghi nhận và truy vấn nhật ký hoạt động hệ thống.
 */
class NhatKyService
{
    /**
     * Ghi một bản ghi nhật ký hệ thống.
     *
     * @param string|null $maTaiKhoan Mã tài khoản thực hiện (hoặc null nếu hệ thống)
     * @param string $chucNang Tên chức năng/phân hệ
     * @param string $hanhDong Tên hành động (vd: DANG_NHAP, TAO_MOI, CAP_NHAT, KHOA)
     * @param string|null $noiDungThayDoi Mô tả tóm tắt nội dung
     * @param array|string|null $giaTriCu Giá trị trước khi đổi (JSON hoặc array)
     * @param array|string|null $giaTriMoi Giá trị sau khi đổi (JSON hoặc array)
     * @param string|null $diaChiIP Địa chỉ IP (tự nhận diện nếu null)
     * @return bool
     */
    public static function ghi(
        ?string $maTaiKhoan,
        string $chucNang,
        string $hanhDong,
        ?string $noiDungThayDoi = null,
        mixed $giaTriCu = null,
        mixed $giaTriMoi = null,
        ?string $diaChiIP = null
    ): bool {
        try {
            $db = Database::getConnection();

            if ($diaChiIP === null) {
                $diaChiIP = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            }

            $jsonCu = null;
            if ($giaTriCu !== null) {
                $jsonCu = is_string($giaTriCu) ? $giaTriCu : json_encode($giaTriCu, JSON_UNESCAPED_UNICODE);
            }

            $jsonMoi = null;
            if ($giaTriMoi !== null) {
                $jsonMoi = is_string($giaTriMoi) ? $giaTriMoi : json_encode($giaTriMoi, JSON_UNESCAPED_UNICODE);
            }

            $sql = "INSERT INTO `nhat_ky_he_thong`
                    (`maTaiKhoan`, `chucNang`, `hanhDong`, `noiDungThayDoi`, `giaTriCu`, `giaTriMoi`, `diaChiIP`)
                    VALUES
                    (:maTaiKhoan, :chucNang, :hanhDong, :noiDungThayDoi, :giaTriCu, :giaTriMoi, :diaChiIP)";

            $stmt = $db->prepare($sql);
            return $stmt->execute([
                'maTaiKhoan'     => $maTaiKhoan,
                'chucNang'       => $chucNang,
                'hanhDong'       => $hanhDong,
                'noiDungThayDoi' => $noiDungThayDoi,
                'giaTriCu'       => $jsonCu,
                'giaTriMoi'      => $jsonMoi,
                'diaChiIP'       => $diaChiIP,
            ]);
        } catch (\Throwable $e) {
            // Ghi log lỗi không được làm gián đoạn luồng nghiệp vụ chính
            error_log("Lỗi ghi nhật ký hệ thống: " . $e->getMessage());
            return false;
        }
    }
}
