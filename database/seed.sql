-- ==============================================================
-- DỮ LIỆU KHỞI TẠO TOÀN DIỆN (SEED DATA) CHO MARVEL WMS
-- Thống nhất toàn bộ 6 phân hệ nghiệp vụ nhà máy bánh kẹo
-- ==============================================================

USE `marvel_wms`;
SET NAMES utf8mb4;

-- --------------------------------------------------------------
-- 1. BỘ PHẬN & NHÂN VIÊN & TÀI KHOẢN HỆ THỐNG
-- --------------------------------------------------------------

INSERT INTO `bo_phan` (`maBoPhan`, `tenBoPhan`, `moTa`) VALUES
('BP_IT',  'Phòng Công Nghệ Thông Tin', 'Quản trị hệ thống, hạ tầng mạng và phần mềm WMS'),
('BP_BGD', 'Ban Giám Đốc',              'Điều hành doanh nghiệp, phê duyệt kế hoạch và kiểm kê'),
('BP_LKH', 'Bộ Phận Lập Kế Hoạch',     'Lập kế hoạch sản xuất, lập đề nghị mua nguyên vật liệu'),
('BP_KHO', 'Bộ Phận Quản Lý Kho',       'Quản lý kho nguyên vật liệu, thành phẩm, điều phối và kiểm kê'),
('BP_KCS', 'Bộ Phận Kiểm Tra Chất Lượng', 'Kiểm tra chất lượng NVL nhập kho và đề xuất hàng lỗi hỏng');

INSERT INTO `nhan_vien` (`maNhanVien`, `hoTen`, `chucDanh`, `email`, `soDienThoai`, `trangThai`, `maBoPhan`) VALUES
('NV000', 'Quản Trị Viên',   'Kỹ sư hệ thống IT',      'admin@marvelsweet.vn',  '0901000000', 'HOAT_DONG', 'BP_IT'),
('NV001', 'Lê Kế Hoạch',     'Chuyên viên lập kế hoạch', 'kehoach@marvelsweet.vn', '0901000001', 'HOAT_DONG', 'BP_LKH'),
('NV002', 'Quách Quản Kho',  'Quản lý kho trưởng',      'quankho@marvelsweet.vn', '0901000002', 'HOAT_DONG', 'BP_KHO'),
('NV003', 'Ngô Đình Trí',    'Nhân viên vận hành kho',  'dinh.tri@marvelsweet.vn','0901000003', 'HOAT_DONG', 'BP_KHO'),
('NV004', 'Mai Kiểm Phẩm',   'Chuyên viên kiểm định QC', 'qc@marvelsweet.vn',       '0901000004', 'HOAT_DONG', 'BP_KCS'),
('NV005', 'Trần Giám Đốc',   'Giám đốc điều hành',       'giamdoc@marvelsweet.vn', '0901000005', 'HOAT_DONG', 'BP_BGD'),
('NV0001', 'Nguyễn Văn Kho 1', 'Nhân viên bốc xếp kho', 'nvk1@marvelsweet.vn',    '0901000011', 'HOAT_DONG', 'BP_KHO'),
('NV0002', 'Trần Văn Kho 2',   'Nhân viên bốc xếp kho', 'nvk2@marvelsweet.vn',    '0901000012', 'HOAT_DONG', 'BP_KHO'),
('NV0003', 'Lê Văn Kho 3',     'Nhân viên kiểm đếm',    'nvk3@marvelsweet.vn',    '0901000013', 'HOAT_DONG', 'BP_KHO'),
('NV0004', 'Phạm Văn Kho 4',   'Nhân viên xe nâng',     'nvk4@marvelsweet.vn',    '0901000014', 'HOAT_DONG', 'BP_KHO');

INSERT INTO `tai_khoan_nguoi_dung` (`maTaiKhoan`, `tenDangNhap`, `matKhau`, `email`, `soDienThoai`, `vaiTro`, `trangThai`, `maNhanVien`) VALUES
('TK000', 'qtv01', '$2y$10$Ojw2uEWh.j.a3pAIQN5CDe0W1l7dMZDdQy1HQAAlBvcBlcvOM9KkG', 'qtv01@marvelsweet.vn', '0901000000', 'QTV', 'HOAT_DONG', 'NV000'),
('TK001', 'lkh01', '$2y$10$Ojw2uEWh.j.a3pAIQN5CDe0W1l7dMZDdQy1HQAAlBvcBlcvOM9KkG', 'lkh01@marvelsweet.vn', '0901000001', 'LKH', 'HOAT_DONG', 'NV001'),
('TK002', 'qlk01', '$2y$10$Ojw2uEWh.j.a3pAIQN5CDe0W1l7dMZDdQy1HQAAlBvcBlcvOM9KkG', 'qlk01@marvelsweet.vn', '0901000002', 'QLK', 'HOAT_DONG', 'NV002'),
('TK003', 'nvk01', '$2y$10$Ojw2uEWh.j.a3pAIQN5CDe0W1l7dMZDdQy1HQAAlBvcBlcvOM9KkG', 'nvk01@marvelsweet.vn', '0901000003', 'NVK', 'HOAT_DONG', 'NV003'),
('TK004', 'qc01',  '$2y$10$Ojw2uEWh.j.a3pAIQN5CDe0W1l7dMZDdQy1HQAAlBvcBlcvOM9KkG', 'qc01@marvelsweet.vn',  '0901000004', 'QC',  'HOAT_DONG', 'NV004'),
('TK005', 'bgd01', '$2y$10$Ojw2uEWh.j.a3pAIQN5CDe0W1l7dMZDdQy1HQAAlBvcBlcvOM9KkG', 'bgd01@marvelsweet.vn', '0901000005', 'BGD', 'HOAT_DONG', 'NV005'),
('TK006', 'khoa01','$2y$10$Ojw2uEWh.j.a3pAIQN5CDe0W1l7dMZDdQy1HQAAlBvcBlcvOM9KkG', 'khoa01@marvelsweet.vn','0901000006', 'NVK', 'KHOA',      NULL);

INSERT INTO `ban_giam_doc` (`maThanhVien`, `maNhanVien`, `chucVu`, `phamViPheDuyet`) VALUES
('BGD01', 'NV005', 'Giám Đốc Điều Hành', 'Toàn quyền phê duyệt kế hoạch sản xuất, mua hàng và biên bản kiểm kê');

INSERT INTO `nha_cung_cap` (`maNCC`, `tenNCC`, `diaChi`, `soDienThoai`, `email`, `trangThai`) VALUES
('NCC0001', 'Công ty Bột mì An Phát',            'KCN Tân Bình, TP.HCM',      '02838112233', 'anphat@flour.vn',   'HOAT_DONG'),
('NCC0002', 'Công ty Đường Thành Công',          'Biên Hòa, Đồng Nai',        '02513889900', 'thanhcong@sugar.vn', 'HOAT_DONG'),
('NCC0003', 'Công ty Nguyên liệu bánh kẹo Á Châu', 'KCN VSIP 1, Bình Dương',   '02743778899', 'achau@ingredients.vn','HOAT_DONG');

INSERT INTO `khach_hang` (`maKhachHang`, `tenKhachHang`, `diaChi`, `soDienThoai`, `email`, `trangThai`) VALUES
('KH001', 'Cửa hàng Bánh Ngọt An Phú', '123 Cách Mạng Tháng 8, Q.3, TP.HCM', '0912345678', 'anphu@bakery.com',  'HOAT_DONG'),
('KH002', 'Siêu thị Minh Long',        '456 Nguyễn Tri Phương, Q.10, TP.HCM', '0923456789', 'minhlong@mart.com', 'HOAT_DONG'),
('KH003', 'Đại lý Hương Việt',         '789 Lê Lợi, TP. Thủ Đức, TP.HCM',    '0934567890', 'huongviet@agent.com', 'HOAT_DONG'),
('KH004', 'Công ty CP Bánh Kẹo Hải Hà', 'KCN Tiên Sơn, Bắc Ninh',            '0222384759', 'haiha@haiha.vn',     'HOAT_DONG'),
('KH005', 'Chuỗi Bách Hóa Xanh',       '399 Trường Chinh, Q.Tân Bình, TP.HCM','0283838383', 'bhx@bachhoaxanh.com','HOAT_DONG'),
('KH006', 'Hệ thống WinMart',          'Vincom Center, 72 Lê Thánh Tôn, Q.1', '0283939393', 'winmart@masangroup.com','HOAT_DONG'),
('KH007', 'Đại lý Bánh kẹo Miền Nam',  '12 Đại lộ Bình Dương, Thuận An',      '0274384920', 'miennam@distributor.vn','HOAT_DONG'),
('KH008', 'Cửa hàng Bánh kẹo Tràng An','54 Hàng Bông, Hoàn Kiếm, Hà Nội',    '0243829482', 'trangan@bakery.com', 'HOAT_DONG');

