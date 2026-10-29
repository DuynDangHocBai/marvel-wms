# BÁO CÁO KIỂM THỬ HỆ THỐNG QUẢN LÝ KHO BÁNH KẸO MARVEL WMS

- **Dự án**: Marvel WMS (Hệ thống quản lý kho nguyên vật liệu & thành phẩm sản xuất bánh kẹo)
- **Môi trường thử nghiệm**:
  - HĐH: Windows
  - Web Server: Apache 2.4.58 (Cổng 81) qua XAMPP
  - Database: MySQL 8.0 / MariaDB (Cổng 3306), Database `marvel_wms`
  - Ngôn ngữ: PHP 8.2.12
- **Thời gian thực hiện**: 28/10/2026
- **Kết quả tổng thể**: **100% PASS** (13/13 Test Suites - 240+ Test Cases, 0 Lỗi). Dữ liệu seed giữ nguyên vẹn sau toàn bộ chu trình kiểm thử.

---

## 1. TỔNG HỢP KẾT QUẢ CÁC BỘ KIỂM THỬ (TEST SUITES)

| STT | Tên Test Suite | File thực thi | Số Test Case | Kết quả | Ghi chú |
|:---:|:---|:---|:---:|:---:|:---|
| 1 | **Kiểm tra cú pháp PHP (Lint Syntax)** | `tests/lint_syntax.php` | 89 files | **PASS** | Quét toàn bộ `app/`, `config/`, `tests/` không lỗi cú pháp |
| 2 | **Kiểm thử Core MVC & Security** | `tests/verify_core.php` | 54 tests | **PASS** | PDO singleton, Router, Auth 6 roles, Session 15 phút, CSRF, Flash, Nhật ký JSON |
| 3 | **Cập nhật chênh lệch kiểm kê (UC 2.6.3)** | `tests/test_kiem_ke.php` | 38 tests | **PASS** | BBKK-2026-001 (194k), không can thiệp tồn kho, kiểm soát ngưỡng duyệt BGD 10 triệu |
| 4 | **Đề xuất xử lý hàng lỗi hỏng (UC 2.4.3)** | `tests/test_hang_loi.php` | 30 tests | **PASS** | HL001+HL002 (109k), tính tồn còn lại động, snapshot số lô & HSD, SELECT FOR UPDATE |
| 5 | **Tạo lệnh sản xuất theo BOM (UC 2.1.3)** | `tests/test_lenh_san_xuat.php` | 29 tests | **PASS** | Quy tắc BR-06 trừ khả dụng qua view, định mức BOM bánh quy bơ/kẹo dẻo dâu, chặn thiếu NVL |
| 6 | **Phân bổ số lượng NVL lưu kho (UC 2.2.3)** | `tests/test_phan_bo.php` | 37 tests | **PASS** | Thuật toán FEFO, trừ tồn kho giữ chỗ cấp lô, giải phóng giữ chỗ cấp lệnh, chặn lô quá hạn/cách ly |
| 7 | **Điều phối nhập kho & Lệnh NVK (UC 2.5.3)** | `tests/test_dieu_phoi.php` | 28 tests | **PASS** | Tái hiện đầy đủ 10 test case README (chia 100+100, chặn QC hỏng, bảo trì kho/vị trí, quá hạn) |
| 8 | **Lập đề nghị mua NVL (UC 2.3.3)** | `tests/test_de_nghi_mua.php` | 32 tests | **PASS** | KHSX004 chuẩn xác (NVL001=5, NVL005=16, NVL006=38), tính tiền dự toán, hủy khi chờ duyệt |
| 9 | **HTTP Endpoints Kiểm kê & Hàng lỗi (Port 81)** | `tests/test_http_endpoints.php` | 9 tests | **PASS** | QLK truy cập 200 OK, QC bị ẩn nút & chặn 403, BGD bị chặn form cập nhật 403 |
| 10 | **HTTP Endpoints Lệnh sản xuất (Port 81)** | `tests/test_lenh_web.php` | 5 tests | **PASS** | LKH truy cập lập lệnh 200 OK, QLK bị chặn 403 khi vào màn hình lập lệnh |
| 11 | **HTTP Endpoints Phân bổ số lượng (Port 81)** | `tests/test_phan_bo_web.php` | 5 tests | **PASS** | QLK truy cập phân bổ 200 OK (2 Tab), LKH bị chặn 403 khi vào form phân bổ |
| 12 | **HTTP Endpoints Điều phối & NVK (Port 81)** | `tests/test_dieu_phoi_web.php` | 8 tests | **PASS** | QLK điều phối 200 OK, NVK xem lệnh giao & xác nhận nhận lệnh, NVK bị chặn vào điều phối |
| 13 | **HTTP Endpoints Đề nghị mua NVL (Port 81)** | `tests/test_de_nghi_mua_web.php` | 7 tests | **PASS** | LKH lập đề nghị 200 OK, QLK xem được nhưng bị chặn tạo mới 403 |

