# Chuyển giao diện An Tâm Pharmacy sang Clinic

Nhánh `feat/new-ui` bắt đầu từ `2cc19dc`. Nguồn thiết kế là thư mục **Clinic-v1.0.0** của BootstrapMade trong Downloads. Các trang HTML của mẫu dùng Bootstrap 5.3.7, CSS riêng, Bootstrap Icons, AOS, Swiper và GLightbox. Dự án triển khai các bố cục đó bằng Laravel 12, Blade, Tailwind CSS 4, CSS theo nhóm trang và Vite 7. Các font tiếng Việt, logo, ảnh hàng hóa và nội dung lấy từ An Tâm.

## Đối chiếu trước và sau

| Khu vực và đường dẫn | Trước | Sau và phần tương ứng của Clinic |
|---|---|---|
| Khung cửa hàng, mọi trang khách | Header cố định một hàng, footer có phần pháp lý cùng khối điều hướng | Topbar phí giao hàng, hàng thương hiệu và điều hướng, menu mobile, footer giới thiệu và các cột liên kết với dải pháp lý riêng |
| Trang chủ `/` | Tiêu đề và mô tả hai cột, các phần nội dung cao bằng màn hình | Hero giới thiệu bằng chữ, nhấn màu trong tiêu đề, đoạn mô tả ngay bên dưới. Các phần danh mục, sản phẩm, hướng dẫn và bài viết tự giãn theo lượng dữ liệu |
| Sản phẩm `/san-pham`, `/danh-muc/{category}`, `/yeu-thich` | Breadcrumb, tiêu đề và thẻ sản phẩm tách rời | Tiêu đề trang căn giữa, breadcrumb trên nền phụ tại các trang có breadcrumb. Thẻ kiểu Services có trạng thái tồn kho tại mép ảnh. Danh mục và sắp xếp giữ cùng truy vấn |
| Chi tiết `/san-pham/{product}` | Tên sản phẩm nằm trong cột mua hàng | Tên và breadcrumb nằm trong đầu trang Service Details, bên dưới là ảnh, giá, tùy chọn mua, nội dung, đánh giá và sản phẩm liên quan |
| Nội dung `/bai-viet`, `/bai-viet/{post}`, `/huong-dan` | Lưới bài viết và các khối văn bản | Các thẻ danh sách, đầu trang căn giữa, khối đọc bài và các panel hướng dẫn giữ nguyên mốc liên kết |
| Xác thực `/dang-nhap`, `/dang-ky`, `/quen-mat-khau`, `/dat-lai-mat-khau/{token}` | Khung biểu mẫu có cùng một nền | Panel kiểu Appointment với đầu biểu mẫu có nền nhấn và thân biểu mẫu riêng. Tiếp tục dùng component trường nhập, lỗi và autocomplete của dự án |
| Tài khoản `/tai-khoan` | Điều hướng tài khoản cạnh các biểu mẫu và lịch sử | Đầu trang, sidebar kiểu trang chi tiết, các panel hồ sơ, mật khẩu, đơn hàng và đánh giá |
| Giỏ, thanh toán và kết quả `/gio-hang`, `/thanh-toan`, `/dat-hang-thanh-cong/{order?}` | Các nhóm sản phẩm, biểu mẫu giao hàng và tổng tiền | Bố cục Appointment dùng panel, tiêu đề nhóm và tóm tắt đơn. Trang kết quả dùng thẻ xác nhận với biểu tượng lấy màu từ theme |
| Quản trị `/admin`, `/admin/don-hang`, `/admin/khach-hang`, `/admin/danh-gia` | Sidebar, chỉ số, bảng và panel | Thanh tên cửa hàng trên sidebar, tiêu đề nền nhạt, thẻ chỉ số và bảng có đầu cột phân biệt. Bảng rộng cuộn trong panel |
| CRUD `/admin/quan-ly/{resource}`, `/tao`, `/{record}/sua` | Hai view danh sách và biểu mẫu dùng chung | Bảng và panel biểu mẫu cùng hệ giao diện quản trị cho `products`, `categories`, `promotions`, `posts` |
| Báo cáo `/admin/bao-cao` | Tải CSV từ sidebar | Vẫn là liên kết tải CSV trong sidebar mới |

Các component Blade dùng chung gồm `button`, `badge`, `breadcrumb`, `section-title`, `input`, `quantity`, `pagination`, `icon`, `product-image`, `product-card` và `order-card`. Các bảng và panel dùng CSS dùng chung. Thông báo AJAX giữ vai trò status và nút đóng. Hộp xác nhận thao tác tiếp tục dùng cơ chế trình duyệt của dự án.

## Bảng màu

Nguồn màu là `resources/css/colors.css`, được import tại `resources/css/app.css`. Theme của thanh trình duyệt đọc giá trị đã tính của `--color-paper`. Các biểu tượng điều khiển đang hiển thị dùng `currentColor`. Ảnh hàng hóa và logo giữ màu gốc trong tệp hình.

| Vai trò của Clinic | Token của An Tâm | Giá trị |
|---|---|---|
| Primary, accent, nav hover · xanh #175cdd | `--color-brand` | #3f6d56 |
| Secondary, dải nền tối · #021418 | `--color-dark` | #2e5442 |
| Nền trang · #ffffff | `--color-paper` | #f5f3ee |
| Surface, dropdown · #ffffff | `--color-surface` | #ffffff |
| Nền phụ · #f4f8ff | `--color-pale`, `--color-stone` | #e3eae4, #ebe7df |
| Chữ và tiêu đề · #3c4049, #112344 | `--color-ink`, `--color-muted` | #1d2420, #5b605c |
| Viền nhẹ, viền mạnh | `--color-line`, `--color-line-strong` | #dad6cd, #b9b4a9 |
| Footer, chữ phụ trên footer | `--color-night`, `--color-night-muted` | #1d2420, #a9ada7 |
| Thành công | `--color-brand` trên `--color-pale` | #3f6d56 trên #e3eae4 |
| Cảnh báo | `--color-orange` trên `--color-warm` | #8a5a1c trên #f4ecdc |
| Lỗi | `--color-danger` | #a3332b |