-- --------------------------------------------------------------
-- 2. KHO, VỊ TRÍ LƯU KHO & XƯỞNG SẢN XUẤT
-- --------------------------------------------------------------

INSERT INTO `kho` (`maKho`, `tenKho`, `loaiKho`, `diaDiem`, `sucChuaToiDa`, `sucChuaDaSuDung`, `sucChuaDaHenNhap`, `trangThai`) VALUES
('K001', 'Kho nguyên vật liệu chính',    'KHO_NVL',   'Khu A - Tầng 1', 1000.000, 200.000, 50.000, 'HOAT_DONG'),
('K002', 'Kho bột và đường',             'KHO_NVL',   'Khu A - Tầng 2',  800.000, 150.000,  0.000, 'HOAT_DONG'),
('K003', 'Kho hương liệu và phụ gia',    'KHO_NVL',   'Khu B - Tầng 1',  500.000,  50.000,  0.000, 'HOAT_DONG'),
('K004', 'Kho bao bì đóng gói',          'KHO_NVL',   'Khu B - Tầng 2',  600.000, 100.000,  0.000, 'HOAT_DONG'),
('K005', 'Kho thành phẩm bánh quy',      'KHO_TP',    'Khu C - Tầng 1', 1200.000, 300.000,  0.000, 'HOAT_DONG'),
('K006', 'Kho thành phẩm kẹo',          'KHO_TP',    'Khu C - Tầng 2', 1000.000, 250.000,  0.000, 'HOAT_DONG'),
('K007', 'Kho hàng trả về / kiểm định',  'KHO_CHUNG', 'Khu D - Tầng 1',  400.000,  80.000,  0.000, 'HOAT_DONG'),
('K008', 'Kho hàng lỗi chờ tiêu hủy',   'KHO_CHUNG', 'Khu D - Tầng 2',  300.000,  40.000,  0.000, 'HOAT_DONG'),
('K009', 'Kho lưu chuyển bảo trì',       'KHO_CHUNG', 'Khu E - Phụ',     500.000,   0.000,  0.000, 'BAO_TRI'),
('K010', 'Kho lạnh bơ & chocolate',      'KHO_NVL',   'Khu Lạnh - T1',   400.000, 120.000,  0.000, 'HOAT_DONG'),
('K011', 'Kho dự phòng',                 'KHO_CHUNG', 'Khu F - Tầng 1',  500.000,   0.000,  0.000, 'HOAT_DONG');

INSERT INTO `vi_tri_luu_kho` (`maViTri`, `maKho`, `maKe`, `tang`, `oChua`, `loaiHangLuuTru`, `sucChuaToiDa`, `sucChuaDaSuDung`, `trangThai`) VALUES
('VT000001', 'K001', 'KE01', 1, 'O01', 'NVL', 100.000, 25.000, 'HOAT_DONG'),
('VT000002', 'K001', 'KE01', 2, 'O02', 'NVL', 100.000,  5.000, 'HOAT_DONG'),
('VT000003', 'K001', 'KE01', 3, 'O03', 'NVL', 100.000, 15.000, 'HOAT_DONG'),
('VT000004', 'K001', 'KE02', 1, 'O01', 'NVL', 150.000, 120.000,'HOAT_DONG'),
('VT000005', 'K002', 'KE01', 1, 'O01', 'NVL', 100.000, 30.000, 'HOAT_DONG'),
('VT000006', 'K001', 'KE03', 1, 'O01', 'NVL',  50.000,  8.000, 'HOAT_DONG'),
('VT000007', 'K001', 'KE03', 2, 'O02', 'NVL',  50.000, 30.000, 'HOAT_DONG'),
('VT000008', 'K002', 'KE02', 1, 'O01', 'NVL', 100.000, 80.000, 'HOAT_DONG'),
('VT000009', 'K002', 'KE03', 1, 'O01', 'NVL', 100.000,  0.000, 'BAO_TRI'),
('VT000010', 'K003', 'KE01', 1, 'O01', 'NVL',  50.000, 48.000, 'DAY'),
('VT000011', 'K003', 'KE01', 2, 'O02', 'NVL',  50.000,  4.000, 'HOAT_DONG'),
('VT000012', 'K003', 'KE02', 1, 'O01', 'NVL',  50.000, 10.000, 'HOAT_DONG'),
('VT000013', 'K004', 'KE01', 1, 'O01', 'NVL',  80.000,  0.000, 'BAO_TRI'),
('VT000014', 'K004', 'KE01', 2, 'O02', 'NVL',  80.000, 20.000, 'HOAT_DONG'),
('VT000015', 'K005', 'KE01', 1, 'O01', 'TP',  100.000, 50.000, 'HOAT_DONG'),
('VT000016', 'K005', 'KE01', 2, 'O02', 'TP',  100.000, 60.000, 'HOAT_DONG'),
('VT000017', 'K005', 'KE02', 1, 'O01', 'TP',  100.000, 40.000, 'HOAT_DONG'),
('VT000018', 'K005', 'KE02', 2, 'O02', 'TP',  100.000, 50.000, 'HOAT_DONG'),
('VT000019', 'K006', 'KE01', 1, 'O01', 'TP',  100.000, 40.000, 'HOAT_DONG'),
('VT000020', 'K006', 'KE01', 2, 'O02', 'TP',  100.000, 50.000, 'HOAT_DONG'),
('VT000021', 'K006', 'KE02', 1, 'O01', 'TP',  100.000, 30.000, 'HOAT_DONG'),
('VT000022', 'K006', 'KE03', 1, 'O01', 'TP',  120.000,  0.000, 'HOAT_DONG'),
('VT000023', 'K006', 'KE03', 2, 'O02', 'TP',  120.000,  0.000, 'HOAT_DONG'),
('VT000024', 'K007', 'KE01', 1, 'O01', 'CHUNG', 80.000, 20.000, 'HOAT_DONG'),
('VT000025', 'K007', 'KE01', 2, 'O02', 'CHUNG', 80.000, 25.000, 'HOAT_DONG'),
('VT000026', 'K007', 'KE02', 1, 'O01', 'CHUNG', 80.000, 15.000, 'HOAT_DONG'),
('VT000027', 'K008', 'KE01', 1, 'O01', 'CHUNG', 60.000, 18.000, 'HOAT_DONG'),
('VT000028', 'K008', 'KE01', 2, 'O02', 'CHUNG', 60.000, 12.000, 'HOAT_DONG'),
('VT000029', 'K009', 'KE01', 1, 'O01', 'CHUNG',100.000,  0.000, 'BAO_TRI'),
('VT000030', 'K009', 'KE01', 2, 'O02', 'CHUNG',100.000,  0.000, 'BAO_TRI'),
('VT000031', 'K009', 'KE02', 1, 'O01', 'CHUNG',100.000,  0.000, 'BAO_TRI'),
('VT000032', 'K009', 'KE02', 2, 'O02', 'CHUNG',100.000,  0.000, 'BAO_TRI'),
('VT000033', 'K009', 'KE03', 1, 'O01', 'CHUNG',100.000,  0.000, 'BAO_TRI'),
('VT000034', 'K010', 'KE01', 1, 'O01', 'NVL',  80.000, 45.000, 'HOAT_DONG'),
('VT000035', 'K010', 'KE01', 2, 'O02', 'NVL',  80.000, 12.000, 'HOAT_DONG'),
('VT000036', 'K010', 'KE02', 1, 'O01', 'NVL',  80.000,  8.000, 'HOAT_DONG'),
('VT000037', 'K010', 'KE02', 2, 'O02', 'NVL',  80.000, 45.000, 'HOAT_DONG'),
('VT000038', 'K011', 'KE01', 1, 'O01', 'CHUNG',100.000,  0.000, 'HOAT_DONG');