---

## 2. BẢNG CHI TIẾT CÁC CA KIỂM THỬ ĐẶC TẢ NGHIỆP VỤ

### 2.1. Phân hệ Lập kế hoạch sản xuất & Mua sắm (LKH)
| Mã ca kiểm thử | Mô tả kiểm thử | Dữ liệu đầu vào | Kỳ vọng | Thực tế | Trạng thái |
|:---|:---|:---|:---|:---|:---:|
| **TC-DNM-01** | Lấy kế hoạch sản xuất đã duyệt | Toàn bộ kế hoạch trong DB | Chỉ lấy `trangThai = 'DA_DUYET'` | Lấy đúng KHSX003, KHSX004, KHSX005; loại bỏ KHSX002 | **PASS** |
| **TC-DNM-02** | Tính toán nhu cầu mua KHSX004 | KHSX004 (sản xuất Bánh quy bơ & Kẹo dẻo dâu) | NVL001 = 5 kg<br>NVL005 = 16 kg<br>NVL006 = 38 kg | NVL001: 25+20-30-10 = 5.0<br>NVL005: 20+8-12-0 = 16.0<br>NVL006: 48+10-20-0 = 38.0 | **PASS** |
| **TC-DNM-03** | Chặn ngày cần hàng trong quá khứ | Ngày cần hàng < ngày hiện tại | Báo lỗi, không cho lưu | Báo lỗi: "Ngày cần hàng không được nhỏ hơn ngày hiện tại" | **PASS** |
| **TC-DNM-04** | Hủy đề nghị mua khi `CHO_DUYET` | Đề nghị vừa tạo | Chuyển `DA_HUY`, ghi nhật ký | Chuyển trạng thái `DA_HUY` thành công, đề nghị đã duyệt bị chặn hủy | **PASS** |
| **TC-LSX-01** | Khấu trừ tồn khả dụng BR-06 | Phát lệnh sản xuất (KHSX001) | `ton_kho` giữ nguyên, `vw_ton_kho_kha_dung` giảm | Không chạm `ton_kho`, view giảm trừ chính xác theo lệnh DA_PHAT | **PASS** |
| **TC-LSX-02** | Chặn phát lệnh khi thiếu NVL | Kế hoạch vượt quá tồn khả dụng | Không cho phát lệnh | Hệ thống cảnh báo thiếu NVL và chặn phát lệnh | **PASS** |
| **TC-LSX-03** | Ràng buộc hạn mức KHSX | Số lượng sản xuất vượt kế hoạch | Báo lỗi vượt hạn mức | Chặn không cho tạo lệnh vượt quá số lượng duyệt của KHSX | **PASS** |

