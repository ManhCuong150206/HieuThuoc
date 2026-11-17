# An Tâm Pharmacy

Giao diện nhà thuốc trực tuyến của Nhóm 10. Dự án dùng Laravel 12, Blade, Tailwind CSS 4 và Vite. Bản hiện tại có 8 màn hình và dùng dữ liệu mẫu để trình diễn.

## Yêu cầu

- Git để tải mã nguồn.
- PHP 8.2 trở lên và Composer 2. Nếu dùng XAMPP, PHP trên terminal phải trỏ đến bản trong XAMPP.
- Node.js 22.12 trở lên và npm.

Chạy các lệnh dưới đây để kiểm tra các công cụ đã sẵn sàng.

```powershell
git --version
php -v
composer --version
node -v
npm.cmd -v
```

Trên macOS hoặc Linux, dùng `npm` thay cho `npm.cmd` trong các lệnh bên dưới.

## Chạy dự án lần đầu

### 1. Tải dự án

Khi mã dự án đã có trên nhánh `main`, chạy các lệnh sau trong PowerShell từ thư mục bạn muốn lưu dự án.

```powershell
git clone https://github.com/ManhCuong150206/HieuThuoc.git
cd HieuThuoc
```

Nếu đã có thư mục dự án, mở terminal ở thư mục chứa `artisan`, `composer.json` và `package.json` rồi bắt đầu từ bước 2.

### 2. Cài thư viện và tạo `.env`

```powershell
composer install
npm.cmd ci
Copy-Item .env.example .env
```

Chỉ chạy `Copy-Item` khi chưa có `.env`. Trên macOS hoặc Linux, dùng `cp .env.example .env`.

### 3. Cấu hình chế độ demo

Mở file `.env` vừa tạo và dùng các giá trị dưới đây cho những dòng tương ứng.

```dotenv
APP_NAME="An Tâm Pharmacy"
APP_URL=http://127.0.0.1:8000
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

`SESSION_DRIVER=file` và `CACHE_STORE=file` giúp Laravel lưu phiên và dữ liệu đệm trong file cục bộ. Giao diện lấy dữ liệu mẫu từ PHP nên có thể chạy khi MySQL chưa bật. Bản demo không cần tạo cơ sở dữ liệu hay chạy migration.

### 4. Tạo khóa ứng dụng, build và mở web

```powershell
php artisan key:generate
php artisan config:clear
npm.cmd run build
php artisan serve --host=127.0.0.1 --port=8000
```

Chỉ chạy `key:generate` khi `.env` mới tạo hoặc `APP_KEY` chưa có giá trị. Giữ cửa sổ terminal đang chạy rồi mở [http://127.0.0.1:8000](http://127.0.0.1:8000) trên trình duyệt. Nhấn `Ctrl+C` trong terminal để dừng máy chủ.

## Các màn hình

| Màn hình | Đường dẫn để thử |
| --- | --- |
| Trang chủ | `/` |
| Danh sách sản phẩm | `/san-pham` |
| Danh mục thực phẩm chức năng | `/danh-muc/thuc-pham-chuc-nang` |
| Chi tiết sản phẩm | `/san-pham/vitamin-c-500-mg` |
| Giỏ hàng và thanh toán | `/gio-hang` hoặc `/thanh-toan` |
| Đặt hàng thành công | `/dat-hang-thanh-cong` |
| Tài khoản và đơn hàng | `/tai-khoan` |
| Quản trị nhà thuốc | `/quan-tri` |
| Đăng nhập | `/dang-nhap` |

Bạn có thể thử luồng minh họa từ **Giỏ hàng → Đặt hàng → Xem đơn hàng**. Trang quản trị và tài khoản mở trực tiếp, không cần tài khoản demo. Các ô nhập và nút tìm kiếm, đăng nhập, thêm giỏ hàng, thanh toán và phân trang chưa xử lý dữ liệu thật.

## Khi chỉnh giao diện

Giữ `php artisan serve` chạy trong terminal thứ nhất. Trong terminal thứ hai, ở cùng thư mục dự án, chạy `npm.cmd run dev` để Vite cập nhật CSS và JavaScript khi sửa file. Nếu chỉ muốn xem bản build, chạy `npm.cmd run build` sau khi sửa và dùng một terminal để chạy Laravel.

| Thư mục hoặc file | Nội dung |
| --- | --- |
| `routes/web.php` | URL và tên route |
| `app/Http/Controllers/` | Controller chuẩn bị dữ liệu cho view |
| `app/Support/MockData.php` | Dữ liệu mẫu cho giao diện |
| `resources/views/` | Layout, partial, component và các trang Blade |
| `resources/css/` | Theme, font và CSS responsive |
| `public/images/` | Logo và SVG xuất từ Figma |

Có thể chạy `php artisan route:list` để xem các URL và `php artisan test` để chạy bộ kiểm tra có sẵn sau khi build.

## Lỗi thường gặp

| Lỗi | Cách xử lý |
| --- | --- |
| `Could not open input file: artisan` | Chuyển terminal vào đúng thư mục chứa `artisan`. Nếu mới clone và chỉ thấy `README.md`, kiểm tra nhánh Git hoặc đợi mã dự án được đưa lên repo. |
| `php` hoặc `composer` không được nhận diện | Thêm công cụ vào `PATH`, mở lại terminal rồi chạy `php -v` hoặc `composer --version`. |
| Composer báo thiếu extension PHP | Chạy `php --ini` để tìm đúng `php.ini` của PHP CLI. Bật extension mà Composer báo thiếu rồi chạy lại `composer install`. |
| PowerShell chặn `npm.ps1` | Dùng `npm.cmd` như các lệnh trong hướng dẫn. |
| `No application encryption key has been specified` | Kiểm tra `.env` đã tồn tại, rồi chạy `php artisan key:generate`. |
| `Vite manifest not found` hoặc trang không có CSS | Chạy `npm.cmd ci` và `npm.cmd run build` tại thư mục gốc. Nếu đang dùng Vite dev, kiểm tra terminal `npm.cmd run dev` còn chạy. |
| Lỗi MySQL hoặc thiếu bảng `sessions` | Đặt `SESSION_DRIVER=file` và `CACHE_STORE=file` trong `.env`, chạy `php artisan config:clear` rồi khởi động lại Laravel. |
| Cổng `8000` đã được dùng | Chạy `php artisan serve --host=127.0.0.1 --port=8001`, đổi `APP_URL` sang `http://127.0.0.1:8001` nếu cần và mở cổng mới trên trình duyệt. |

Giữ `.env` trên máy cá nhân. Git theo dõi `.env.example` và các file lock để người khác có thể cài lại các thư viện. `vendor/`, `node_modules/` và `public/build/` được tạo từ các bước cài đặt và build.