INSERT INTO `xuong_san_xuat` (`maXuong`, `tenXuong`, `viTri`, `congSuat`, `maKhoNVL`, `trangThai`) VALUES
('XSX001', 'Xưởng sản xuất bánh quy bơ', 'Nhà xưởng 1 - Phân khu Đông', 5000.000, 'K001', 'HOAT_DONG'),
('XSX002', 'Xưởng sản xuất kẹo dẻo & kẹo sữa', 'Nhà xưởng 2 - Phân khu Tây', 4000.000, 'K002', 'HOAT_DONG'),
('XSX003', 'Xưởng sản xuất bánh wafer',    'Nhà xưởng 3 - Phân khu Nam', 3000.000, 'K001', 'HOAT_DONG'),
('XSX004', 'Xưởng sản xuất bánh bông lan',  'Nhà xưởng 4 - Phân khu Bắc', 3500.000, 'K001', 'HOAT_DONG'),
('XSX005', 'Xưởng sản xuất kẹo chocolate',  'Nhà xưởng 5 - Phân khu Lạnh', 2500.000, 'K010', 'HOAT_DONG');

-- --------------------------------------------------------------
-- 3. MẶT HÀNG, ĐỊNH MỨC & PHÂN LOẠI
-- --------------------------------------------------------------

-- Yêu cầu 1: tonToiThieu: NVL001 = 20, NVL004 = 10, NVL005 = 8, NVL006 = 10
INSERT INTO `mat_hang` (`maSKU`, `tenHang`, `loaiHang`, `nhomHang`, `donViTinh`, `quyCachDongGoi`, `giaNhap`, `giaBan`, `tonToiThieu`, `tonToiDa`, `coHanSuDung`, `soNgayCanhBaoHetHan`, `moTa`, `trangThai`) VALUES
('NVL001', 'Đường tinh luyện RE',      'NGUYEN_VAT_LIEU', 'Nguyên liệu chính', 'kg',   'Bao 50kg',    18000.00,     0.00,  20.000, 100.000, 1, 30, 'Đường tinh luyện Biên Hòa RE', 'DANG_KINH_DOANH'),
('NVL002', 'Bột mì đa dụng',          'NGUYEN_VAT_LIEU', 'Nguyên liệu chính', 'kg',   'Bao 25kg',    15000.00,     0.00,  30.000, 300.000, 1, 30, 'Bột mì số 11 chất lượng cao',   'DANG_KINH_DOANH'),
('NVL004', 'Bơ thực vật',              'NGUYEN_VAT_LIEU', 'Chất béo',          'kg',   'Thùng 15kg',  45000.00,     0.00,  10.000, 100.000, 1, 15, 'Bơ thực vật thơm béo làm bánh',  'DANG_KINH_DOANH'),
('NVL005', 'Sữa bột nguyên kem',      'NGUYEN_VAT_LIEU', 'Nguyên liệu sữa',   'kg',   'Bao 25kg',    85000.00,     0.00,   8.000,  50.000, 1, 30, 'Sữa bột New Zealand nhập khẩu',  'DANG_KINH_DOANH'),
('NVL006', 'Chocolate đen',           'NGUYEN_VAT_LIEU', 'Nguyên liệu cacao', 'kg',   'Thùng 20kg', 120000.00,     0.00,  10.000,  80.000, 1, 45, 'Chocolate nguyên chất 70%',     'DANG_KINH_DOANH'),
('NVL007', 'Hương vani',              'NGUYEN_VAT_LIEU', 'Phụ gia',          'lít',  'Can 5 lít',   95000.00,     0.00,   2.000,  20.000, 1, 60, 'Hương vani thực phẩm cô đặc',    'DANG_KINH_DOANH'),
('SP001',  'Bánh quy bơ',             'THANH_PHAM',      'Bánh khô',         'hộp',  'Hộp 300g',    24000.00, 32000.00,  50.000, 500.000, 1, 30, 'Bánh quy bơ hộp thiếc cao cấp', 'DANG_KINH_DOANH'),
('SP002',  'Kẹo chocolate',           'THANH_PHAM',      'Kẹo cứng',         'hộp',  'Hộp 250g',    30000.00, 42000.00,  30.000, 400.000, 1, 45, 'Kẹo chocolate nhân hạnh nhân',  'DANG_KINH_DOANH'),
('SP003',  'Kẹo sữa',                 'THANH_PHAM',      'Kẹo dẻo',          'hộp',  'Hộp 200g',    12000.00, 18000.00,  40.000, 400.000, 1, 30, 'Kẹo sữa caramen mềm béo',       'DANG_KINH_DOANH'),
('BK-QB-001', 'Bánh quy bơ hộp 300g',  'THANH_PHAM',      'Bánh quy',         'Hộp',  'Hộp 300g',    24000.00, 32000.00,  30.000, 300.000, 1, 30, 'Bánh quy bơ hộp 300g chuẩn kiểm kê/lỗi', 'DANG_KINH_DOANH'),
('BK-KD-002', 'Kẹo dẻo trái cây gói 100g', 'THANH_PHAM',  'Kẹo dẻo',          'Gói',  'Gói 100g',    10000.00, 15000.00,  40.000, 400.000, 1, 30, 'Kẹo dẻo trái cây gói 100g chuẩn kiểm kê/lỗi', 'DANG_KINH_DOANH'),
('BK-BL-003', 'Bánh bông lan cuộn 180g',   'THANH_PHAM',  'Bánh mềm',         'Gói',  'Gói 180g',    20000.00, 28000.00,  20.000, 200.000, 1, 15, 'Bánh bông lan cuộn kem dâu',  'DANG_KINH_DOANH'),
('BK-SC-004', 'Socola hạnh nhân thanh 80g','THANH_PHAM',  'Socola',           'Thanh','Thanh 80g',   32000.00, 42000.00,  25.000, 250.000, 1, 45, 'Thanh socola hạnh nhân giòn tan', 'DANG_KINH_DOANH'),
('BK-BG-005', 'Bánh gạo rong biển 100g',   'THANH_PHAM',  'Bánh gạo',         'Gói',  'Gói 100g',    16000.00, 22000.00,  30.000, 300.000, 1, 30, 'Bánh gạo nướng vị rong biển',  'DANG_KINH_DOANH'),
('BK-KS-006', 'Kẹo sữa viên túi 120g',     'THANH_PHAM',  'Kẹo sữa',          'Túi',  'Túi 120g',    13000.00, 18000.00,  35.000, 350.000, 1, 30, 'Kẹo sữa bò nguyên chất túi 120g','DANG_KINH_DOANH'),
('BK-CP-007', 'Bánh phủ socola hộp 12 chiếc','THANH_PHAM','Bánh phủ socola',   'Hộp',  'Hộp 12 cái',  38000.00, 49000.00,  20.000, 200.000, 1, 30, 'Bánh chocopie phủ socola đậm đà', 'DANG_KINH_DOANH');

INSERT INTO `nguyen_vat_lieu` (`maSKU`, `tonToiThieu`) VALUES
('NVL001', 20.000),
('NVL002', 30.000),
('NVL004', 10.000),
('NVL005',  8.000),
('NVL006', 10.000),
('NVL007',  2.000);

INSERT INTO `thanh_pham` (`maSKU`, `quyCachDongGoi`) VALUES
('SP001', 'Hộp 300g'),
('SP002', 'Hộp 250g'),
('SP003', 'Hộp 200g'),
('BK-QB-001', 'Hộp 300g'),
('BK-KD-002', 'Gói 100g'),
('BK-BL-003', 'Gói 180g'),
('BK-SC-004', 'Thanh 80g'),
('BK-BG-005', 'Gói 100g'),
('BK-KS-006', 'Túi 120g'),
('BK-CP-007', 'Hộp 12 cái');

