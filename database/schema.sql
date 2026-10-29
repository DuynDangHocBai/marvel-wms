-- ==============================================================
-- HỆ THỐNG QUẢN LÝ KHO MARVEL SWEET (MARVEL WMS)
-- Database: marvel_wms (UTF-8 mb4 unicode ci, InnoDB)
-- Phục vụ thống nhất cho toàn bộ 6 phân hệ chức năng
-- ==============================================================

SET NAMES utf8mb4;

DROP DATABASE IF EXISTS `marvel_wms`;
CREATE DATABASE `marvel_wms`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `marvel_wms`;
SET NAMES utf8mb4;

-- ==============================================================
-- PHÂN HỆ 1: TỔ CHỨC & HỆ THỐNG
-- ==============================================================

-- Bảng 1: Bộ phận (bo_phan)
CREATE TABLE `bo_phan` (
  `maBoPhan` VARCHAR(20) NOT NULL,
  `tenBoPhan` VARCHAR(100) NOT NULL,
  `moTa` TEXT NULL,
  PRIMARY KEY (`maBoPhan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 2: Nhân viên (nhan_vien)
CREATE TABLE `nhan_vien` (
  `maNhanVien` VARCHAR(20) NOT NULL,
  `hoTen` VARCHAR(100) NOT NULL,
  `chucDanh` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) NULL,
  `soDienThoai` VARCHAR(20) NULL,
  `trangThai` ENUM('HOAT_DONG', 'NGHI_VIEC') NOT NULL DEFAULT 'HOAT_DONG',
  `maBoPhan` VARCHAR(20) NOT NULL,
  PRIMARY KEY (`maNhanVien`),
  KEY `idx_nv_bophan` (`maBoPhan`),
  CONSTRAINT `fk_nv_bophan` FOREIGN KEY (`maBoPhan`)
    REFERENCES `bo_phan` (`maBoPhan`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 3: Tài khoản người dùng (tai_khoan_nguoi_dung)
CREATE TABLE `tai_khoan_nguoi_dung` (
  `maTaiKhoan` VARCHAR(20) NOT NULL,
  `tenDangNhap` VARCHAR(50) NOT NULL UNIQUE,
  `matKhau` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NULL,
  `soDienThoai` VARCHAR(20) NULL,
  `vaiTro` ENUM('QTV', 'BGD', 'LKH', 'QLK', 'NVK', 'QC') NOT NULL,
  `trangThai` ENUM('HOAT_DONG', 'KHOA') NOT NULL DEFAULT 'HOAT_DONG',
  `maNhanVien` VARCHAR(20) NULL,
  `ngayTao` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`maTaiKhoan`),
  KEY `idx_tk_nhanvien` (`maNhanVien`),
  CONSTRAINT `fk_tk_nhanvien` FOREIGN KEY (`maNhanVien`)
    REFERENCES `nhan_vien` (`maNhanVien`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 4: Nhật ký hệ thống (nhat_ky_he_thong)
CREATE TABLE `nhat_ky_he_thong` (
  `maNhatKy` BIGINT NOT NULL AUTO_INCREMENT,
  `maTaiKhoan` VARCHAR(20) NULL,
  `chucNang` VARCHAR(100) NOT NULL,
  `hanhDong` VARCHAR(50) NOT NULL,
  `noiDungThayDoi` TEXT NULL,
  `giaTriCu` JSON NULL,
  `giaTriMoi` JSON NULL,
  `thoiGianThucHien` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `diaChiIP` VARCHAR(45) NULL,
  PRIMARY KEY (`maNhatKy`),
  KEY `idx_nk_taikhoan` (`maTaiKhoan`),
  KEY `idx_nk_thoigian` (`thoiGianThucHien`),
  CONSTRAINT `fk_nk_taikhoan` FOREIGN KEY (`maTaiKhoan`)
    REFERENCES `tai_khoan_nguoi_dung` (`maTaiKhoan`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 5: Ban giám đốc (ban_giam_doc)
CREATE TABLE `ban_giam_doc` (
  `maThanhVien` VARCHAR(20) NOT NULL,
  `maNhanVien` VARCHAR(20) NOT NULL,
  `chucVu` VARCHAR(100) NOT NULL,
  `phamViPheDuyet` VARCHAR(255) NULL,
  PRIMARY KEY (`maThanhVien`),
  UNIQUE KEY `uq_bgd_nhanvien` (`maNhanVien`),
  CONSTRAINT `fk_bgd_nhanvien` FOREIGN KEY (`maNhanVien`)
    REFERENCES `nhan_vien` (`maNhanVien`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 6: Nhà cung cấp (nha_cung_cap)
CREATE TABLE `nha_cung_cap` (
  `maNCC` VARCHAR(20) NOT NULL,
  `tenNCC` VARCHAR(200) NOT NULL,
  `diaChi` VARCHAR(255) NULL,
  `soDienThoai` VARCHAR(50) NULL,
  `email` VARCHAR(100) NULL,
  `trangThai` ENUM('HOAT_DONG', 'NGUNG_HOP_TAC') NOT NULL DEFAULT 'HOAT_DONG',
  PRIMARY KEY (`maNCC`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 7: Khách hàng (khach_hang)
CREATE TABLE `khach_hang` (
  `maKhachHang` VARCHAR(20) NOT NULL,
  `tenKhachHang` VARCHAR(200) NOT NULL,
  `diaChi` VARCHAR(255) NULL,
  `soDienThoai` VARCHAR(50) NULL,
  `email` VARCHAR(100) NULL,
  `trangThai` ENUM('HOAT_DONG', 'NGUNG') NOT NULL DEFAULT 'HOAT_DONG',
  PRIMARY KEY (`maKhachHang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================
-- PHÂN HỆ 2: DANH MỤC KHO & HÀNG HÓA
-- ==============================================================

-- Bảng 8: Kho (kho)
CREATE TABLE `kho` (
  `maKho` VARCHAR(20) NOT NULL,
  `tenKho` VARCHAR(150) NOT NULL,
  `loaiKho` VARCHAR(50) NOT NULL DEFAULT 'KHO_NVL',
  `diaDiem` VARCHAR(255) NULL,
  `sucChuaToiDa` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `sucChuaDaSuDung` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `sucChuaDaHenNhap` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `trangThai` ENUM('HOAT_DONG', 'BAO_TRI', 'NGUNG') NOT NULL DEFAULT 'HOAT_DONG',
  PRIMARY KEY (`maKho`),
  CONSTRAINT `chk_kho_succhua_toida` CHECK (`sucChuaToiDa` >= 0),
  CONSTRAINT `chk_kho_succhua_dasudung` CHECK (`sucChuaDaSuDung` >= 0),
  CONSTRAINT `chk_kho_succhua_dahennhap` CHECK (`sucChuaDaHenNhap` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 9: Vị trí lưu kho (vi_tri_luu_kho)
CREATE TABLE `vi_tri_luu_kho` (
  `maViTri` VARCHAR(20) NOT NULL,
  `maKho` VARCHAR(20) NOT NULL,
  `maKe` VARCHAR(20) NULL,
  `tang` INT NULL,
  `oChua` VARCHAR(20) NULL,
  `loaiHangLuuTru` VARCHAR(50) NULL,
  `sucChuaToiDa` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `sucChuaDaSuDung` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `trangThai` ENUM('HOAT_DONG', 'BAO_TRI', 'DAY', 'KHOA') NOT NULL DEFAULT 'HOAT_DONG',
  PRIMARY KEY (`maViTri`),
  KEY `idx_vt_kho` (`maKho`),
  CONSTRAINT `chk_vt_succhua_toida` CHECK (`sucChuaToiDa` >= 0),
  CONSTRAINT `chk_vt_succhua_dasudung` CHECK (`sucChuaDaSuDung` >= 0),
  CONSTRAINT `fk_vt_kho` FOREIGN KEY (`maKho`)
    REFERENCES `kho` (`maKho`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 10: Mặt hàng (mat_hang)
CREATE TABLE `mat_hang` (
  `maSKU` VARCHAR(30) NOT NULL,
  `tenHang` VARCHAR(200) NOT NULL,
  `loaiHang` ENUM('NGUYEN_VAT_LIEU', 'THANH_PHAM') NOT NULL,
  `nhomHang` VARCHAR(100) NULL,
  `donViTinh` VARCHAR(30) NOT NULL,
  `quyCachDongGoi` VARCHAR(100) NULL,
  `giaNhap` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `giaBan` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `tonToiThieu` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `tonToiDa` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `coHanSuDung` TINYINT(1) NOT NULL DEFAULT 1,
  `soNgayCanhBaoHetHan` INT NOT NULL DEFAULT 30,
  `moTa` TEXT NULL,
  `trangThai` ENUM('DANG_KINH_DOANH', 'NGUNG_KINH_DOANH') NOT NULL DEFAULT 'DANG_KINH_DOANH',
  PRIMARY KEY (`maSKU`),
  KEY `idx_mh_loaihang` (`loaiHang`),
  CONSTRAINT `chk_mh_gianhap` CHECK (`giaNhap` >= 0),
  CONSTRAINT `chk_mh_giaban` CHECK (`giaBan` >= 0),
  CONSTRAINT `chk_mh_tontoithieu` CHECK (`tonToiThieu` >= 0),
  CONSTRAINT `chk_mh_tontoida` CHECK (`tonToiDa` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 11: Nguyên vật liệu (nguyen_vat_lieu)
CREATE TABLE `nguyen_vat_lieu` (
  `maSKU` VARCHAR(30) NOT NULL,
  `tonToiThieu` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  PRIMARY KEY (`maSKU`),
  CONSTRAINT `chk_nvl_tontoithieu` CHECK (`tonToiThieu` >= 0),
  CONSTRAINT `fk_nvl_mathang` FOREIGN KEY (`maSKU`)
    REFERENCES `mat_hang` (`maSKU`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 12: Thành phẩm (thanh_pham)
CREATE TABLE `thanh_pham` (
  `maSKU` VARCHAR(30) NOT NULL,
  `quyCachDongGoi` VARCHAR(100) NULL,
  PRIMARY KEY (`maSKU`),
  CONSTRAINT `fk_tp_mathang` FOREIGN KEY (`maSKU`)
    REFERENCES `mat_hang` (`maSKU`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 13: Xưởng sản xuất (xuong_san_xuat)
CREATE TABLE `xuong_san_xuat` (
  `maXuong` VARCHAR(20) NOT NULL,
  `tenXuong` VARCHAR(150) NOT NULL,
  `viTri` VARCHAR(255) NULL,
  `congSuat` DECIMAL(15,3) NULL,
  `maKhoNVL` VARCHAR(20) NULL,
  `trangThai` ENUM('HOAT_DONG', 'BAO_TRI', 'NGUNG') NOT NULL DEFAULT 'HOAT_DONG',
  PRIMARY KEY (`maXuong`),
  KEY `idx_xsx_khonvl` (`maKhoNVL`),
  CONSTRAINT `fk_xsx_khonvl` FOREIGN KEY (`maKhoNVL`)
    REFERENCES `kho` (`maKho`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 14: Định mức nguyên vật liệu (dinh_muc_nvl)
CREATE TABLE `dinh_muc_nvl` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `maThanhPham` VARCHAR(30) NOT NULL,
  `maNVL` VARCHAR(30) NOT NULL,
  `soLuongDinhMuc` DECIMAL(15,3) NOT NULL,
  `donViTinh` VARCHAR(30) NULL,
  `trangThai` ENUM('HOAT_DONG', 'NGUNG') NOT NULL DEFAULT 'HOAT_DONG',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_dm_tp_nvl` (`maThanhPham`, `maNVL`),
  KEY `idx_dm_tp` (`maThanhPham`),
  KEY `idx_dm_nvl` (`maNVL`),
  CONSTRAINT `chk_dm_soluong` CHECK (`soLuongDinhMuc` > 0),
  CONSTRAINT `fk_dm_tp` FOREIGN KEY (`maThanhPham`)
    REFERENCES `mat_hang` (`maSKU`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_dm_nvl` FOREIGN KEY (`maNVL`)
    REFERENCES `mat_hang` (`maSKU`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 15: Lô hàng (lo_hang)
CREATE TABLE `lo_hang` (
  `maLo` VARCHAR(30) NOT NULL,
  `soLo` VARCHAR(50) NOT NULL,
  `maSKU` VARCHAR(30) NOT NULL,
  `ngaySanXuat` DATE NULL,
  `ngayNhap` DATE NOT NULL DEFAULT (CURRENT_DATE),
  `ngayHetHan` DATE NULL,
  `trangThaiChatLuong` ENUM('CHO_KIEM', 'DAT', 'KHONG_DAT', 'CACH_LY') NOT NULL DEFAULT 'CHO_KIEM',
  `trangThai` ENUM('HOAT_DONG', 'TAM_KHOA', 'DA_XUAT_HET', 'HUY') NOT NULL DEFAULT 'HOAT_DONG',
  PRIMARY KEY (`maLo`),
  KEY `idx_lo_sku` (`maSKU`),
  KEY `idx_lo_hethan` (`ngayHetHan`),
  KEY `idx_lo_chatluong` (`trangThaiChatLuong`),
  CONSTRAINT `fk_lo_mathang` FOREIGN KEY (`maSKU`)
    REFERENCES `mat_hang` (`maSKU`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 16: Tồn kho theo lô và vị trí (ton_kho)
CREATE TABLE `ton_kho` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `maLo` VARCHAR(30) NOT NULL,
  `maViTri` VARCHAR(20) NOT NULL,
  `soLuongThucTe` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `soLuongGiuCho` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tonkho_lo_vitri` (`maLo`, `maViTri`),
  KEY `idx_tk_lo` (`maLo`),
  KEY `idx_tk_vitri` (`maViTri`),
  CONSTRAINT `chk_tk_thucte` CHECK (`soLuongThucTe` >= 0),
  CONSTRAINT `chk_tk_giucho` CHECK (`soLuongGiuCho` >= 0),
  CONSTRAINT `chk_tk_thucte_giucho` CHECK (`soLuongThucTe` >= `soLuongGiuCho`),
  CONSTRAINT `fk_tk_lo` FOREIGN KEY (`maLo`)
    REFERENCES `lo_hang` (`maLo`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_tk_vitri` FOREIGN KEY (`maViTri`)
    REFERENCES `vi_tri_luu_kho` (`maViTri`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================
-- PHÂN HỆ 3: KẾ HOẠCH & MUA HÀNG
-- ==============================================================

-- Bảng 17: Đơn hàng bán (don_hang)
CREATE TABLE `don_hang` (
  `maDonHang` VARCHAR(30) NOT NULL,
  `maKhachHang` VARCHAR(20) NOT NULL,
  `ngayDat` DATE NOT NULL,
  `ngayGiaoDuKien` DATE NOT NULL,
  `trangThai` ENUM('CHO_XU_LY', 'CHO_SAN_XUAT', 'DANG_SAN_XUAT', 'CHO_XUAT', 'HOAN_THANH', 'HUY') NOT NULL DEFAULT 'CHO_XU_LY',
  `tongTien` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `ghiChu` TEXT NULL,
  PRIMARY KEY (`maDonHang`),
  KEY `idx_dh_khachhang` (`maKhachHang`),
  CONSTRAINT `chk_dh_tongtien` CHECK (`tongTien` >= 0),
  CONSTRAINT `fk_dh_khachhang` FOREIGN KEY (`maKhachHang`)
    REFERENCES `khach_hang` (`maKhachHang`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 18: Chi tiết đơn hàng bán (chi_tiet_don_hang)
CREATE TABLE `chi_tiet_don_hang` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `maDonHang` VARCHAR(30) NOT NULL,
  `maSKU` VARCHAR(30) NOT NULL,
  `soLuong` DECIMAL(15,3) NOT NULL,
  `donGia` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `thanhTien` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ctdh_donhang_sku` (`maDonHang`, `maSKU`),
  KEY `idx_ctdh_sku` (`maSKU`),
  CONSTRAINT `chk_ctdh_soluong` CHECK (`soLuong` > 0),
  CONSTRAINT `chk_ctdh_dongia` CHECK (`donGia` >= 0),
  CONSTRAINT `chk_ctdh_thanhtien` CHECK (`thanhTien` >= 0),
  CONSTRAINT `fk_ctdh_donhang` FOREIGN KEY (`maDonHang`)
    REFERENCES `don_hang` (`maDonHang`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_ctdh_sku` FOREIGN KEY (`maSKU`)
    REFERENCES `mat_hang` (`maSKU`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 19: Kế hoạch sản xuất (ke_hoach_san_xuat)
CREATE TABLE `ke_hoach_san_xuat` (
  `maKeHoach` VARCHAR(30) NOT NULL,
  `maNhanVienLap` VARCHAR(20) NOT NULL,
  `maXuong` VARCHAR(20) NOT NULL,
  `ngayLap` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ngayBatDau` DATE NOT NULL,
  `ngayHoanThanh` DATE NOT NULL,
  `trangThai` ENUM('CHO_DUYET', 'DA_DUYET', 'TU_CHOI', 'DA_HUY') NOT NULL DEFAULT 'CHO_DUYET',
  `ghiChu` TEXT NULL,
  PRIMARY KEY (`maKeHoach`),
  KEY `idx_khsx_nv` (`maNhanVienLap`),
  KEY `idx_khsx_xuong` (`maXuong`),
  CONSTRAINT `fk_khsx_nv` FOREIGN KEY (`maNhanVienLap`)
    REFERENCES `nhan_vien` (`maNhanVien`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_khsx_xuong` FOREIGN KEY (`maXuong`)
    REFERENCES `xuong_san_xuat` (`maXuong`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 20: Chi tiết kế hoạch sản xuất (chi_tiet_ke_hoach_san_xuat)
CREATE TABLE `chi_tiet_ke_hoach_san_xuat` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `maKeHoach` VARCHAR(30) NOT NULL,
  `maSKU` VARCHAR(30) NOT NULL,
  `soLuongSanXuat` DECIMAL(15,3) NOT NULL,
  `soLuongDaGiaoLenh` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ctkh_kehoach_sku` (`maKeHoach`, `maSKU`),
  KEY `idx_ctkh_sku` (`maSKU`),
  CONSTRAINT `chk_ctkh_soluong_sx` CHECK (`soLuongSanXuat` > 0),
  CONSTRAINT `chk_ctkh_soluong_dagiao` CHECK (`soLuongDaGiaoLenh` >= 0),
  CONSTRAINT `fk_ctkh_kehoach` FOREIGN KEY (`maKeHoach`)
    REFERENCES `ke_hoach_san_xuat` (`maKeHoach`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_ctkh_sku` FOREIGN KEY (`maSKU`)
    REFERENCES `mat_hang` (`maSKU`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 21: Nhu cầu nguyên vật liệu (nhu_cau_nvl)
CREATE TABLE `nhu_cau_nvl` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `maKeHoach` VARCHAR(30) NOT NULL,
  `maNVL` VARCHAR(30) NOT NULL,
  `soLuongCan` DECIMAL(15,3) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ncnvl_kehoach_nvl` (`maKeHoach`, `maNVL`),
  KEY `idx_ncnvl_nvl` (`maNVL`),
  CONSTRAINT `chk_ncnvl_soluong` CHECK (`soLuongCan` > 0),
  CONSTRAINT `fk_ncnvl_kehoach` FOREIGN KEY (`maKeHoach`)
    REFERENCES `ke_hoach_san_xuat` (`maKeHoach`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_ncnvl_nvl` FOREIGN KEY (`maNVL`)
    REFERENCES `mat_hang` (`maSKU`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 22: Đề nghị mua nguyên vật liệu (de_nghi_mua_nvl)
CREATE TABLE `de_nghi_mua_nvl` (
  `maDeNghi` VARCHAR(50) NOT NULL,
  `maNhanVienLap` VARCHAR(20) NOT NULL,
  `maKeHoach` VARCHAR(30) NULL,
  `ngayLap` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ngayCanHang` DATE NULL,
  `trangThai` ENUM('CHO_DUYET', 'DA_DUYET', 'TU_CHOI', 'DA_HUY', 'DANG_MUA', 'HOAN_THANH') NOT NULL DEFAULT 'CHO_DUYET',
  `tongTienDuKien` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `lyDo` TEXT NULL,
  PRIMARY KEY (`maDeNghi`),
  KEY `idx_dnm_nv` (`maNhanVienLap`),
  KEY `idx_dnm_khsx` (`maKeHoach`),
  CONSTRAINT `chk_dnm_tongtien` CHECK (`tongTienDuKien` >= 0),
  CONSTRAINT `fk_dnm_nv` FOREIGN KEY (`maNhanVienLap`)
    REFERENCES `nhan_vien` (`maNhanVien`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_dnm_khsx` FOREIGN KEY (`maKeHoach`)
    REFERENCES `ke_hoach_san_xuat` (`maKeHoach`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 23: Chi tiết đề nghị mua NVL (chi_tiet_de_nghi_mua_nvl)
CREATE TABLE `chi_tiet_de_nghi_mua_nvl` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `maDeNghi` VARCHAR(50) NOT NULL,
  `maSKU` VARCHAR(30) NOT NULL,
  `nhuCauSX` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `tonKhaDung` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `tonToiThieu` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `hangDangVe` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `soLuongDeXuat` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `soLuongMua` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `ngoaiKeHoach` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ctdnm_denghi_sku` (`maDeNghi`, `maSKU`),
  KEY `idx_ctdnm_sku` (`maSKU`),
  CONSTRAINT `chk_ctdnm_nhucau` CHECK (`nhuCauSX` >= 0),
  CONSTRAINT `chk_ctdnm_tonkhadung` CHECK (`tonKhaDung` >= 0),
  CONSTRAINT `chk_ctdnm_tontoithieu` CHECK (`tonToiThieu` >= 0),
  CONSTRAINT `chk_ctdnm_hangdangve` CHECK (`hangDangVe` >= 0),
  CONSTRAINT `chk_ctdnm_dexuat` CHECK (`soLuongDeXuat` >= 0),
  CONSTRAINT `chk_ctdnm_soluongmua` CHECK (`soLuongMua` >= 0),
  CONSTRAINT `fk_ctdnm_denghi` FOREIGN KEY (`maDeNghi`)
    REFERENCES `de_nghi_mua_nvl` (`maDeNghi`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_ctdnm_sku` FOREIGN KEY (`maSKU`)
    REFERENCES `mat_hang` (`maSKU`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 24: Đơn mua hàng (don_mua_hang)
CREATE TABLE `don_mua_hang` (
  `maDonMua` VARCHAR(30) NOT NULL,
  `maDeNghi` VARCHAR(50) NULL,
  `maNCC` VARCHAR(20) NOT NULL,
  `maNhanVienLap` VARCHAR(20) NOT NULL,
  `ngayDat` DATE NOT NULL,
  `ngayGiaoDuKien` DATE NULL,
  `trangThai` ENUM('CHO_DUYET', 'DA_DUYET', 'DANG_GIAO', 'HOAN_THANH', 'HUY') NOT NULL DEFAULT 'CHO_DUYET',
  `tongTien` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`maDonMua`),
  KEY `idx_dmh_denghi` (`maDeNghi`),
  KEY `idx_dmh_ncc` (`maNCC`),
  KEY `idx_dmh_nv` (`maNhanVienLap`),
  CONSTRAINT `chk_dmh_tongtien` CHECK (`tongTien` >= 0),
  CONSTRAINT `fk_dmh_denghi` FOREIGN KEY (`maDeNghi`)
    REFERENCES `de_nghi_mua_nvl` (`maDeNghi`) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_dmh_ncc` FOREIGN KEY (`maNCC`)
    REFERENCES `nha_cung_cap` (`maNCC`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_dmh_nv` FOREIGN KEY (`maNhanVienLap`)
    REFERENCES `nhan_vien` (`maNhanVien`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 25: Chi tiết đơn mua hàng (chi_tiet_don_mua_hang)
CREATE TABLE `chi_tiet_don_mua_hang` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `maDonMua` VARCHAR(30) NOT NULL,
  `maSKU` VARCHAR(30) NOT NULL,
  `soLuongDat` DECIMAL(15,3) NOT NULL,
  `soLuongDaNhan` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `donGia` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `thanhTien` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ctdm_donmua_sku` (`maDonMua`, `maSKU`),
  KEY `idx_ctdm_sku` (`maSKU`),
  CONSTRAINT `chk_ctdm_soluongdat` CHECK (`soLuongDat` > 0),
  CONSTRAINT `chk_ctdm_soluongdanhan` CHECK (`soLuongDaNhan` >= 0),
  CONSTRAINT `chk_ctdm_dongia` CHECK (`donGia` >= 0),
  CONSTRAINT `chk_ctdm_thanhtien` CHECK (`thanhTien` >= 0),
  CONSTRAINT `fk_ctdm_donmua` FOREIGN KEY (`maDonMua`)
    REFERENCES `don_mua_hang` (`maDonMua`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_ctdm_sku` FOREIGN KEY (`maSKU`)
    REFERENCES `mat_hang` (`maSKU`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 26: Lệnh sản xuất (lenh_san_xuat)
CREATE TABLE `lenh_san_xuat` (
  `maLenhSX` VARCHAR(30) NOT NULL,
  `maKeHoach` VARCHAR(30) NOT NULL,
  `maXuong` VARCHAR(20) NOT NULL,
  `maNhanVienLap` VARCHAR(20) NOT NULL,
  `ngayLap` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ngayBatDau` DATE NOT NULL,
  `ngayHoanThanh` DATE NOT NULL,
  `trangThai` ENUM('CHO_PHAT', 'DA_PHAT', 'DANG_PHAN_BO', 'DANG_SAN_XUAT', 'HOAN_THANH', 'HUY') NOT NULL DEFAULT 'CHO_PHAT',
  `ghiChu` TEXT NULL,
  PRIMARY KEY (`maLenhSX`),
  KEY `idx_lsx_khsx` (`maKeHoach`),
  KEY `idx_lsx_xuong` (`maXuong`),
  KEY `idx_lsx_nv` (`maNhanVienLap`),
  CONSTRAINT `fk_lsx_khsx` FOREIGN KEY (`maKeHoach`)
    REFERENCES `ke_hoach_san_xuat` (`maKeHoach`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_lsx_xuong` FOREIGN KEY (`maXuong`)
    REFERENCES `xuong_san_xuat` (`maXuong`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_lsx_nv` FOREIGN KEY (`maNhanVienLap`)
    REFERENCES `nhan_vien` (`maNhanVien`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 27: Chi tiết lệnh sản xuất (chi_tiet_lenh_san_xuat)
CREATE TABLE `chi_tiet_lenh_san_xuat` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `maLenhSX` VARCHAR(30) NOT NULL,
  `maSKU` VARCHAR(30) NOT NULL,
  `soLuongGiao` DECIMAL(15,3) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ctlsx_lenh_sku` (`maLenhSX`, `maSKU`),
  KEY `idx_ctlsx_sku` (`maSKU`),
  CONSTRAINT `chk_ctlsx_soluong` CHECK (`soLuongGiao` > 0),
  CONSTRAINT `fk_ctlsx_lenh` FOREIGN KEY (`maLenhSX`)
    REFERENCES `lenh_san_xuat` (`maLenhSX`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_ctlsx_sku` FOREIGN KEY (`maSKU`)
    REFERENCES `mat_hang` (`maSKU`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 28: NVL cần cấp cho lệnh sản xuất (nvl_can_cap_lenh_sx)
-- Phục vụ BR-06 giữ chỗ NVL từ khi phát lệnh đến khi phân bổ
CREATE TABLE `nvl_can_cap_lenh_sx` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `maLenhSX` VARCHAR(30) NOT NULL,
  `maSKU` VARCHAR(30) NOT NULL,
  `soLuongCanCap` DECIMAL(15,3) NOT NULL,
  `soLuongDaPhanBo` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `trangThai` ENUM('CHO_PHAN_BO', 'DANG_PHAN_BO', 'DA_PHAN_BO_DU') NOT NULL DEFAULT 'CHO_PHAN_BO',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_nvlls_lenh_sku` (`maLenhSX`, `maSKU`),
  KEY `idx_nvlls_sku` (`maSKU`),
  CONSTRAINT `chk_nvlls_cancap` CHECK (`soLuongCanCap` > 0),
  CONSTRAINT `chk_nvlls_daphanbo` CHECK (`soLuongDaPhanBo` >= 0),
  CONSTRAINT `fk_nvlls_lenh` FOREIGN KEY (`maLenhSX`)
    REFERENCES `lenh_san_xuat` (`maLenhSX`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_nvlls_sku` FOREIGN KEY (`maSKU`)
    REFERENCES `mat_hang` (`maSKU`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================
-- PHÂN HỆ 4: NHẬP, XUẤT, ĐIỀU PHỐI & PHÂN BỔ KHO
-- ==============================================================

-- Bảng 29: Phiếu nhập kho (phieu_nhap_kho)
CREATE TABLE `phieu_nhap_kho` (
  `maPhieuNhap` VARCHAR(30) NOT NULL,
  `maDonMua` VARCHAR(30) NULL,
  `maKho` VARCHAR(20) NOT NULL,
  `maNCC` VARCHAR(20) NULL,
  `ngayLap` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ngayHenGiao` DATE NULL,
  `ngayNhap` DATETIME NULL,
  `maNhanVienLap` VARCHAR(20) NOT NULL,
  `tongSoLuong` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `tongTheTich` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `trangThai` ENUM('CHO_DIEU_PHOI', 'DA_DIEU_PHOI', 'DA_NHAP', 'HUY') NOT NULL DEFAULT 'CHO_DIEU_PHOI',
  `ghiChu` TEXT NULL,
  PRIMARY KEY (`maPhieuNhap`),
  KEY `idx_pnk_kho` (`maKho`),
  KEY `idx_pnk_ncc` (`maNCC`),
  KEY `idx_pnk_nv` (`maNhanVienLap`),
  KEY `idx_pnk_trangthai` (`trangThai`),
  CONSTRAINT `chk_pnk_tongsoluong` CHECK (`tongSoLuong` >= 0),
  CONSTRAINT `chk_pnk_tongthetich` CHECK (`tongTheTich` >= 0),
  CONSTRAINT `fk_pnk_donmua` FOREIGN KEY (`maDonMua`)
    REFERENCES `don_mua_hang` (`maDonMua`) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_pnk_kho` FOREIGN KEY (`maKho`)
    REFERENCES `kho` (`maKho`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_pnk_ncc` FOREIGN KEY (`maNCC`)
    REFERENCES `nha_cung_cap` (`maNCC`) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_pnk_nv` FOREIGN KEY (`maNhanVienLap`)
    REFERENCES `nhan_vien` (`maNhanVien`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 30: Chi tiết phiếu nhập kho (chi_tiet_phieu_nhap_kho)
CREATE TABLE `chi_tiet_phieu_nhap_kho` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `maPhieuNhap` VARCHAR(30) NOT NULL,
  `maLo` VARCHAR(30) NOT NULL,
  `maSKU` VARCHAR(30) NOT NULL,
  `soLuong` DECIMAL(15,3) NOT NULL,
  `donGia` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `thanhTien` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `theTichDonVi` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `tongTheTich` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  PRIMARY KEY (`id`),
  KEY `idx_ctpnk_phieu` (`maPhieuNhap`),
  KEY `idx_ctpnk_lo` (`maLo`),
  KEY `idx_ctpnk_sku` (`maSKU`),
  CONSTRAINT `chk_ctpnk_soluong` CHECK (`soLuong` > 0),
  CONSTRAINT `chk_ctpnk_dongia` CHECK (`donGia` >= 0),
  CONSTRAINT `chk_ctpnk_thanhtien` CHECK (`thanhTien` >= 0),
  CONSTRAINT `fk_ctpnk_phieu` FOREIGN KEY (`maPhieuNhap`)
    REFERENCES `phieu_nhap_kho` (`maPhieuNhap`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_ctpnk_lo` FOREIGN KEY (`maLo`)
    REFERENCES `lo_hang` (`maLo`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_ctpnk_sku` FOREIGN KEY (`maSKU`)
    REFERENCES `mat_hang` (`maSKU`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 31: Lệnh điều phối kho (lenh_dieu_phoi_kho)
CREATE TABLE `lenh_dieu_phoi_kho` (
  `maLenhDieuPhoi` VARCHAR(30) NOT NULL,
  `maPhieuNhap` VARCHAR(30) NOT NULL,
  `maKho` VARCHAR(20) NOT NULL,
  `ngayPhatLenh` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ngayHoanThanh` DATETIME NULL,
  `maNhanVienPhatLenh` VARCHAR(20) NOT NULL,
  `maNhanVienThucHien` VARCHAR(20) NOT NULL,
  `trangThai` ENUM('CHO_THUC_HIEN', 'DANG_THUC_HIEN', 'HOAN_THANH', 'HUY') NOT NULL DEFAULT 'CHO_THUC_HIEN',
  `ghiChu` TEXT NULL,
  PRIMARY KEY (`maLenhDieuPhoi`),
  KEY `idx_ldp_phieu` (`maPhieuNhap`),
  KEY `idx_ldp_kho` (`maKho`),
  KEY `idx_ldp_nvphat` (`maNhanVienPhatLenh`),
  KEY `idx_ldp_nvthuchien` (`maNhanVienThucHien`),
  CONSTRAINT `fk_ldp_phieu` FOREIGN KEY (`maPhieuNhap`)
    REFERENCES `phieu_nhap_kho` (`maPhieuNhap`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_ldp_kho` FOREIGN KEY (`maKho`)
    REFERENCES `kho` (`maKho`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_ldp_nvphat` FOREIGN KEY (`maNhanVienPhatLenh`)
    REFERENCES `nhan_vien` (`maNhanVien`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_ldp_nvthuchien` FOREIGN KEY (`maNhanVienThucHien`)
    REFERENCES `nhan_vien` (`maNhanVien`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 32: Chi tiết lệnh điều phối (chi_tiet_lenh_dieu_phoi)
CREATE TABLE `chi_tiet_lenh_dieu_phoi` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `maLenhDieuPhoi` VARCHAR(30) NOT NULL,
  `maLo` VARCHAR(30) NOT NULL,
  `maSKU` VARCHAR(30) NOT NULL,
  `maViTri` VARCHAR(20) NOT NULL,
  `soLuongDieuPhoi` DECIMAL(15,3) NOT NULL,
  `soLuongThucHien` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `trangThai` ENUM('CHO_NHAP', 'DA_NHAP', 'LOI') NOT NULL DEFAULT 'CHO_NHAP',
  PRIMARY KEY (`id`),
  KEY `idx_ctldp_lenh` (`maLenhDieuPhoi`),
  KEY `idx_ctldp_lo` (`maLo`),
  KEY `idx_ctldp_sku` (`maSKU`),
  KEY `idx_ctldp_vitri` (`maViTri`),
  CONSTRAINT `chk_ctldp_soluong_dp` CHECK (`soLuongDieuPhoi` > 0),
  CONSTRAINT `chk_ctldp_soluong_th` CHECK (`soLuongThucHien` >= 0),
  CONSTRAINT `fk_ctldp_lenh` FOREIGN KEY (`maLenhDieuPhoi`)
    REFERENCES `lenh_dieu_phoi_kho` (`maLenhDieuPhoi`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_ctldp_lo` FOREIGN KEY (`maLo`)
    REFERENCES `lo_hang` (`maLo`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_ctldp_sku` FOREIGN KEY (`maSKU`)
    REFERENCES `mat_hang` (`maSKU`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_ctldp_vitri` FOREIGN KEY (`maViTri`)
    REFERENCES `vi_tri_luu_kho` (`maViTri`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 33: Phiếu xuất kho (phieu_xuat_kho)
CREATE TABLE `phieu_xuat_kho` (
  `maPhieuXuat` VARCHAR(30) NOT NULL,
  `loaiXuat` ENUM('XUAT_SAN_XUAT', 'XUAT_BAN_HANG', 'XUAT_CHUYEN_KHO', 'XUAT_HUY', 'XUAT_TRA_NCC') NOT NULL,
  `maChungTuThamChieu` VARCHAR(50) NULL,
  `maKho` VARCHAR(20) NOT NULL,
  `ngayLap` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ngayXuat` DATETIME NULL,
  `maNhanVienLap` VARCHAR(20) NOT NULL,
  `maNhanVienXuat` VARCHAR(20) NULL,
  `trangThai` ENUM('CHO_XUAT', 'DA_XUAT', 'HUY') NOT NULL DEFAULT 'CHO_XUAT',
  `ghiChu` TEXT NULL,
  PRIMARY KEY (`maPhieuXuat`),
  KEY `idx_pxk_kho` (`maKho`),
  KEY `idx_pxk_nvlap` (`maNhanVienLap`),
  KEY `idx_pxk_nvxuat` (`maNhanVienXuat`),
  CONSTRAINT `fk_pxk_kho` FOREIGN KEY (`maKho`)
    REFERENCES `kho` (`maKho`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_pxk_nvlap` FOREIGN KEY (`maNhanVienLap`)
    REFERENCES `nhan_vien` (`maNhanVien`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_pxk_nvxuat` FOREIGN KEY (`maNhanVienXuat`)
    REFERENCES `nhan_vien` (`maNhanVien`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 34: Chi tiết phiếu xuất kho (chi_tiet_phieu_xuat_kho)
CREATE TABLE `chi_tiet_phieu_xuat_kho` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `maPhieuXuat` VARCHAR(30) NOT NULL,
  `maLo` VARCHAR(30) NOT NULL,
  `maViTri` VARCHAR(20) NOT NULL,
  `maSKU` VARCHAR(30) NOT NULL,
  `soLuong` DECIMAL(15,3) NOT NULL,
  `donGia` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `thanhTien` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_ctpxk_phieu` (`maPhieuXuat`),
  KEY `idx_ctpxk_lo` (`maLo`),
  KEY `idx_ctpxk_vitri` (`maViTri`),
  KEY `idx_ctpxk_sku` (`maSKU`),
  CONSTRAINT `chk_ctpxk_soluong` CHECK (`soLuong` > 0),
  CONSTRAINT `chk_ctpxk_dongia` CHECK (`donGia` >= 0),
  CONSTRAINT `chk_ctpxk_thanhtien` CHECK (`thanhTien` >= 0),
  CONSTRAINT `fk_ctpxk_phieu` FOREIGN KEY (`maPhieuXuat`)
    REFERENCES `phieu_xuat_kho` (`maPhieuXuat`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_ctpxk_lo` FOREIGN KEY (`maLo`)
    REFERENCES `lo_hang` (`maLo`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_ctpxk_vitri` FOREIGN KEY (`maViTri`)
    REFERENCES `vi_tri_luu_kho` (`maViTri`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_ctpxk_sku` FOREIGN KEY (`maSKU`)
    REFERENCES `mat_hang` (`maSKU`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 35: Phiếu phân bổ số lượng (phieu_phan_bo_so_luong)
CREATE TABLE `phieu_phan_bo_so_luong` (
  `maPhieuPhanBo` VARCHAR(30) NOT NULL,
  `maLenhSX` VARCHAR(30) NULL,
  `maKhoNguon` VARCHAR(20) NOT NULL,
  `loaiNoiNhan` ENUM('LENH_SAN_XUAT', 'DON_HANG', 'KHO') NOT NULL,
  `maNoiNhan` VARCHAR(50) NULL,
  `ngayPhanBo` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `maNhanVien` VARCHAR(20) NOT NULL,
  `trangThai` ENUM('DANG_PHAN_BO', 'HOAN_THANH', 'HUY') NOT NULL DEFAULT 'HOAN_THANH',
  `ghiChu` TEXT NULL,
  PRIMARY KEY (`maPhieuPhanBo`),
  KEY `idx_ppb_lsx` (`maLenhSX`),
  KEY `idx_ppb_khonguon` (`maKhoNguon`),
  KEY `idx_ppb_nv` (`maNhanVien`),
  CONSTRAINT `fk_ppb_lsx` FOREIGN KEY (`maLenhSX`)
    REFERENCES `lenh_san_xuat` (`maLenhSX`) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_ppb_khonguon` FOREIGN KEY (`maKhoNguon`)
    REFERENCES `kho` (`maKho`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_ppb_nv` FOREIGN KEY (`maNhanVien`)
    REFERENCES `nhan_vien` (`maNhanVien`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 36: Chi tiết phân bổ (chi_tiet_phan_bo)
CREATE TABLE `chi_tiet_phan_bo` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `maPhieuPhanBo` VARCHAR(30) NOT NULL,
  `maLo` VARCHAR(30) NOT NULL,
  `maViTri` VARCHAR(20) NOT NULL,
  `maSKU` VARCHAR(30) NOT NULL,
  `soLuongPhanBo` DECIMAL(15,3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ctpb_phieu` (`maPhieuPhanBo`),
  KEY `idx_ctpb_lo` (`maLo`),
  KEY `idx_ctpb_vitri` (`maViTri`),
  KEY `idx_ctpb_sku` (`maSKU`),
  CONSTRAINT `chk_ctpb_soluong` CHECK (`soLuongPhanBo` > 0),
  CONSTRAINT `fk_ctpb_phieu` FOREIGN KEY (`maPhieuPhanBo`)
    REFERENCES `phieu_phan_bo_so_luong` (`maPhieuPhanBo`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_ctpb_lo` FOREIGN KEY (`maLo`)
    REFERENCES `lo_hang` (`maLo`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_ctpb_vitri` FOREIGN KEY (`maViTri`)
    REFERENCES `vi_tri_luu_kho` (`maViTri`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_ctpb_sku` FOREIGN KEY (`maSKU`)
    REFERENCES `mat_hang` (`maSKU`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 37: Phiếu điều chuyển nội bộ (phieu_dieu_chuyen_noi_bo)
CREATE TABLE `phieu_dieu_chuyen_noi_bo` (
  `maPhieuDieuChuyen` VARCHAR(30) NOT NULL,
  `maKhoXuat` VARCHAR(20) NOT NULL,
  `maKhoNhap` VARCHAR(20) NOT NULL,
  `ngayDieuChuyen` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `maNhanVienLap` VARCHAR(20) NOT NULL,
  `trangThai` ENUM('CHO_CHUYEN', 'DANG_CHUYEN', 'HOAN_THANH', 'HUY') NOT NULL DEFAULT 'CHO_CHUYEN',
  `ghiChu` TEXT NULL,
  PRIMARY KEY (`maPhieuDieuChuyen`),
  KEY `idx_pdc_khoxuat` (`maKhoXuat`),
  KEY `idx_pdc_khonhap` (`maKhoNhap`),
  KEY `idx_pdc_nv` (`maNhanVienLap`),
  CONSTRAINT `fk_pdc_khoxuat` FOREIGN KEY (`maKhoXuat`)
    REFERENCES `kho` (`maKho`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_pdc_khonhap` FOREIGN KEY (`maKhoNhap`)
    REFERENCES `kho` (`maKho`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_pdc_nv` FOREIGN KEY (`maNhanVienLap`)
    REFERENCES `nhan_vien` (`maNhanVien`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 38: Giao dịch kho (giao_dich_kho)
-- Phục vụ BR-04: Mọi thay đổi tồn kho đều đi kèm chứng từ và ghi giao dịch
CREATE TABLE `giao_dich_kho` (
  `maGiaoDich` BIGINT AUTO_INCREMENT NOT NULL,
  `loaiGiaoDich` ENUM('NHAP', 'XUAT', 'DIEU_CHUYEN', 'PHAN_BO_GIU_CHO', 'DIEU_CHINH_KIEM_KE') NOT NULL,
  `maChungTu` VARCHAR(50) NOT NULL,
  `loaiChungTu` VARCHAR(50) NOT NULL,
  `ngayGiaoDich` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `maLo` VARCHAR(30) NOT NULL,
  `maViTri` VARCHAR(20) NOT NULL,
  `maSKU` VARCHAR(30) NOT NULL,
  `soLuongThayDoi` DECIMAL(15,3) NOT NULL,
  `tonTruocGiaoDich` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `tonSauGiaoDich` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `maNhanVien` VARCHAR(20) NOT NULL,
  `ghiChu` TEXT NULL,
  PRIMARY KEY (`maGiaoDich`),
  KEY `idx_gdk_chungtu` (`maChungTu`),
  KEY `idx_gdk_lo` (`maLo`),
  KEY `idx_gdk_vitri` (`maViTri`),
  KEY `idx_gdk_sku` (`maSKU`),
  KEY `idx_gdk_nv` (`maNhanVien`),
  CONSTRAINT `fk_gdk_lo` FOREIGN KEY (`maLo`)
    REFERENCES `lo_hang` (`maLo`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_gdk_vitri` FOREIGN KEY (`maViTri`)
    REFERENCES `vi_tri_luu_kho` (`maViTri`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_gdk_sku` FOREIGN KEY (`maSKU`)
    REFERENCES `mat_hang` (`maSKU`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_gdk_nv` FOREIGN KEY (`maNhanVien`)
    REFERENCES `nhan_vien` (`maNhanVien`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================
-- PHÂN HỆ 5: KIỂM KÊ KHO
-- ==============================================================

-- Bảng 39: Biên bản kiểm kê (bien_ban_kiem_ke)
CREATE TABLE `bien_ban_kiem_ke` (
  `maBienBan` VARCHAR(32) NOT NULL,
  `ngayKiemKe` DATE NOT NULL,
  `maKho` VARCHAR(20) NOT NULL,
  `maNhanVienLap` VARCHAR(20) NOT NULL,
  `trangThai` ENUM('DANG_KIEM_KE', 'CHO_QLK_DUYET', 'CHO_BGD_DUYET', 'DA_DUYET', 'TU_CHOI') NOT NULL DEFAULT 'DANG_KIEM_KE',
  `ngayCapNhat` DATETIME NULL,
  `maNhanVienCapNhat` VARCHAR(20) NULL,
  `tongGiaTriThua` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `tongGiaTriThieu` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `tongGiaTriChenhLech` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `giaTriChenhLechRong` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `lyDoChenhLech` TEXT NULL,
  `yKienPheDuyet` TEXT NULL,
  PRIMARY KEY (`maBienBan`),
  KEY `idx_bbkk_kho` (`maKho`),
  KEY `idx_bbkk_nvlap` (`maNhanVienLap`),
  KEY `idx_bbkk_trangthai` (`trangThai`),
  CONSTRAINT `fk_bbkk_kho` FOREIGN KEY (`maKho`)
    REFERENCES `kho` (`maKho`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_bbkk_nvlap` FOREIGN KEY (`maNhanVienLap`)
    REFERENCES `nhan_vien` (`maNhanVien`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_bbkk_nvcapnhat` FOREIGN KEY (`maNhanVienCapNhat`)
    REFERENCES `nhan_vien` (`maNhanVien`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 40: Chi tiết kiểm kê (chi_tiet_kiem_ke)
CREATE TABLE `chi_tiet_kiem_ke` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `maBienBan` VARCHAR(32) NOT NULL,
  `maSKU` VARCHAR(30) NOT NULL,
  `maLo` VARCHAR(30) NULL,
  `maViTri` VARCHAR(20) NULL,
  `soLuongSoSach` DECIMAL(15,3) NULL,
  `soLuongThucTe` DECIMAL(15,3) NULL,
  `donGia` DECIMAL(18,2) NULL,
  `soLuongChenhLech` DECIMAL(15,3) NULL,
  `phanLoai` VARCHAR(32) NULL,
  `giaTriChenhLech` DECIMAL(18,2) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ctkk_bienban_sku_lo` (`maBienBan`, `maSKU`, `maLo`, `maViTri`),
  KEY `idx_ctkk_sku` (`maSKU`),
  KEY `idx_ctkk_lo` (`maLo`),
  KEY `idx_ctkk_vitri` (`maViTri`),
  CONSTRAINT `fk_ctkk_bienban` FOREIGN KEY (`maBienBan`)
    REFERENCES `bien_ban_kiem_ke` (`maBienBan`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_ctkk_sku` FOREIGN KEY (`maSKU`)
    REFERENCES `mat_hang` (`maSKU`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_ctkk_lo` FOREIGN KEY (`maLo`)
    REFERENCES `lo_hang` (`maLo`) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_ctkk_vitri` FOREIGN KEY (`maViTri`)
    REFERENCES `vi_tri_luu_kho` (`maViTri`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 41: Thành viên ban kiểm kê (thanh_vien_kiem_ke)
CREATE TABLE `thanh_vien_kiem_ke` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `maBienBan` VARCHAR(32) NOT NULL,
  `maNhanVien` VARCHAR(20) NOT NULL,
  `vaiTroKiemKe` VARCHAR(50) NOT NULL DEFAULT 'THANH_VIEN',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tvkk_bienban_nv` (`maBienBan`, `maNhanVien`),
  KEY `idx_tvkk_nv` (`maNhanVien`),
  CONSTRAINT `fk_tvkk_bienban` FOREIGN KEY (`maBienBan`)
    REFERENCES `bien_ban_kiem_ke` (`maBienBan`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_tvkk_nv` FOREIGN KEY (`maNhanVien`)
    REFERENCES `nhan_vien` (`maNhanVien`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================
-- PHÂN HỆ 6: CHẤT LƯỢNG & HÀNG LỖI HỎNG
-- ==============================================================

-- Bảng 42: Biên bản kiểm tra chất lượng (bien_ban_kiem_tra_chat_luong)
CREATE TABLE `bien_ban_kiem_tra_chat_luong` (
  `maBienBanQC` VARCHAR(32) NOT NULL,
  `maPhieuNhap` VARCHAR(30) NULL,
  `maLo` VARCHAR(30) NOT NULL,
  `maSKU` VARCHAR(30) NOT NULL,
  `ngayKiemTra` DATE NOT NULL,
  `maNhanVienQC` VARCHAR(20) NOT NULL,
  `soLuongKiemTra` DECIMAL(15,3) NOT NULL,
  `soLuongDat` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `soLuongKhongDat` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `ketLuan` ENUM('DAT', 'KHONG_DAT', 'CACH_LY') NOT NULL,
  `ghiChu` TEXT NULL,
  PRIMARY KEY (`maBienBanQC`),
  KEY `idx_bbqc_pnk` (`maPhieuNhap`),
  KEY `idx_bbqc_lo` (`maLo`),
  KEY `idx_bbqc_sku` (`maSKU`),
  KEY `idx_bbqc_nvqc` (`maNhanVienQC`),
  CONSTRAINT `chk_bbqc_sl_kiemtra` CHECK (`soLuongKiemTra` >= 0),
  CONSTRAINT `chk_bbqc_sl_dat` CHECK (`soLuongDat` >= 0),
  CONSTRAINT `chk_bbqc_sl_khongdat` CHECK (`soLuongKhongDat` >= 0),
  CONSTRAINT `fk_bbqc_pnk` FOREIGN KEY (`maPhieuNhap`)
    REFERENCES `phieu_nhap_kho` (`maPhieuNhap`) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_bbqc_lo` FOREIGN KEY (`maLo`)
    REFERENCES `lo_hang` (`maLo`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_bbqc_sku` FOREIGN KEY (`maSKU`)
    REFERENCES `mat_hang` (`maSKU`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_bbqc_nvqc` FOREIGN KEY (`maNhanVienQC`)
    REFERENCES `nhan_vien` (`maNhanVien`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 43: Biên bản trả hàng từ khách (bien_ban_tra_hang)
CREATE TABLE `bien_ban_tra_hang` (
  `maBienBanTra` VARCHAR(32) NOT NULL,
  `maKhachHang` VARCHAR(20) NULL,
  `ngayTra` DATE NOT NULL,
  `maNhanVienTiepNhan` VARCHAR(20) NOT NULL,
  `lyDoTra` TEXT NOT NULL,
  `trangThai` ENUM('CHO_XU_LY', 'DA_XU_LY') NOT NULL DEFAULT 'CHO_XU_LY',
  PRIMARY KEY (`maBienBanTra`),
  KEY `idx_bbth_kh` (`maKhachHang`),
  KEY `idx_bbth_nv` (`maNhanVienTiepNhan`),
  CONSTRAINT `fk_bbth_kh` FOREIGN KEY (`maKhachHang`)
    REFERENCES `khach_hang` (`maKhachHang`) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_bbth_nv` FOREIGN KEY (`maNhanVienTiepNhan`)
    REFERENCES `nhan_vien` (`maNhanVien`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 44: Hàng lỗi hỏng (hang_loi_hong)
CREATE TABLE `hang_loi_hong` (
  `maHangLoi` VARCHAR(20) NOT NULL,
  `maSKU` VARCHAR(30) NOT NULL,
  `maLo` VARCHAR(30) NULL,
  `maKho` VARCHAR(20) NOT NULL,
  `nguonGoc` VARCHAR(150) NOT NULL,
  `maChungTuNguon` VARCHAR(40) NOT NULL,
  `ngayGhiNhan` DATE NOT NULL,
  `nguyenNhan` VARCHAR(500) NOT NULL,
  `soLuongLoiHong` DECIMAL(15,3) NOT NULL,
  `donGia` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `trangThai` ENUM('CHO_XU_LY', 'DANG_XU_LY', 'DA_XU_LY') NOT NULL DEFAULT 'CHO_XU_LY',
  PRIMARY KEY (`maHangLoi`),
  KEY `idx_hlh_sku` (`maSKU`),
  KEY `idx_hlh_lo` (`maLo`),
  KEY `idx_hlh_kho` (`maKho`),
  CONSTRAINT `chk_hlh_sl_loihong` CHECK (`soLuongLoiHong` > 0),
  CONSTRAINT `chk_hlh_dongia` CHECK (`donGia` >= 0),
  CONSTRAINT `fk_hlh_sku` FOREIGN KEY (`maSKU`)
    REFERENCES `mat_hang` (`maSKU`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_hlh_lo` FOREIGN KEY (`maLo`)
    REFERENCES `lo_hang` (`maLo`) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_hlh_kho` FOREIGN KEY (`maKho`)
    REFERENCES `kho` (`maKho`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 45: Đề xuất xử lý hàng lỗi (de_xuat_xu_ly_hang_loi)
CREATE TABLE `de_xuat_xu_ly_hang_loi` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `maDeXuat` VARCHAR(32) NOT NULL UNIQUE,
  `ngayLap` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `maNhanVienLap` VARCHAR(20) NOT NULL,
  `phuongAn` ENUM('TIEU_HUY', 'TRA_NCC') NOT NULL,
  `lyDo` TEXT NOT NULL,
  `trangThai` ENUM('CHO_XU_LY', 'CHO_BGD_DUYET', 'DA_DUYET', 'TU_CHOI', 'DA_XU_LY') NOT NULL DEFAULT 'CHO_XU_LY',
  `tongGiaTri` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_dxhl_nv` (`maNhanVienLap`),
  KEY `idx_dxhl_trangthai` (`trangThai`),
  CONSTRAINT `chk_dxhl_tonggiatri` CHECK (`tongGiaTri` >= 0),
  CONSTRAINT `fk_dxhl_nv` FOREIGN KEY (`maNhanVienLap`)
    REFERENCES `nhan_vien` (`maNhanVien`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 46: Chi tiết đề xuất xử lý hàng lỗi (chi_tiet_de_xuat_xu_ly)
CREATE TABLE `chi_tiet_de_xuat_xu_ly` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `deXuatId` INT NOT NULL,
  `maHangLoi` VARCHAR(20) NOT NULL,
  `maSKU` VARCHAR(30) NOT NULL,
  `soLo` VARCHAR(40) NOT NULL,
  `ngayHetHan` DATE NOT NULL,
  `soLuongXuLy` DECIMAL(15,3) NOT NULL,
  `donGia` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `thanhTien` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_ctdx_dexuat` (`deXuatId`),
  KEY `idx_ctdx_hangloi` (`maHangLoi`),
  KEY `idx_ctdx_sku` (`maSKU`),
  CONSTRAINT `chk_ctdx_sl_xuly` CHECK (`soLuongXuLy` > 0),
  CONSTRAINT `chk_ctdx_dongia` CHECK (`donGia` >= 0),
  CONSTRAINT `chk_ctdx_thanhtien` CHECK (`thanhTien` >= 0),
  CONSTRAINT `fk_ctdx_dexuat` FOREIGN KEY (`deXuatId`)
    REFERENCES `de_xuat_xu_ly_hang_loi` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_ctdx_hangloi` FOREIGN KEY (`maHangLoi`)
    REFERENCES `hang_loi_hong` (`maHangLoi`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_ctdx_sku` FOREIGN KEY (`maSKU`)
    REFERENCES `mat_hang` (`maSKU`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================
-- DATABASE VIEWS CHUẨN NGHIỆP VỤ WMS
-- ==============================================================

-- View 1: Tồn kho theo lô x vị trí (vw_ton_kho_lo)
-- Bỏ lô hết hạn, bỏ lô trạng thái CHO_KIEM, KHONG_DAT, CACH_LY; tính khả dụng = thucTe - giuCho
CREATE OR REPLACE VIEW `vw_ton_kho_lo` AS
SELECT 
    tk.maLo,
    tk.maViTri,
    lh.maSKU,
    mh.tenHang,
    mh.donViTinh,
    vt.maKho,
    k.tenKho,
    lh.ngaySanXuat,
    lh.ngayHetHan,
    lh.trangThaiChatLuong,
    tk.soLuongThucTe,
    tk.soLuongGiuCho,
    (tk.soLuongThucTe - tk.soLuongGiuCho) AS tonKhaDungLo
FROM ton_kho tk
JOIN lo_hang lh ON tk.maLo = lh.maLo
JOIN mat_hang mh ON lh.maSKU = mh.maSKU
JOIN vi_tri_luu_kho vt ON tk.maViTri = vt.maViTri
JOIN kho k ON vt.maKho = k.maKho
WHERE lh.trangThaiChatLuong = 'DAT'
  AND (lh.ngayHetHan IS NULL OR lh.ngayHetHan >= CURDATE())
  AND (tk.soLuongThucTe - tk.soLuongGiuCho) > 0;

-- View 2: Tồn kho khả dụng theo mặt hàng (vw_ton_kho_kha_dung)
-- Theo maSKU = SUM(tonKhaDungLo của vw_ton_kho_lo) - SUM(soLuongCanCap - soLuongDaPhanBo) của LSX DA_PHAT/DANG_PHAN_BO (BR-06)
CREATE OR REPLACE VIEW `vw_ton_kho_kha_dung` AS
SELECT 
    mh.maSKU,
    mh.tenHang,
    mh.loaiHang,
    mh.donViTinh,
    mh.tonToiThieu,
    COALESCE(ton_lo.tongTonKhaDungLo, 0.000) AS tongTonKhaDungLo,
    COALESCE(giu_cho_lsx.tongGiuChoLSX, 0.000) AS tongGiuChoLSX,
    GREATEST(0.000, COALESCE(ton_lo.tongTonKhaDungLo, 0.000) - COALESCE(giu_cho_lsx.tongGiuChoLSX, 0.000)) AS tonKhaDung
FROM mat_hang mh
LEFT JOIN (
    SELECT maSKU, SUM(tonKhaDungLo) AS tongTonKhaDungLo
    FROM vw_ton_kho_lo
    GROUP BY maSKU
) ton_lo ON mh.maSKU = ton_lo.maSKU
LEFT JOIN (
    SELECT 
        nvl.maSKU, 
        SUM(nvl.soLuongCanCap - nvl.soLuongDaPhanBo) AS tongGiuChoLSX
    FROM nvl_can_cap_lenh_sx nvl
    JOIN lenh_san_xuat lsx ON nvl.maLenhSX = lsx.maLenhSX
    WHERE lsx.trangThai IN ('DA_PHAT', 'DANG_PHAN_BO')
      AND (nvl.soLuongCanCap - nvl.soLuongDaPhanBo) > 0
    GROUP BY nvl.maSKU
) giu_cho_lsx ON mh.maSKU = giu_cho_lsx.maSKU;

-- View 3: Hàng đang về theo đơn mua (vw_hang_dang_ve)
-- Theo maSKU = SUM(soLuongDat - soLuongDaNhan) của các đơn mua hàng chưa hoàn tất
CREATE OR REPLACE VIEW `vw_hang_dang_ve` AS
SELECT 
    ct.maSKU,
    mh.tenHang,
    mh.donViTinh,
    SUM(ct.soLuongDat - ct.soLuongDaNhan) AS hangDangVe
FROM chi_tiet_don_mua_hang ct
JOIN don_mua_hang dmh ON ct.maDonMua = dmh.maDonMua
JOIN mat_hang mh ON ct.maSKU = mh.maSKU
WHERE dmh.trangThai IN ('CHO_DUYET', 'DA_DUYET', 'DANG_GIAO')
  AND (ct.soLuongDat - ct.soLuongDaNhan) > 0
GROUP BY ct.maSKU, mh.tenHang, mh.donViTinh;
