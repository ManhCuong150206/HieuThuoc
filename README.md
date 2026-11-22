# An Tâm Pharmacy

Website nhà thuốc trực tuyến của Nhóm 10, dùng Laravel 12, Blade, Tailwind CSS 4 và Vite. Các chức năng mua hàng, tài khoản và quản trị dùng database thật theo schema nhóm.

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

### 3. Cấu hình database

Mở file `.env` vừa tạo và dùng các giá trị dưới đây cho những dòng tương ứng.

```dotenv
APP_NAME="An Tâm Pharmacy"
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=group10
DB_USERNAME=root
DB_PASSWORD=
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

`DB_PORT=3307` khớp file dump `group10.sql` ngày 21/11/2026; chỉnh cổng, tên đăng nhập và mật khẩu theo máy của bạn. Bật MySQL/MariaDB và tạo database `group10` với charset `utf8mb4`. Có thể import file dump của nhóm để có dữ liệu, hoặc dùng database trống.

`SESSION_DRIVER=file` và `CACHE_STORE=file` giúp Laravel lưu phiên và dữ liệu đệm trong file cục bộ. Sau khi cấu hình, chạy:

```powershell
php artisan config:clear
php artisan migrate
```

Migration nghiệp vụ tạo các bảng còn thiếu theo file dump, giữ nguyên các bảng đã tồn tại và không chèn dữ liệu mẫu. Migration này không tự nâng cấp cấu trúc bảng đã tồn tại từ phiên bản database khác. Migration mặc định Laravel chỉ tạo bảng hạ tầng, không tạo lại bảng `users` theo mẫu đăng nhập mặc định.

Baseline nghiệp vụ không hỗ trợ `migrate:rollback` vì có thể gặp bảng được import trước khi có lịch sử migration. `migrate:fresh` vẫn xóa toàn bộ bảng; chỉ dùng với database kiểm thử có thể bỏ.

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
| Danh mục thực phẩm chức năng trong dump | `/danh-muc/3` |
| Chi tiết sản phẩm trong dump | `/san-pham/64` |
| Giỏ hàng và thanh toán | `/gio-hang` hoặc `/thanh-toan` |
| Thông tin đơn sau khi đặt | `/dat-hang-thanh-cong/{id}` (chỉ chủ đơn được xem) |
| Tài khoản và đơn hàng | `/tai-khoan` |
| Quản trị nhà thuốc | `/admin` |
| Đăng nhập | `/dang-nhap` |
| Quên mật khẩu | `/quen-mat-khau` |
| Sản phẩm yêu thích | `/yeu-thich` |
| Tin tức sức khỏe | `/bai-viet` |
| Hướng dẫn mua hàng | `/huong-dan` |

Đăng ký tại `/dang-ky` rồi đăng nhập để mua hàng. Tìm kiếm theo tên/thương hiệu, sắp xếp, phân trang; thêm hoặc mua ngay từ chi tiết sản phẩm. Giỏ hàng lưu theo hồ sơ khách hàng, hỗ trợ cập nhật số lượng, xóa từng dòng và xóa toàn bộ.

Thêm vào giỏ và lưu yêu thích dùng Ajax: thông báo tại chỗ, cập nhật số giỏ hàng và trạng thái nút mà không tải lại trang. Lỗi tồn kho/phiên đăng nhập được hiển thị tại chỗ; nút Mua ngay vẫn chuyển tới thanh toán. Các form vẫn hoạt động bằng POST thông thường khi JavaScript bị tắt.

Đặt hàng lưu `orders`, `order_items` và `payments` trong cùng transaction, trừ tồn kho và chỉ xóa giỏ sau khi lưu thành công. Giá, ưu đãi và số lượng được tính lại từ database; mã xác nhận ngăn tạo đơn trùng khi gửi lại form. Phí giao hàng hiện tại là **25.000đ/đơn**.

**Tiền mặt:** theo yêu cầu của bản hiện tại, đặt hàng sẽ tự duyệt đơn thành `CONFIRMED`, thanh toán thành `SUCCESS` và lưu ngày thanh toán. Đây là hành vi của ứng dụng; không kết nối một cổng thanh toán. **QR:** hiển thị “Đang bổ sung”, bị khóa trên giao diện và bị từ chối ở backend.

Trong tài khoản có cập nhật hồ sơ/địa chỉ, đổi mật khẩu, xem chi tiết đơn, hủy trước khi giao (hoàn tồn kho đúng một lần), đặt lại và yêu thích. Đơn `COMPLETED` mới được đánh giá; mỗi dòng đơn một đánh giá và cần quản trị duyệt trước khi công khai.

Quản trị tại `/admin` hỗ trợ sản phẩm, giá và tồn kho, hạn dùng, danh mục, ưu đãi theo thời gian, bài viết, khóa/mở khách hàng, xử lý đơn, duyệt đánh giá và xuất CSV. Ngừng sản phẩm/ưu đãi giữ nguyên lịch sử; ẩn bài viết chuyển về nháp. Danh mục đang có sản phẩm không được xóa.

### Test nhanh với database nhóm

1. Đăng nhập tài khoản admin sẵn có trong database, vào **Sản phẩm & tồn kho → Sửa**, đặt số lượng lớn hơn 0 và giá bán lớn hơn 0 cho sản phẩm muốn thử. File dump ban đầu có toàn bộ tồn kho bằng 0; ứng dụng không tự bịa số lượng.
2. Đăng xuất, đăng nhập tài khoản khách có đủ hồ sơ, thêm sản phẩm vào giỏ và đi tới thanh toán.
3. Điền địa chỉ, chọn ưu đãi nếu có, chọn tiền mặt và xác nhận. Xem mã đơn trong tài khoản; kiểm tra các bảng `orders`, `order_items`, `payments`, tồn kho giảm và giỏ được xóa.
4. Test hủy trước khi giao để kiểm tra hoàn kho; hoặc dùng admin chuyển đơn lần lượt sang đang xử lý/đang giao/đã giao/hoàn thành rồi gửi đánh giá bằng tài khoản khách.

Luồng quên mật khẩu gửi liên kết qua mailer Laravel, token được băm trong `password_reset_tokens`, có hạn 60 phút và dùng một lần. Với `MAIL_MAILER=log`, email nằm trong `storage/logs/laravel.log`; để gửi tới hộp thư thật, cấu hình SMTP trong `.env`. Không đưa log hoặc liên kết khôi phục lên Git.

## Khai báo database

12 bảng trong `group10.sql` đã có model, quan hệ và migration tương ứng:

| Bảng | Model |
| --- | --- |
| `accounts` | `Account` — xác thực, quyền, hồ sơ và bài viết |
| `users` | `CustomerProfile` / `User` — hồ sơ khách hàng, giỏ hàng, đơn hàng và yêu thích |
| `categories` | `Category` |
| `products` | `Product` |
| `cart_items` | `CartItem` |
| `promotions` | `Promotion` |
| `orders` | `Order` |
| `order_items` | `OrderItem` |
| `payments` | `Payment` |
| `favorites` | `Favorite` — pivot khóa ghép `user_id, product_id` |
| `reviews` | `Review` |
| `posts` | `Post` |

`users.id` là ID hồ sơ, khác với `accounts.id`. Các quan hệ giỏ hàng, đơn hàng và yêu thích tham chiếu ID hồ sơ. Model không tự ghi `updated_at` vì dump không có cột này. Các cột ngày, số lượng và trạng thái bật/tắt có cast phù hợp.

Quản lý mục yêu thích qua `$profile->favoriteProducts()->attach($productId)`, `updateExistingPivot(...)` và `detach($productId)`; bảng này không có cột `id`. Logic giỏ hàng và đặt/hủy/đặt lại đơn nằm trong `app/Services/`.

`DatabaseSeeder` không tự tạo tài khoản hoặc sao chép dữ liệu cá nhân từ dump. Khi cần dữ liệu phát triển, dùng `Account::factory()` và `User::factory()`; factory hồ sơ tự tạo tài khoản liên quan. Bộ kiểm thử database chạy migration thật trên SQLite trong bộ nhớ, tách khỏi database MySQL trong `.env`.

## Khi chỉnh giao diện

Giữ `php artisan serve` chạy trong terminal thứ nhất. Trong terminal thứ hai, ở cùng thư mục dự án, chạy `npm.cmd run dev` để Vite cập nhật CSS và JavaScript khi sửa file. Nếu chỉ muốn xem bản build, chạy `npm.cmd run build` sau khi sửa và dùng một terminal để chạy Laravel.

| Thư mục hoặc file | Nội dung |
| --- | --- |
| `routes/web.php` | URL và tên route |
| `app/Http/Controllers/` | Controller chuẩn bị dữ liệu cho view |
| `app/Services/` | Giỏ hàng, kiểm tra tồn kho, giao dịch đặt/hủy đơn |
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