INSERT INTO `dinh_muc_nvl` (`maThanhPham`, `maNVL`, `soLuongDinhMuc`, `donViTinh`, `trangThai`) VALUES
('SP001', 'NVL001', 0.020, 'kg', 'HOAT_DONG'),
('SP001', 'NVL002', 0.080, 'kg', 'HOAT_DONG'),
('SP001', 'NVL004', 0.030, 'kg', 'HOAT_DONG'),
('SP002', 'NVL001', 0.020, 'kg', 'HOAT_DONG'),
('SP002', 'NVL005', 0.010, 'kg', 'HOAT_DONG'),
('SP002', 'NVL006', 0.060, 'kg', 'HOAT_DONG'),
('SP003', 'NVL001', 0.030, 'kg', 'HOAT_DONG'),
('SP003', 'NVL005', 0.040, 'kg', 'HOAT_DONG');

-- --------------------------------------------------------------
-- 4. ĐƠN HÀNG BÁN & KẾ HOẠCH SẢN XUẤT
-- --------------------------------------------------------------

INSERT INTO `don_hang` (`maDonHang`, `maKhachHang`, `ngayDat`, `ngayGiaoDuKien`, `trangThai`, `tongTien`, `ghiChu`) VALUES
('DH001', 'KH001', '2026-10-20', '2026-11-12', 'CHO_SAN_XUAT', 16000000.00, 'Đơn hàng bánh quy bơ giao đợt 1'),
('DH002', 'KH002', '2026-10-21', '2026-11-18', 'CHO_SAN_XUAT',  9600000.00, 'Giao siêu thị Minh Long đúng lịch'),
('DH003', 'KH003', '2026-10-22', '2026-11-25', 'CHO_SAN_XUAT',  7200000.00, 'Đại lý Hương Việt đặt kẹo sữa'),
('DH004', 'KH004', '2026-10-23', '2026-11-28', 'CHO_SAN_XUAT', 10500000.00, 'Bánh kẹo Hải Hà đặt kẹo chocolate'),
('DH005', 'KH005', '2026-10-24', '2026-12-05', 'CHO_XU_LY',    19200000.00, 'Bách Hóa Xanh đặt hàng Tết'),
('DH006', 'KH006', '2026-10-25', '2026-12-10', 'CHO_XU_LY',    12800000.00, 'WinMart đặt bánh quy'),
('DH007', 'KH007', '2026-10-26', '2026-12-15', 'CHO_XU_LY',    39000000.00, 'Đại lý miền Nam đặt số lượng lớn'),
('DH008', 'KH008', '2026-10-27', '2026-12-20', 'CHO_XU_LY',    16000000.00, 'Cửa hàng Tràng An đặt bổ sung');

INSERT INTO `chi_tiet_don_hang` (`maDonHang`, `maSKU`, `soLuong`, `donGia`, `thanhTien`) VALUES
('DH001', 'SP001', 500.000, 32000.00, 16000000.00),
('DH002', 'SP001', 300.000, 32000.00,  9600000.00),
('DH003', 'SP003', 400.000, 18000.00,  7200000.00),
('DH004', 'SP002', 250.000, 42000.00, 10500000.00),
('DH005', 'SP001', 600.000, 32000.00, 19200000.00),
('DH006', 'SP001', 400.000, 32000.00, 12800000.00),
('DH007', 'SP002', 800.000, 42000.00, 33600000.00),
('DH007', 'SP003', 300.000, 18000.00,  5400000.00),
('DH008', 'SP001', 500.000, 32000.00, 16000000.00);

-- Kế hoạch sản xuất: KHSX001..KHSX005
INSERT INTO `ke_hoach_san_xuat` (`maKeHoach`, `maNhanVienLap`, `maXuong`, `ngayLap`, `ngayBatDau`, `ngayHoanThanh`, `trangThai`, `ghiChu`) VALUES
('KHSX001', 'NV001', 'XSX001', '2026-10-27 08:30:00', '2026-11-01', '2026-11-07', 'DA_DUYET', 'Kế hoạch sản xuất đợt 1 bánh quy bơ (Đủ NVL)'),
('KHSX002', 'NV001', 'XSX002', '2026-10-27 09:00:00', '2026-11-10', '2026-11-15', 'CHO_DUYET', 'Kế hoạch sản xuất kẹo sữa đợt 1'),
('KHSX003', 'NV001', 'XSX001', '2026-10-27 10:00:00', '2026-11-05', '2026-11-10', 'DA_DUYET', 'Kế hoạch sản xuất bánh quy bơ lớn (Đã phát 2 lệnh)'),
('KHSX004', 'NV001', 'XSX002', '2026-10-27 10:30:00', '2026-11-12', '2026-11-18', 'DA_DUYET', 'Kế hoạch sản xuất kẹo chocolate & sữa (Thiếu NVL005 & NVL006)'),
('KHSX005', 'NV001', 'XSX001', '2026-10-27 11:00:00', '2026-11-20', '2026-11-26', 'DA_DUYET', 'Kế hoạch sản xuất bánh quy bổ sung (Đã phát 1 lệnh)');

-- Yêu cầu 2: Cộng soLuongDaGiaoLenh tương ứng vào chi_tiet_ke_hoach_san_xuat
-- KHSX003 đã giao 400 + 600 = 1000 hộp; KHSX005 đã giao 500 hộp
INSERT INTO `chi_tiet_ke_hoach_san_xuat` (`maKeHoach`, `maSKU`, `soLuongSanXuat`, `soLuongDaGiaoLenh`) VALUES
('KHSX001', 'SP001',  800.000,    0.000),
('KHSX002', 'SP003',  400.000,    0.000),
('KHSX003', 'SP001', 1000.000, 1000.000),
('KHSX004', 'SP002',  800.000,    0.000),
('KHSX004', 'SP003',  300.000,    0.000),
('KHSX005', 'SP001',  500.000,  500.000);

-- Nhu cầu nguyên vật liệu từ định mức
INSERT INTO `nhu_cau_nvl` (`maKeHoach`, `maNVL`, `soLuongCan`) VALUES
('KHSX001', 'NVL001', 16.000),
('KHSX001', 'NVL002', 64.000),
('KHSX001', 'NVL004', 24.000),
('KHSX002', 'NVL001', 12.000),
('KHSX002', 'NVL005', 16.000),
('KHSX003', 'NVL001', 20.000),
('KHSX003', 'NVL002', 80.000),
('KHSX003', 'NVL004', 30.000),
('KHSX004', 'NVL001', 25.000),
('KHSX004', 'NVL005', 20.000),
('KHSX004', 'NVL006', 48.000),
('KHSX005', 'NVL001', 10.000),
('KHSX005', 'NVL002', 40.000),
('KHSX005', 'NVL004', 15.000);

-- --------------------------------------------------------------
-- 5. LỆNH SẢN XUẤT & NVL CẦN CẤP (CHO PROMPT 5 VÀ BR-06)
-- --------------------------------------------------------------

-- Yêu cầu 2: 3 lệnh sản xuất DA_PHAT lấy từ KHSX003 (giao 400 + 600) và KHSX005 (giao 500)
INSERT INTO `lenh_san_xuat` (`maLenhSX`, `maKeHoach`, `maXuong`, `maNhanVienLap`, `ngayLap`, `ngayBatDau`, `ngayHoanThanh`, `trangThai`, `ghiChu`) VALUES
('LSX2610001', 'KHSX003', 'XSX001', 'NV001', '2026-10-27 14:00:00', '2026-11-05', '2026-11-08', 'DA_PHAT', 'Lệnh sản xuất 400 hộp bánh quy (Đợt 1 KHSX003)'),
('LSX2610002', 'KHSX003', 'XSX001', 'NV001', '2026-10-27 15:00:00', '2026-11-08', '2026-11-10', 'DA_PHAT', 'Lệnh sản xuất 600 hộp bánh quy (Đợt 2 KHSX003)'),
('LSX2610003', 'KHSX005', 'XSX001', 'NV001', '2026-10-27 16:00:00', '2026-11-20', '2026-11-26', 'DA_PHAT', 'Lệnh sản xuất 500 hộp bánh quy (KHSX005)');

INSERT INTO `chi_tiet_lenh_san_xuat` (`maLenhSX`, `maSKU`, `soLuongGiao`) VALUES
('LSX2610001', 'SP001', 400.000),
('LSX2610002', 'SP001', 600.000),
('LSX2610003', 'SP001', 500.000);