### 2.2. Phân hệ Quản lý kho & Điều phối (QLK, NVK)
| Mã ca kiểm thử | Mô tả kiểm thử | Dữ liệu đầu vào | Kỳ vọng | Thực tế | Trạng thái |
|:---|:---|:---|:---|:---|:---:|
| **TC-PB-01** | Thuật toán FEFO phân bổ lô hàng | Lô NVL001 nhiều HSD khác nhau | Ưu tiên lô có HSD gần nhất | Sắp xếp đúng theo `ngayHetHan ASC` | **PASS** |
| **TC-PB-02** | Loại bỏ lô cách ly và hết hạn | Lô `CACH_LY` hoặc HSD < hiện tại | Không xuất hiện trong danh sách chọn | Lô cách ly và lô quá hạn bị lọc bỏ hoàn toàn | **PASS** |
| **TC-PB-03** | Giữ chỗ cấp lô & tránh trừ đúp | Tạo phiếu phân bổ NVL | Tăng `ton_kho.soLuongGiuCho`, cập nhật `nvl_can_cap_lenh_sx` | Giữ chỗ chuyển sang cấp lô, khả dụng view không bị trừ lặp | **PASS** |
| **TC-DP-01** | Gợi ý vị trí chia tách (YCN26100001) | Hàng 200 thùng, sức chứa ô 100 | Chia 2 vị trí (100 + 100) | Gợi ý tự động chia đủ 2 ô kệ còn trống | **PASS** |
| **TC-DP-02** | Chặn hàng kiểm định không đạt | Phiếu có lô `KHONG_DAT` (YCN26100004) | Báo lỗi không đạt QC | Chặn điều phối, thông báo lỗi QC rõ ràng | **PASS** |
| **TC-DP-03** | Chặn kho/vị trí bảo trì | Kho trạng thái `BAO_TRI` (YCN26100010) | Báo lỗi bảo trì | Chặn chọn kho bảo trì | **PASS** |
| **TC-NVK-01** | Xác nhận nhận lệnh điều phối | NVK đăng nhập, click "Đã nhận lệnh" | Chuyển `DANG_THUC_HIEN`, cập nhật thời gian | Trạng thái lệnh đổi sang `DANG_THUC_HIEN`, ghi nhận thời gian nhận | **PASS** |

### 2.3. Phân hệ Kiểm kê & Xử lý hàng lỗi (QLK, QC, BGD)
| Mã ca kiểm thử | Mô tả kiểm thử | Dữ liệu đầu vào | Kỳ vọng | Thực tế | Trạng thái |
|:---|:---|:---|:---|:---|:---:|
| **TC-KK-01** | Tính tổng giá trị chênh lệch | BBKK-2026-001 (Thừa 128k, Thiếu 66k) | Tổng chênh lệch = 194.000 đ | Tính đúng 194.000 đ (128.000 + 66.000) | **PASS** |
| **TC-KK-02** | Không can thiệp `ton_kho` | Lưu cập nhật biên bản kiểm kê | Bảng `ton_kho` không bị thay đổi | `ton_kho` giữ nguyên 100%, chờ quyết định xử lý | **PASS** |
| **TC-KK-03** | Phân cấp phê duyệt theo ngưỡng | Biên bản chênh lệch $\le$ 10tr và $>$ 10tr | $\le$ 10tr: QLK duyệt;<br>$>$ 10tr: BGD duyệt | Hiển thị đúng thẩm quyền duyệt trên modal và form | **PASS** |
| **TC-HL-01** | Đề xuất xử lý hàng lỗi HL001+HL002 | Lập đề xuất cho HL001 (59k) + HL002 (50k) | Tổng tiền = 109.000 đ | Lưu đề xuất đúng 109.000 đ, snapshot số lô và HSD | **PASS** |
| **TC-HL-02** | Tính số lượng còn lại động | Đề xuất một phần số lượng lỗi | `soLuongConLai` giảm theo đề xuất | Tính toán động bằng Repository, khóa `FOR UPDATE` | **PASS** |
| **TC-HL-03** | Phân quyền màn hình hàng lỗi | QC đăng nhập xem danh sách | Ẩn nút "Lập đề xuất", chặn 403 form tạo | QC chỉ xem được kết quả, bị từ chối 403 khi cố tạo mới | **PASS** |

---

## 3. KỊCH BẢN ĐẦU-CUỐI (END-TO-END) VÀ ẢNH CHỤP MINH CHỨNG

Tất cả các ảnh chụp màn hình được lưu trữ tại thư mục: `docs/screenshots/`:

1. **Kịch bản A (LKH - Lập đề nghị mua NVL)**:
   - File: `docs/screenshots/e2e_01_lkh_de_nghi_mua_khsx004.png`
   - Minh chứng: Màn hình lập đề nghị cho KHSX004 hiển thị chính xác nhu cầu NVL001 = 5 kg, NVL005 = 16 kg, NVL006 = 38 kg kèm công thức cân đối tồn khả dụng.
2. **Kịch bản B (LKH - Tạo lệnh sản xuất)**:
   - File: `docs/screenshots/e2e_02_lkh_tao_lenh_san_xuat.png` & `e2e_02b_lkh_danh_sach_lenh_sx.png`
   - Minh chứng: Wizard 4 bước chia xưởng sản xuất, tự động tra cứu định mức BOM bánh kẹo, phát lệnh thành công.