Tỷ lệ tương phản chữ/nền theo độ chói tương đối: chữ chính trên nền giấy 14,29:1, chữ phụ 5,79:1, xanh thương hiệu 5,36:1. Chữ tiêu đề xanh đậm trên nền nhạt đạt 6,97:1. Lỗi trên nền trắng đạt 6,85:1, cảnh báo trên nền ấm đạt 5,02:1.

## Logic và phạm vi

Định tuyến và middleware nằm ở `routes/web.php`. Controller, service, model và lớp hỗ trợ nằm trong `app/`. Blade cung cấp action, HTTP method, CSRF, tên trường, điều kiện theo quyền và binding từ controller. `resources/js/app.js` xử lý menu, xác nhận, tổng tiền và trạng thái đặt đơn. `resources/js/shopping.js` xử lý AJAX, số lượng trên giỏ, yêu thích và thông báo. Đối chiếu với commit gốc cho thấy không có thay đổi trong các tệp nghiệp vụ, JavaScript ứng dụng, migration, test hoặc manifest thư viện.

Clinic không có màn hình giỏ hàng hay quản trị nhà thuốc. Các trang đó kết hợp sidebar của trang chi tiết với panel và nhóm trường Appointment. Banner trang chủ tiếp tục chỉ giới thiệu bằng chữ theo yêu cầu trước đó. Email đặt lại mật khẩu thuộc luồng gửi thư hiện có. Các view đang dùng không khởi tạo canvas, WebGL, bản đồ hoặc trình tải tệp. Trường ảnh quản trị tiếp tục nhận URL.

Đã bỏ view Laravel `welcome` không có route, CSS của hero và các khối minh họa trang chủ cũ, hai ảnh lineup và bốn ảnh phụ không còn tham chiếu. Ảnh sản phẩm được tra theo tên trong database vẫn được giữ. Menu bác sĩ, lịch khám, bản đồ, testimonial, logo, ảnh bệnh viện, bộ xử lý PHP mẫu và thư viện JavaScript của Clinic không được đưa vào ứng dụng. Số dependency mới là **0**.

## Chạy và kiểm tra

Với cấu hình và cơ sở dữ liệu hiện có, chạy tại thư mục chứa `artisan`:

```powershell
composer install
npm.cmd ci
npm.cmd run build
php artisan serve --host=127.0.0.1 --port=8000
```

Mở [An Tâm Pharmacy](http://127.0.0.1:8000/). Khi sửa CSS, có thể chạy `npm.cmd run dev` trong terminal thứ hai. Dùng `php artisan test` để chạy kiểm thử trên SQLite trong bộ nhớ. Dự án không khai báo lệnh npm lint.

## Kết quả kiểm tra

| Kiểm tra | Kết quả |
|---|---|
| Cài đặt, build, cú pháp | Composer và npm cài thành công từ lockfile. Vite build, Laravel Pint ở chế độ kiểm tra và biên dịch Blade đều đạt |
| Hành vi nghiệp vụ | 70 kiểm thử đạt, 607 assertions. So sánh 16 view Blade đã sửa với commit gốc xác nhận 64 biểu thức liên quan đến form, CSRF, giả lập HTTP method và selector tương tác vẫn khớp |
| Route và liên kết | Laravel liệt kê 47 route của ứng dụng, gồm cả thao tác ghi. Chrome đã mở 32 đường dẫn trang trong ba vai trò khách, khách hàng và quản trị viên. Kiểm tra 74 tổ hợp vai trò/liên kết nội bộ, gồm fragment khi trang đích không chuyển hướng. Tải CSV trả HTTP 200 |
| Thao tác trên trình duyệt | Mở/đóng menu mobile, đăng nhập, thêm giỏ, cập nhật huy hiệu giỏ, lưu yêu thích, chọn khuyến mãi, xác nhận đơn, đăng xuất và mở các trang quản trị. Các thao tác ghi dùng SQLite thử nghiệm riêng |
| Responsive và trợ năng | Kiểm tra trang chủ và catalog ở 390, 768 và 1440 px. Các trang được duyệt không có lỗi tràn ngang, ảnh thiếu thuộc tính alt hay trường nhập thiếu nhãn. Đã rà kiểu focus, màu chữ, biểu tượng trang trí và ảnh chụp giao diện. Đây là kiểm tra nhanh, chưa phải đánh giá bằng trình đọc màn hình |
| Tài nguyên và màu | Chrome không ghi nhận lỗi console, tài nguyên HTTP lỗi hay ảnh hỏng trong lượt kiểm tra. Quét Blade, CSS giao diện và CSS chuyển động không thấy literal hex/RGB ngoài tệp token hoặc các utility màu mẫu đã kiểm tra. Nội dung và ảnh do quản trị viên nhập sau này phụ thuộc dữ liệu thực tế |
| Chạy cục bộ | Máy chủ phát triển thực tế trả HTTP 200 tại `http://127.0.0.1:8000/`. Trình kiểm thử dùng cổng và dữ liệu riêng |