-- nvl_can_cap_lenh_sx trạng thái CHO_PHAN_BO:
-- Định mức SP001: NVL001 (0.02), NVL002 (0.08), NVL004 (0.03)
-- Giữ chỗ theo BR-06:
-- LSX2610001: NVL001=8, NVL002=32, NVL004=12
-- LSX2610002: NVL001=12, NVL002=48, NVL004=18
-- LSX2610003: NVL001=10, NVL002=40, NVL004=15
-- Tổng giữ chỗ: NVL001 = 30kg, NVL002 = 120kg, NVL004 = 45kg
INSERT INTO `nvl_can_cap_lenh_sx` (`maLenhSX`, `maSKU`, `soLuongCanCap`, `soLuongDaPhanBo`, `trangThai`) VALUES
('LSX2610001', 'NVL001',  8.000, 0.000, 'CHO_PHAN_BO'),
('LSX2610001', 'NVL002', 32.000, 0.000, 'CHO_PHAN_BO'),
('LSX2610001', 'NVL004', 12.000, 0.000, 'CHO_PHAN_BO'),
('LSX2610002', 'NVL001', 12.000, 0.000, 'CHO_PHAN_BO'),
('LSX2610002', 'NVL002', 48.000, 0.000, 'CHO_PHAN_BO'),
('LSX2610002', 'NVL004', 18.000, 0.000, 'CHO_PHAN_BO'),
('LSX2610003', 'NVL001', 10.000, 0.000, 'CHO_PHAN_BO'),
('LSX2610003', 'NVL002', 40.000, 0.000, 'CHO_PHAN_BO'),
('LSX2610003', 'NVL004', 15.000, 0.000, 'CHO_PHAN_BO');

-- --------------------------------------------------------------
-- 6. ĐỀ NGHỊ MUA NVL & ĐƠN MUA HÀNG (HÀNG ĐANG VỀ)
-- --------------------------------------------------------------

INSERT INTO `de_nghi_mua_nvl` (`maDeNghi`, `maNhanVienLap`, `maKeHoach`, `ngayLap`, `ngayCanHang`, `trangThai`, `tongTienDuKien`, `lyDo`) VALUES
('DNM-20261027-568A973', 'NV001', 'KHSX001', '2026-10-27 10:45:00', '2026-10-31', 'CHO_DUYET', 2430000.00, 'Mua bổ sung NVL cho KHSX001'),
('DNM-20261027-656AB2B', 'NV001', 'KHSX004', '2026-10-27 11:30:00', '2026-11-10', 'CHO_DUYET', 6010000.00, 'Kế hoạch KHSX004 thiếu NVL001=5, NVL005=16, NVL006=38'),
('DNM-20261027-6F4851F9', 'NV001', NULL,      '2026-10-27 14:15:00', '2026-11-05', 'DA_DUYET',  2970000.00, 'Đề nghị mua bổ sung ngoài kế hoạch'),
('DNM-20261027-97B3885',  'NV001', NULL,      '2026-10-27 16:30:00', '2026-11-08', 'CHO_DUYET', 6465000.00, 'Bổ sung dự phòng kho');

-- Yêu cầu 1: KHSX004 đề nghị mua: NVL001 = 5, NVL005 = 16, NVL006 = 38
INSERT INTO `chi_tiet_de_nghi_mua_nvl` (`maDeNghi`, `maSKU`, `nhuCauSX`, `tonKhaDung`, `tonToiThieu`, `hangDangVe`, `soLuongDeXuat`, `soLuongMua`, `ngoaiKeHoach`) VALUES
('DNM-20261027-568A973', 'NVL001', 16.000, 30.000, 20.000, 10.000,  0.000, 60.000, 0),
('DNM-20261027-568A973', 'NVL004', 24.000, 30.000, 10.000,  0.000,  0.000, 30.000, 0),
('DNM-20261027-656AB2B', 'NVL001', 25.000, 30.000, 20.000, 10.000,  5.000,  5.000, 0),
('DNM-20261027-656AB2B', 'NVL005', 20.000, 12.000,  8.000,  0.000, 16.000, 16.000, 0),
('DNM-20261027-656AB2B', 'NVL006', 48.000, 20.000, 10.000,  0.000, 38.000, 38.000, 0),
('DNM-20261027-6F4851F9', 'NVL004', 15.000, 30.000, 10.000,  0.000, 10.000, 66.000, 0),
('DNM-20261027-97B3885',  'NVL004', 24.000, 30.000, 10.000,  0.000, 19.000, 19.000, 0),
('DNM-20261027-97B3885',  'NVL005',  0.000, 12.000,  8.000,  0.000,  0.000, 66.000, 1);

-- Yêu cầu 1: vw_hang_dang_ve: NVL001 = 10, NVL005 = 0, NVL006 = 0
INSERT INTO `don_mua_hang` (`maDonMua`, `maDeNghi`, `maNCC`, `maNhanVienLap`, `ngayDat`, `ngayGiaoDuKien`, `trangThai`, `tongTien`) VALUES
('PO-202610-001', 'DNM-20261027-568A973', 'NCC0002', 'NV001', '2026-10-25', '2026-10-30', 'DANG_GIAO',  360000.00),
('PO-202610-002', NULL,                  'NCC0001', 'NV001', '2026-10-24', '2026-10-27', 'HOAN_THANH', 750000.00);

INSERT INTO `chi_tiet_don_mua_hang` (`maDonMua`, `maSKU`, `soLuongDat`, `soLuongDaNhan`, `donGia`, `thanhTien`) VALUES
('PO-202610-001', 'NVL001', 20.000, 10.000, 18000.00, 360000.00),
('PO-202610-002', 'NVL002', 50.000, 50.000, 15000.00, 750000.00);

-- --------------------------------------------------------------
-- 7. LÔ HÀNG VÀ TỒN KHO THỰC TẾ (RÀNG BUỘC THEO PROMPT 2 & 3)
-- --------------------------------------------------------------
-- Yêu cầu 1: Sau khi trừ giữ chỗ LSX (NVL001 trừ 30, NVL004 trừ 45, NVL002 trừ 120),
-- vw_ton_kho_kha_dung phải ra CHÍNH XÁC:
-- NVL001 = 30  -> Cần tổng lô DAT = 60 kg (trừ 30 = 30)
-- NVL005 = 12  -> Cần tổng lô DAT = 12 kg (trừ 0  = 12)
-- NVL006 = 20  -> Cần tổng lô DAT = 20 kg (trừ 0  = 20)
-- NVL004 >= 24 -> Cần tổng lô DAT = 75 kg (trừ 45 = 30 >= 24)
-- NVL002 >= 64 -> Cần tổng lô DAT = 200kg (trừ 120 = 80 >= 64)
-- Yêu cầu 2: Ít nhất 2 kho, mỗi NVL 2-3 lô khác HSD, 1 lô còn <= 7 ngày, 1 lô CACH_LY

INSERT INTO `lo_hang` (`maLo`, `soLo`, `maSKU`, `ngaySanXuat`, `ngayNhap`, `ngayHetHan`, `trangThaiChatLuong`, `trangThai`) VALUES
-- NVL001: Đường (Tổng DAT còn hạn = 60 kg)
('LO_NVL001_01', 'DG-1026-01', 'NVL001', '2026-09-01', '2026-10-01', '2027-06-30', 'DAT', 'HOAT_DONG'),
('LO_NVL001_02', 'DG-1026-02', 'NVL001', '2026-09-15', '2026-10-10', '2027-09-30', 'DAT', 'HOAT_DONG'),
('LO_NVL001_EXP','DG-0926-EX', 'NVL001', '2026-01-01', '2026-02-01', DATE_ADD(CURRENT_DATE, INTERVAL 3 DAY), 'DAT', 'HOAT_DONG'), -- Sắp hết hạn còn 3 ngày
('LO_NVL001_CL', 'DG-1026-CL', 'NVL001', '2026-08-01', '2026-10-15', '2027-03-15', 'CACH_LY', 'TAM_KHOA'),                 -- Lô cách ly

-- NVL002: Bột mì (Tổng DAT còn hạn = 200 kg)
('LO_NVL002_01', 'BM-1026-01', 'NVL002', '2026-08-10', '2026-09-01', '2027-06-30', 'DAT', 'HOAT_DONG'),
('LO_NVL002_02', 'BM-1026-02', 'NVL002', '2026-09-01', '2026-10-05', '2027-11-30', 'DAT', 'HOAT_DONG'),