3. **Kịch bản C (QLK - Phân bổ số lượng NVL lưu kho)**:
   - File: `docs/screenshots/e2e_03_qlk_phan_bo_nvl.png` & `e2e_03b_qlk_danh_sach_phan_bo.png`
   - Minh chứng: Wizard phân bổ FEFO sắp xếp lô theo hạn dùng sớm nhất, nút gợi ý tự động, chuyển giữ chỗ cấp lệnh thành giữ chỗ cấp lô.
4. **Kịch bản D (QLK - Điều phối nhập kho & NVK nhận lệnh)**:
   - File: `docs/screenshots/e2e_04_qlk_dieu_phoi_ycn26100001.png` & `e2e_04b_nvk_lenh_duoc_giao.png`
   - Minh chứng: Giao diện điều phối YCN26100001 tự động chia 2 ô kệ (100+100), màn hình NVK hiển thị lệnh được giao kèm nút "Đã nhận lệnh".
5. **Kịch bản E (QLK - Cập nhật chênh lệch kiểm kê)**:
   - File: `docs/screenshots/e2e_05_qlk_chenh_lech_kiem_ke.png` & `e2e_05b_kiem_ke_detail_modal.png`
   - Minh chứng: Biên bản BBKK-2026-001 tổng chênh lệch 194.000 đ, 3 thẻ KPI và hộp quy tắc phê duyệt thẩm quyền.
6. **Kịch bản F (QLK - Đề xuất xử lý hàng lỗi & QC xem)**:
   - File: `docs/screenshots/e2e_06_qlk_de_xuat_hang_loi.png` & `e2e_06b_qc_xem_hang_loi.png`
   - Minh chứng: Đề xuất hàng lỗi HL001 + HL002 = 109.000 đ, tài khoản QC đăng nhập chỉ có quyền đọc, nút lập đề xuất bị ẩn.

---

## 4. KIỂM SOÁT AN TOÀN VÀ TÍNH TOÀN VẸN CỦA SEED DỮ LIỆU

- **Nguyên tắc giao dịch**: Toàn bộ các bài test CLI đều được bao bọc trong khối giao dịch `beginTransaction()` ... `rollBack()`.
- **Kiểm tra sau kiểm thử (chạy 2 lần liên tiếp tests/run_all.php đều PASS 100%)**:
  - `SELECT maSKU, tonKhaDung FROM vw_ton_kho_kha_dung WHERE maSKU IN ('NVL001', 'NVL005', 'NVL006');`
    - `NVL001`: **30.000 kg** (Đúng chuẩn)
    - `NVL005`: **12.000 kg** (Đúng chuẩn)
    - `NVL006`: **20.000 kg** (Đúng chuẩn)
- **Quy chuẩn chất lượng lô hàng lỗi (BR-09)**:
  - 100% các lô của `HL001..HL007` trong `seed.sql` đã được gán trạng thái `CACH_LY` (`lo_hang.trangThaiChatLuong = 'CACH_LY'`).
  - Đã kiểm tra và cô lập các lô này: không bị sử dụng cho phân bổ sản xuất hay điều phối kho thông thường, bảo đảm `HL001..HL007` xuất hiện chuẩn xác trên trang lập đề xuất xử lý hàng lỗi.
- **Tính toán động số lượng lỗi còn lại**:
  - Bảng `hang_loi_hong` trong `schema.sql` đã loại bỏ hoàn toàn cột `soLuongConLai`. Số lượng còn lại được tính toán động qua Repository với khóa giao dịch `SELECT ... FOR UPDATE`.
- **Hủy đề nghị mua NVL (LKH)**:
  - Đề nghị mua khi hủy chuyển sang trạng thái `DA_HUY` (thay vì `TU_CHOI` dành riêng cho BGD từ chối), tuân thủ đúng phân định trách nhiệm tác nhân.
- **Bảo mật và CSRF**: 100% form POST có token CSRF hợp lệ. Mọi truy cập trái quyền hoặc không có token đều bị từ chối (403 Forbidden hoặc điều hướng an toàn).
- **Nhật ký hệ thống**: Mọi thao tác GHI đều ghi bản ghi vào `nhat_ky_he_thong` với IP client, chức năng, hành động, và snapshot `giaTriCu` / `giaTriMoi` dạng JSON.

