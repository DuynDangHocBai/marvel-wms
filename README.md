# MARVEL WMS - HỆ THỐNG QUẢN LÝ KHO BÁNH KẸO

Hệ thống Quản lý Kho bánh kẹo (Marvel Warehouse Management System) phục vụ quản lý nguyên vật liệu và thành phẩm trong quy trình sản xuất bánh kẹo, tích hợp đầy đủ 6 phân hệ cốt lõi với kiến trúc MVC chuẩn PHP thuần và cơ sở dữ liệu MySQL InnoDB.

---

## 1. THÀNH VIÊN DỰ ÁN & PHÂN HỆ PHỤ TRÁCH

Theo phân công đồ án Báo Cáo Giữa Kỳ (BaoCaoGK_Marvel):

| STT | Thành viên phụ trách | Mã phân hệ | Chức năng nghiệp vụ chính | Vai trò chính |
|:---:|:---|:---|:---|:---:|
| 1 | **Thái Mai Kỳ Hướng** | `de-nghi-mua` | **Lập đề nghị mua NVL** (UC 2.3.3) | LKH |
| 2 | **Nguyễn Thị Mỹ Duyên** | `lenh-san-xuat` | **Tạo lệnh sản xuất bánh kẹo** (UC 2.1.3) | LKH |
| 3 | **Lý Hán Huy** | `phan-bo` | **Phân bổ số lượng NVL lưu kho** (UC 2.2.3) | QLK |
| 4 | **Ngô Đình Trí** | `dieu-phoi` | **Điều phối nhập kho & Lệnh NVK** (UC 2.5.3) | QLK / NVK |
| 5 | **Lê Thảo Uyên** | `kiem-ke` | **Cập nhật chênh lệch kiểm kê** (UC 2.6.3) | QLK |
| 6 | **Cao Xuân Nguyên** | `hang-loi` | **Đề xuất xử lý hàng lỗi hỏng** (UC 2.4.3) | QLK |

---

## 2. HƯỚNG DẪN CÀI ĐẶT & MÔI TRƯỜNG CHẠY (XAMPP CỔNG 81)

### 2.1. Yêu cầu hệ thống
- XAMPP for Windows (PHP $\ge$ 8.2, MySQL $\ge$ 8.0 / MariaDB 10.4+)
- Trình duyệt Web hiện đại (Chrome / Edge / Firefox)

### 2.2. Cấu hình Apache chạy Cổng 81
1. Mở file `C:\xampp\apache\conf\httpd.conf`.
2. Tìm dòng `Listen 80`, sửa thành:
   ```apache
   Listen 81
   ```
3. Tìm dòng `ServerName localhost:80`, sửa thành:
   ```apache
   ServerName localhost:81
   ```
4. Khởi động lại dịch vụ Apache từ XAMPP Control Panel.

### 2.3. Cài đặt Cơ sở dữ liệu (Import SQL)
Mở PowerShell hoặc Command Prompt tại thư mục dự án `C:\xampp\htdocs\marvel-wms` và chạy 2 lệnh sau:
```powershell
C:\xampp\mysql\bin\mysql.exe -u root --default-character-set=utf8mb4 -e "source database/schema.sql"
C:\xampp\mysql\bin\mysql.exe -u root --default-character-set=utf8mb4 -e "source database/seed.sql"
```

### 2.4. Khởi chạy ứng dụng
Truy cập qua trình duyệt tại địa chỉ:
**`http://localhost:81/marvel-wms/public/`**

---

## 3. DANH SÁCH TÀI KHOẢN THỬ NGHIỆM

Mật khẩu mặc định cho tất cả các tài khoản là: **`123456`**

| Tên đăng nhập | Họ và tên | Vai trò | Quyền hạn nổi bật |
|:---|:---|:---:|:---|
| **`lkh01`** | Trần Văn An | **LKH** (Lập kế hoạch) | Lập đề nghị mua NVL, Tạo & phát lệnh sản xuất theo BOM |
| **`qlk01`** | Lê Thị Bình | **QLK** (Quản lý kho) | Phân bổ NVL (FEFO), Điều phối nhập kho, Cập nhật kiểm kê, Đề xuất hàng lỗi |
| **`nvk01`** | Phạm Văn Cường | **NVK** (Nhân viên kho) | Xem danh sách lệnh điều phối được giao, bấm "Đã nhận lệnh" |
| **`qc01`** | Hoàng Thị Dung | **QC** (Kiểm tra chất lượng) | Tra cứu phiếu nhập kho, theo dõi đề xuất xử lý hàng lỗi (Chỉ xem) |
| **`bgd01`** | Ngô Văn Em | **BGD** (Ban Giám Đốc) | Xem toàn bộ báo cáo, tiến độ và phê duyệt các hạn mức cao (> 10 triệu) |
| **`qtv01`** | Nguyễn Văn Admin | **QTV** (Quản trị viên) | Quản lý tài khoản, nhật ký truy vết hệ thống |
| *`khoa01`* | Tài Khoản Khóa | *QLK* | Tài khoản bị khóa (`trangThai = 'KHOA'`) để kiểm thử an ninh |