-- NVL004: Bơ thực vật (Tổng DAT còn hạn = 75 kg)
('LO_NVL004_01', 'BO-1026-01', 'NVL004', '2026-09-01', '2026-09-20', '2027-04-30', 'DAT', 'HOAT_DONG'),
('LO_NVL004_02', 'BO-1026-02', 'NVL004', '2026-09-15', '2026-10-08', '2027-07-31', 'DAT', 'HOAT_DONG'),

-- NVL005: Sữa bột (Tổng DAT còn hạn = 12 kg)
('LO_NVL005_01', 'SB-1026-01', 'NVL005', '2026-08-01', '2026-09-10', '2027-04-15', 'DAT', 'HOAT_DONG'),
('LO_NVL005_02', 'SB-1026-02', 'NVL005', '2026-08-20', '2026-09-25', '2027-08-20', 'DAT', 'HOAT_DONG'),

-- NVL006: Chocolate đen (Tổng DAT còn hạn = 20 kg)
('LO_NVL006_01', 'CC-1026-01', 'NVL006', '2026-07-15', '2026-08-20', '2027-05-10', 'DAT', 'HOAT_DONG'),
('LO_NVL006_02', 'CC-1026-02', 'NVL006', '2026-08-01', '2026-09-05', '2027-10-15', 'DAT', 'HOAT_DONG'),

-- NVL007: Hương vani
('LO_NVL007_01', 'VN-1026-01', 'NVL007', '2026-06-01', '2026-07-01', '2027-12-31', 'DAT', 'HOAT_DONG'),

-- Các lô thành phẩm chuẩn bị cho kiểm kê và điều phối nhập
('LO_SP001_01',  'BQ-1026-01', 'SP001',     '2026-10-15', '2026-10-20', '2027-03-15', 'DAT', 'HOAT_DONG'),
('LO_SP002_01',  'SC-0926-09', 'SP002',     '2026-09-01', '2026-09-10', '2027-05-15', 'DAT', 'HOAT_DONG'),
('LO_SP003_01',  'KS-1026-06', 'SP003',     '2026-10-01', '2026-10-15', '2027-06-01', 'DAT', 'HOAT_DONG'),
('LO_BK_QB_000', 'BQ-1026-00', 'BK-QB-001', '2026-10-20', '2026-10-28', '2027-04-15', 'DAT', 'HOAT_DONG'),
('LO_BK_QB_001', 'BQ-1026-01', 'BK-QB-001', '2026-10-10', '2026-10-20', '2027-03-15', 'CACH_LY', 'HOAT_DONG'),
('LO_BK_KD_002', 'KD-1026-04', 'BK-KD-002', '2026-09-20', '2026-10-12', '2027-01-30', 'CACH_LY', 'HOAT_DONG'),
('LO_BK_BL_003', 'BL-0826-02', 'BK-BL-003', '2026-08-01', '2026-08-15', '2026-10-20', 'CACH_LY', 'TAM_KHOA'), -- Hết hạn
('LO_BK_SC_004', 'SC-0926-09', 'BK-SC-004', '2026-09-01', '2026-09-10', '2027-05-15', 'CACH_LY', 'HOAT_DONG'),
('LO_BK_BG_005', 'BG-1026-03', 'BK-BG-005', '2026-09-15', '2026-10-05', '2027-02-28', 'CACH_LY', 'HOAT_DONG'),
('LO_BK_KS_006', 'KS-1026-06', 'BK-KS-006', '2026-10-01', '2026-10-15', '2027-06-01', 'CACH_LY', 'HOAT_DONG'),
('LO_BK_CP_007', 'CP-1026-01', 'BK-CP-007', '2026-09-10', '2026-09-25', '2027-01-12', 'CACH_LY', 'HOAT_DONG');

-- Bảng tồn kho thực tế:
-- NVL001: 25 (VT000001 kho K001) + 30 (VT000005 kho K002) + 5 (VT000002 kho K001) = 60 kg (Lô CL: 15 ở VT000003)
-- NVL002: 120 (VT000004 kho K001) + 80 (VT000008 kho K002) = 200 kg
-- NVL004: 45 (VT000034 kho K010) + 30 (VT000007 kho K001) = 75 kg
-- NVL005: 8 (VT000006 kho K001) + 4 (VT000011 kho K003) = 12 kg
-- NVL006: 12 (VT000035 kho K010) + 8 (VT000036 kho K010) = 20 kg
-- NVL007: 10 (VT000012 kho K003) = 10 lít
INSERT INTO `ton_kho` (`maLo`, `maViTri`, `soLuongThucTe`, `soLuongGiuCho`) VALUES
('LO_NVL001_01', 'VT000001',  25.000, 0.000),
('LO_NVL001_02', 'VT000005',  30.000, 0.000),
('LO_NVL001_EXP','VT000002',   5.000, 0.000),
('LO_NVL001_CL', 'VT000003',  15.000, 0.000),
('LO_NVL002_01', 'VT000004', 120.000, 0.000),
('LO_NVL002_02', 'VT000008',  80.000, 0.000),
('LO_NVL004_01', 'VT000034',  45.000, 0.000),
('LO_NVL004_02', 'VT000007',  30.000, 0.000),
('LO_NVL005_01', 'VT000006',   8.000, 0.000),
('LO_NVL005_02', 'VT000011',   4.000, 0.000),
('LO_NVL006_01', 'VT000035',  12.000, 0.000),
('LO_NVL006_02', 'VT000036',   8.000, 0.000),
('LO_NVL007_01', 'VT000012',  10.000, 0.000),
('LO_SP001_01',  'VT000015', 100.000, 0.000),
('LO_SP002_01',  'VT000019',  80.000, 0.000),
('LO_SP003_01',  'VT000020',  90.000, 0.000),
('LO_BK_QB_001', 'VT000016', 124.000, 0.000),
('LO_BK_KD_002', 'VT000021', 198.000, 0.000),
('LO_BK_BL_003', 'VT000025',  60.000, 0.000),
('LO_BK_SC_004', 'VT000017',  45.000, 0.000),
('LO_BK_BG_005', 'VT000018',  72.000, 0.000),
('LO_BK_KS_006', 'VT000024', 150.000, 0.000),
('LO_BK_CP_007', 'VT000026',  28.000, 0.000);

-- --------------------------------------------------------------
-- 8. ĐIỀU PHỐI NHẬP KHO (10 CASE TỪ MOCK_BANH_KEO.JSON)
-- --------------------------------------------------------------

INSERT INTO `phieu_nhap_kho` (`maPhieuNhap`, `maDonMua`, `maKho`, `maNCC`, `ngayLap`, `ngayHenGiao`, `ngayNhap`, `maNhanVienLap`, `tongSoLuong`, `tongTheTich`, `trangThai`, `ghiChu`) VALUES
('YCN26100001', NULL, 'K006', 'NCC0003', '2026-10-28 08:00:00', '2026-10-28', '2026-10-28 09:30:00', 'NV002', 200.000,  80.000, 'DA_DIEU_PHOI', 'Case 1: Thành công phân bổ 2 vị trí VT000022 và VT000023 (100+100)'),
('YCN26100002', NULL, 'K001', 'NCC0001', '2026-10-28 08:30:00', '2026-10-29', NULL,                  'NV002', 500.000, 150.000, 'CHO_DIEU_PHOI', 'Case 2: 500 bao bột mì phân bổ nhiều vị trí'),
('YCN26100003', NULL, 'K003', 'NCC0003', '2026-10-28 09:00:00', '2026-10-29', NULL,                  'NV002', 150.000,  60.000, 'CHO_DIEU_PHOI', 'Case 3: Lỗi kho/vị trí không đủ sức chứa'),
('YCN26100004', NULL, 'K002', 'NCC0002', '2026-10-28 09:30:00', '2026-10-29', NULL,                  'NV002', 100.000,  40.000, 'CHO_DIEU_PHOI', 'Case 4: Lỗi không đạt QC'),
('YCN26100005', NULL, 'K004', 'NCC0003', '2026-10-28 10:00:00', '2026-10-30', NULL,                  'NV002', 120.000,  50.000, 'CHO_DIEU_PHOI', 'Case 5: Lỗi vị trí còn lại không đủ và vị trí bảo trì'),
('YCN26100006', NULL, 'K005', 'NCC0003', '2026-10-28 10:30:00', '2026-10-30', NULL,                  'NV002', 100.000,  40.000, 'DA_DIEU_PHOI', 'Case 6: Có lệnh sẵn, xem chi tiết lệnh'),
('YCN26100007', NULL, 'K006', 'NCC0003', '2026-10-28 11:00:00', '2026-10-30', NULL,                  'NV002',  80.000,  32.000, 'DA_DIEU_PHOI', 'Case 7: Có lệnh sẵn, xem chi tiết lệnh'),
('YCN26100008', NULL, 'K001', 'NCC0001', '2026-10-28 11:30:00', '2026-10-31', NULL,                  'NV002',  50.000,  20.000, 'CHO_DIEU_PHOI', 'Case 8: Lỗi lô hết hạn sử dụng'),
('YCN26100009', NULL, 'K002', 'NCC0002', '2026-10-28 13:00:00', '2026-10-31', NULL,                  'NV002',  60.000,  24.000, 'CHO_DIEU_PHOI', 'Case 9: Chờ QA kiểm tra (CHO_KIEM)'),
('YCN26100010', NULL, 'K009', 'NCC0003', '2026-10-28 14:00:00', '2026-11-01', NULL,                  'NV002',  40.000,  16.000, 'CHO_DIEU_PHOI', 'Case 10: Kho K009 đang BAO_TRI');

INSERT INTO `chi_tiet_phieu_nhap_kho` (`maPhieuNhap`, `maLo`, `maSKU`, `soLuong`, `donGia`, `thanhTien`, `theTichDonVi`, `tongTheTich`) VALUES
('YCN26100001', 'LO_BK_QB_000', 'BK-QB-001', 200.000, 24000.00, 4800000.00, 0.400,  80.000),
('YCN26100002', 'LO_NVL002_01', 'NVL002',    500.000, 15000.00, 7500000.00, 0.300, 150.000),
('YCN26100003', 'LO_NVL007_01', 'NVL007',    150.000, 95000.00,14250000.00, 0.400,  60.000),
('YCN26100004', 'LO_NVL001_CL', 'NVL001',    100.000, 18000.00, 1800000.00, 0.400,  40.000),
('YCN26100005', 'LO_NVL004_02', 'NVL004',    120.000, 45000.00, 5400000.00, 0.416,  50.000),
('YCN26100006', 'LO_SP001_01',  'SP001',     100.000, 24000.00, 2400000.00, 0.400,  40.000),
('YCN26100007', 'LO_SP003_01',  'SP003',      80.000, 12000.00,  960000.00, 0.400,  32.000),
('YCN26100008', 'LO_BK_BL_003', 'BK-BL-003',  50.000, 20000.00, 1000000.00, 0.400,  20.000),
('YCN26100009', 'LO_NVL001_02', 'NVL001',     60.000, 18000.00, 1080000.00, 0.400,  24.000),
('YCN26100010', 'LO_NVL005_02', 'NVL005',     40.000, 85000.00, 3400000.00, 0.400,  16.000);

-- Lệnh điều phối cho YCN26100001, YCN26100006, YCN26100007
INSERT INTO `lenh_dieu_phoi_kho` (`maLenhDieuPhoi`, `maPhieuNhap`, `maKho`, `ngayPhatLenh`, `ngayHoanThanh`, `maNhanVienPhatLenh`, `maNhanVienThucHien`, `trangThai`, `ghiChu`) VALUES
('LDP26100001', 'YCN26100001', 'K006', '2026-10-28 08:15:00', '2026-10-28 09:30:00', 'NV002', 'NV0001', 'HOAN_THANH', 'Phân bổ 2 vị trí VT000022 và VT000023'),
('LDP26100006', 'YCN26100006', 'K005', '2026-10-28 10:45:00', NULL,                  'NV002', 'NV0002', 'DANG_THUC_HIEN', 'Đang chuyển hàng vào VT000015'),
('LDP26100007', 'YCN26100007', 'K006', '2026-10-28 11:15:00', NULL,                  'NV002', 'NV0003', 'CHO_THUC_HIEN', 'Chờ xe nâng đưa vào VT000020');

INSERT INTO `chi_tiet_lenh_dieu_phoi` (`maLenhDieuPhoi`, `maLo`, `maSKU`, `maViTri`, `soLuongDieuPhoi`, `soLuongThucHien`, `trangThai`) VALUES
('LDP26100001', 'LO_BK_QB_000', 'BK-QB-001', 'VT000022', 100.000, 100.000, 'DA_NHAP'),
('LDP26100001', 'LO_BK_QB_000', 'BK-QB-001', 'VT000023', 100.000, 100.000, 'DA_NHAP'),
('LDP26100006', 'LO_SP001_01',  'SP001',     'VT000015', 100.000,  50.000, 'CHO_NHAP'),
('LDP26100007', 'LO_SP003_01',  'SP003',     'VT000020',  80.000,   0.000, 'CHO_NHAP');

-- Biên bản kiểm tra chất lượng QC
INSERT INTO `bien_ban_kiem_tra_chat_luong` (`maBienBanQC`, `maPhieuNhap`, `maLo`, `maSKU`, `ngayKiemTra`, `maNhanVienQC`, `soLuongKiemTra`, `soLuongDat`, `soLuongKhongDat`, `ketLuan`, `ghiChu`) VALUES
('BBQC-BK-001', 'YCN26100001', 'LO_BK_QB_000', 'BK-QB-001', '2026-10-28', 'NV004', 200.000, 200.000,   0.000, 'DAT',       'Bánh quy đạt tiêu chuẩn vệ sinh an toàn thực phẩm'),
('BBQC-BK-002', 'YCN26100002', 'LO_NVL002_01', 'NVL002',    '2026-10-28', 'NV004', 500.000, 500.000,   0.000, 'DAT',       'Bột mì đạt độ ẩm và độ mịn'),
('BBQC-BK-004', 'YCN26100004', 'LO_NVL001_CL', 'NVL001',    '2026-10-28', 'NV004', 100.000,   0.000, 100.000, 'KHONG_DAT', 'Đường bị vón cục và nhiễm ẩm nặng (Case 4)');

-- --------------------------------------------------------------
-- 9. KIỂM KÊ KHO (GIỮ NGUYÊN TEST CASE TỪ KIEM_KE_BANH_KEO.SQL)
-- --------------------------------------------------------------
-- BBKK-2026-001: Thừa 128.000 đ, thiếu 66.000 đ, tổng chênh lệch 194.000 đ
-- BBKK-2026-003: Thiếu số lượng thực tế
-- BBKK-2026-004: Thiếu số sổ sách / đơn giá
-- BBKK-2026-005: Không chênh lệch

INSERT INTO `bien_ban_kiem_ke` (`maBienBan`, `ngayKiemKe`, `maKho`, `maNhanVienLap`, `trangThai`, `ngayCapNhat`, `maNhanVienCapNhat`, `tongGiaTriThua`, `tongGiaTriThieu`, `tongGiaTriChenhLech`, `giaTriChenhLechRong`, `lyDoChenhLech`, `yKienPheDuyet`) VALUES
('BBKK-2026-001', '2026-10-25', 'K005', 'NV002', 'DA_DUYET',       '2026-10-25 17:00:00', 'NV002', 128000.00, 66000.00, 194000.00,  62000.00, 'Nhầm lẫn mã khi đóng thùng', 'Phê duyệt điều chỉnh tồn kho'),
('BBKK-2026-002', '2026-10-26', 'K006', 'NV002', 'CHO_QLK_DUYET',  NULL,                  NULL,     180000.00, 30000.00, 210000.00, 150000.00, 'Chờ Quản lý kho xác nhận', NULL),
('BBKK-2026-003', '2026-10-26', 'K005', 'NV002', 'DANG_KIEM_KE',   NULL,                  NULL,          0.00,     0.00,      0.00,      0.00, 'Đang tiến hành đếm thực tế', NULL),
('BBKK-2026-004', '2026-10-27', 'K006', 'NV002', 'DANG_KIEM_KE',   NULL,                  NULL,          0.00,     0.00,      0.00,      0.00, 'Kiểm kê đột xuất bổ sung', NULL),
('BBKK-2026-005', '2026-10-27', 'K005', 'NV002', 'DA_DUYET',       '2026-10-27 16:30:00', 'NV002',          0.00,     0.00,      0.00,      0.00, 'Khớp hoàn toàn sổ sách', 'Duyệt');