---

## 4. MA TRẬN PHÂN QUYỀN (AUTHORIZATION MATRIX)

Được định nghĩa tập trung tại `app/config/permissions.php`:

| Phân hệ / Chức năng | URL Route | Quyền GHI (Tạo / Sửa / Lưu) | Quyền XEM (Tra cứu / Chi tiết) |
|:---|:---|:---:|:---:|
| **Đề nghị mua NVL** | `?route=de-nghi-mua/index` | **LKH** | **LKH, QLK, BGD** |
| **Tạo lệnh sản xuất** | `?route=lenh-san-xuat/index` | **LKH** | **LKH, QLK, BGD** |
| **Phân bổ số lượng** | `?route=phan-bo/index` | **QLK** | **QLK, LKH, BGD** |
| **Điều phối nhập kho** | `?route=dieu-phoi/index` | **QLK** | **QLK, QC, BGD** |
| **Lệnh điều phối giao** | `?route=lenh-dieu-phoi/index` | **NVK** (Xác nhận) | **NVK, QLK** |
| **Chênh lệch kiểm kê** | `?route=kiem-ke/index` | **QLK** | **QLK, BGD** |
| **Đề xuất hàng lỗi** | `?route=hang-loi/index` | **QLK** | **QLK, QC, BGD** |
| **Tài khoản người dùng** | `?route=tai-khoan/index` | **QTV** | **QTV** |
| **Nhật ký hệ thống** | `?route=nhat-ky/index` | *Hệ thống tự ghi* | **QTV, BGD** |

> **Bảo mật & Kiểm soát**: Mọi truy cập trái quyền gõ trực tiếp qua URL đều bị chặn với mã phản hồi **403 Forbidden** kèm giao diện thông báo lỗi chuẩn mực. Mọi form POST đều bắt buộc có token **CSRF** hợp lệ và tự động đăng xuất sau 15 phút không thao tác.

---

## 5. CÁC QUY TẮC NGHIỆP VỤ CỐT LÕI (BUSINESS RULES)

1. **BR-06 (Khấu trừ tồn khả dụng)**: Lệnh sản xuất phát hành (`DA_PHAT`) giữ chỗ NVL và làm giảm tồn khả dụng thông qua view `vw_ton_kho_kha_dung` mà **không** tác động trực tiếp vào bảng `ton_kho`. Khi Quản lý kho thực hiện phân bổ, giữ chỗ chuyển sang cấp lô (`ton_kho.soLuongGiuCho`), giải phóng giữ chỗ cấp lệnh, tránh trừ hai lần.
2. **BR-09 (Cách ly chất lượng)**: Toàn bộ lô hàng có trạng thái chất lượng `CACH_LY` hoặc không đạt chuẩn (`KHONG_DAT`) hoặc đã quá hạn sử dụng đều bị loại bỏ hoàn toàn khỏi danh sách phân bổ sản xuất và điều phối vị trí thông thường.
3. **BR-05 (Chặn tồn âm)**: Cơ sở dữ liệu và tầng Repository áp dụng ràng buộc `CHECK (soLuong >= 0)` và kiểm tra chặt chẽ, ngăn chặn triệt để mọi giao dịch làm số lượng âm.
4. **Kiểm kê không can thiệp tồn kho**: Cập nhật chênh lệch kiểm kê chỉ tính toán giá trị thừa thiếu (BBKK-2026-001 = 194.000 đ) và lưu trữ biên bản, việc xử lý số lượng tồn thực tế phải chờ quyết định xử lý được phê duyệt. Phân cấp thẩm quyền rõ ràng: chênh lệch $\le$ 10 triệu do Quản lý kho duyệt, $>$ 10 triệu do Ban Giám Đốc duyệt.
5. **Đề xuất hàng lỗi tính động**: Số lượng lỗi còn lại của từng dòng lỗi hỏng được tính toán động dựa trên tổng lượng đã đề xuất trong các phiếu chưa hủy, khóa giao dịch `SELECT ... FOR UPDATE` tránh race condition khi nhiều người dùng cùng thao tác.
6. **Thuật toán FEFO & Gợi ý tự động**: Hệ thống ưu tiên xuất lô có hạn dùng gần nhất (First Expired, First Out) và tự động tính toán chia nhiều vị trí lưu kho khi số lượng hàng vượt sức chứa của một ô kệ.

---

## 6. HƯỚNG DẪN CHẠY TEST TỰ ĐỘNG

Dự án cung cấp bộ công cụ kiểm thử toàn diện bằng PHP CLI:
```bash
# Chạy toàn bộ 13 test suite tổng hợp:
C:\xampp\php\php.exe tests\run_all.php

# Kiểm tra cú pháp tất cả file PHP:
C:\xampp\php\php.exe tests\lint_syntax.php

# Chụp lại ảnh màn hình kịch bản đầu cuối (E2E):
node tests\take_e2e_screenshots.js
```
Toàn bộ các test suite đều tự động rollback transaction và bảo toàn 100% dữ liệu seed ban đầu.
Chi tiết kết quả kiểm nghiệm xem tại: `docs/TEST_REPORT.md`.