INSERT INTO `chi_tiet_kiem_ke` (`maBienBan`, `maSKU`, `maLo`, `maViTri`, `soLuongSoSach`, `soLuongThucTe`, `donGia`, `soLuongChenhLech`, `phanLoai`, `giaTriChenhLech`) VALUES
('BBKK-2026-001', 'BK-QB-001', 'LO_BK_QB_001', 'VT000016', 120.000, 124.000, 32000.00,   4.000, 'THUA',   128000.00),
('BBKK-2026-001', 'BK-BG-005', 'LO_BK_BG_005', 'VT000018',  75.000,  72.000, 22000.00,  -3.000, 'THIEU',  -66000.00),
('BBKK-2026-001', 'BK-CP-007', 'LO_BK_CP_007', 'VT000026',  28.000,  28.000, 49000.00,   0.000, 'KHOP',        0.00),
('BBKK-2026-002', 'BK-KD-002', 'LO_BK_KD_002', 'VT000021', 200.000, 198.000, 15000.00,  -2.000, 'THIEU',  -30000.00),
('BBKK-2026-002', 'BK-KS-006', 'LO_BK_KS_006', 'VT000024', 140.000, 150.000, 18000.00,  10.000, 'THUA',   180000.00),
('BBKK-2026-003', 'BK-SC-004', 'LO_BK_SC_004', 'VT000017',  45.000,    NULL, 42000.00,    NULL, NULL,           NULL),
('BBKK-2026-003', 'BK-BL-003', 'LO_BK_BL_003', 'VT000025',  60.000,  60.000, 28000.00,   0.000, 'KHOP',        0.00),
('BBKK-2026-004', 'BK-KD-002', 'LO_BK_KD_002', 'VT000021',    NULL,  85.000, 15000.00,    NULL, NULL,           NULL),
('BBKK-2026-004', 'BK-KS-006', 'LO_BK_KS_006', 'VT000024',  45.000,  40.000,     NULL,  -5.000, NULL,           NULL),
('BBKK-2026-005', 'BK-QB-001', 'LO_BK_QB_001', 'VT000016',  98.000,  98.000, 32000.00,   0.000, 'KHOP',        0.00),
('BBKK-2026-005', 'BK-BG-005', 'LO_BK_BG_005', 'VT000018',  75.000,  75.000, 22000.00,   0.000, 'KHOP',        0.00);

INSERT INTO `thanh_vien_kiem_ke` (`maBienBan`, `maNhanVien`, `vaiTroKiemKe`) VALUES
('BBKK-2026-001', 'NV002', 'TRUONG_DOAN'),
('BBKK-2026-001', 'NV003', 'THANH_VIEN'),
('BBKK-2026-001', 'NV004', 'GIAM_SAT_QC'),
('BBKK-2026-002', 'NV002', 'TRUONG_DOAN'),
('BBKK-2026-002', 'NV003', 'THANH_VIEN');

-- --------------------------------------------------------------
-- 10. HÀNG LỖI HỎNG & ĐỀ XUẤT XỬ LÝ (TỪ BANH_KEO.SQL)
-- --------------------------------------------------------------
-- Yêu cầu 3: HL001 + HL002 = 109.000 đ (2 hộp HL001 * 32.000 + 3 gói HL002 * 15.000)

INSERT INTO `hang_loi_hong` (`maHangLoi`, `maSKU`, `maLo`, `maKho`, `nguonGoc`, `maChungTuNguon`, `ngayGhiNhan`, `nguyenNhan`, `soLuongLoiHong`, `donGia`, `trangThai`) VALUES
('HL001', 'BK-QB-001', 'LO_BK_QB_001', 'K005', 'Kiểm tra chất lượng (QC)', 'BBQC-BK-001', '2026-10-21', 'Hộp móp, tem niêm phong bong tróc',     18.000, 32000.00, 'CHO_XU_LY'),
('HL002', 'BK-KD-002', 'LO_BK_KD_002', 'K006', 'Kiểm tra chất lượng (QC)', 'BBQC-BK-002', '2026-10-22', 'Rách mép hàn bao bì, không kín',       25.000, 15000.00, 'CHO_XU_LY'),
('HL003', 'BK-BL-003', 'LO_BK_BL_003', 'K007', 'Phiếu nhập hàng trả về',    'PNK-BK-003',  '2026-10-23', 'Hết hạn sử dụng khi hàng bị trả về',    12.000, 28000.00, 'CHO_XU_LY'),
('HL004', 'BK-SC-004', 'LO_BK_SC_004', 'K005', 'Kiểm tra chất lượng (QC)', 'BBQC-BK-004', '2026-10-24', 'Biến dạng do bảo quản sai nhiệt độ',    8.000, 42000.00, 'CHO_XU_LY'),
('HL005', 'BK-BG-005', 'LO_BK_BG_005', 'K005', 'Kiểm tra chất lượng (QC)', 'BBQC-BK-005', '2026-10-25', 'Bao bì hở gây hút ẩm, giảm độ giòn',   16.000, 22000.00, 'CHO_XU_LY'),
('HL006', 'BK-KS-006', 'LO_BK_KS_006', 'K006', 'Kiểm tra chất lượng (QC)', 'BBQC-BK-006', '2026-10-25', 'In sai thông tin lô và hạn sử dụng',   30.000, 18000.00, 'CHO_XU_LY'),
('HL007', 'BK-CP-007', 'LO_BK_CP_007', 'K005', 'Phiếu nhập hàng trả về',    'PNK-BK-007',  '2026-10-26', 'Hộp bị móp, bao gói bên trong bung',     9.000, 49000.00, 'CHO_XU_LY');

-- Đề xuất mẫu DXLH-20261028-0001: 2 hộp HL001 + 3 gói HL002 = 109.000 đ
INSERT INTO `de_xuat_xu_ly_hang_loi` (`id`, `maDeXuat`, `ngayLap`, `maNhanVienLap`, `phuongAn`, `lyDo`, `trangThai`, `tongGiaTri`) VALUES
(1, 'DXLH-20261028-0001', '2026-10-28 15:30:00', 'NV002', 'TIEU_HUY', 'Hàng rách bao bì, móp hộp không đạt QC', 'CHO_XU_LY', 109000.00);

INSERT INTO `chi_tiet_de_xuat_xu_ly` (`id`, `deXuatId`, `maHangLoi`, `maSKU`, `soLo`, `ngayHetHan`, `soLuongXuLy`, `donGia`, `thanhTien`) VALUES
(1, 1, 'HL001', 'BK-QB-001', 'BQ-1026-01', '2027-03-15', 2.000, 32000.00, 64000.00),
(2, 1, 'HL002', 'BK-KD-002', 'KD-1026-04', '2027-01-30', 3.000, 15000.00, 45000.00);

-- --------------------------------------------------------------
-- 11. GIAO DỊCH KHO MẪU
-- --------------------------------------------------------------

INSERT INTO `giao_dich_kho` (`loaiGiaoDich`, `maChungTu`, `loaiChungTu`, `ngayGiaoDich`, `maLo`, `maViTri`, `maSKU`, `soLuongThayDoi`, `tonTruocGiaoDich`, `tonSauGiaoDich`, `maNhanVien`, `ghiChu`) VALUES
('NHAP', 'YCN26100001', 'PHIEU_NHAP_KHO', '2026-10-28 09:30:00', 'LO_BK_QB_000', 'VT000022', 'BK-QB-001', 100.000, 0.000, 100.000, 'NV0001', 'Nhập kho theo lệnh điều phối LDP26100001'),
('NHAP', 'YCN26100001', 'PHIEU_NHAP_KHO', '2026-10-28 09:30:00', 'LO_BK_QB_000', 'VT000023', 'BK-QB-001', 100.000, 0.000, 100.000, 'NV0001', 'Nhập kho theo lệnh điều phối LDP26100001');
