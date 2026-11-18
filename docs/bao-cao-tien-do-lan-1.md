# Báo cáo tiến độ lần 1 — An Tâm Pharmacy

| Thông tin | Nội dung |
| --- | --- |
| Học phần | Lập trình Web |
| Nhóm | Nhóm 10 |
| Đề tài | Website nhà thuốc trực tuyến An Tâm Pharmacy |
| Ngày tổng hợp | 17/11/2026 |
| Hạn nộp trên hệ thống môn học | 23:59 ngày 18/11/2026 |
| Tài liệu này | Nội dung bàn giao để dựng slide và chuẩn bị demo |

## 1. Tiến độ hiện tại

**Nhóm đã hoàn thành bản thiết kế Figma, xây dựng 8 màn hình trên Laravel, nối điều hướng và xác nhận Laravel kết nối được với MySQL. Giai đoạn còn lại gồm hai phần: triển khai logic nghiệp vụ với dữ liệu thật và kiểm thử các luồng sau khi tích hợp.**

An Tâm Pharmacy là website nhà thuốc trực tuyến. Người mua có thể xem các nhóm sản phẩm, đọc thông tin chi tiết và theo dõi luồng đặt hàng trên bản demo. Màn hình quản trị minh họa cách nhân viên theo dõi đơn hàng, doanh thu và tình trạng tồn kho.

| Hạng mục | Trạng thái | Kết quả có thể trình bày |
| --- | --- | --- |
| Thiết kế giao diện trên Figma | Hoàn thành | 8 màn hình desktop. |
| Nền tảng Laravel và cấu trúc MVC | Hoàn thành | Route, controller, Blade layout, partial và component đã được tổ chức theo từng vai trò. |
| Giao diện và điều hướng | Hoàn thành trong phạm vi bản demo | 8 màn hình, 10 route GET; giao diện đã kiểm tra ở desktop, tablet và điện thoại. |
| Kết nối cơ sở dữ liệu | Hoàn thành | Laravel kết nối MySQL cục bộ và chạy thành công truy vấn đọc trên database `group10`. |
| Logic nghiệp vụ | Còn lại | Đọc và ghi dữ liệu thật, xác thực, giỏ hàng, đặt hàng và quản trị. |
| Kiểm thử nghiệp vụ và tích hợp | Còn lại | Kiểm thử các chức năng sau khi triển khai logic, sửa lỗi và rà soát trước khi bàn giao. |

## 2. Chi tiết phần đã làm

### 2.1. Thiết kế và dựng giao diện

- Dùng Figma để xác định bố cục, màu, font, khoảng cách và các khối dùng chung.
- Dựng lại giao diện bằng Blade, Tailwind CSS 4 và CSS riêng. Vite biên dịch CSS và JavaScript.
- Dùng font Be Vietnam Pro, logo An Tâm hiện có và 34 SVG xuất từ Figma cho icon và minh họa.
- Dàn lại cột, thẻ sản phẩm và biểu mẫu ở màn hình nhỏ. Đã kiểm tra ba chiều rộng 1440, 768 và 390 px.
- Nối logo, menu, danh mục và luồng đặt hàng minh họa. Menu thể hiện mục đang xem. Nút và link có trạng thái hover và focus bàn phím.

| Màn hình | URL mẫu | Phần đã thể hiện |
| --- | --- | --- |
| Trang chủ | `/` | Banner giới thiệu, danh mục, sản phẩm bán chạy, sản phẩm mới và Góc sức khỏe. |
| Danh mục và tìm kiếm | `/san-pham`, `/danh-muc/thuc-pham-chuc-nang` | Lưới sản phẩm, bộ lọc, sắp xếp và phân trang minh họa. |
| Chi tiết sản phẩm | `/san-pham/vitamin-c-500-mg` | Ảnh, tên, giá, thông tin sử dụng, lưu ý và sản phẩm liên quan. |
| Giỏ hàng và thanh toán | `/gio-hang`, `/thanh-toan` | Sản phẩm trong giỏ, biểu mẫu giao hàng, phương thức thanh toán và tóm tắt đơn. |
| Đặt hàng thành công | `/dat-hang-thanh-cong` | Mã đơn, trạng thái chờ xác nhận, tổng tiền và đường dẫn xem đơn. |
| Tài khoản và đơn hàng | `/tai-khoan` | Thông tin cá nhân và lịch sử đơn hàng minh họa. |
| Quản trị nhà thuốc | `/quan-tri` | Tổng quan, biểu đồ doanh thu, cảnh báo tồn kho và bảng đơn hàng gần đây. |
| Đăng nhập | `/dang-nhap` | Biểu mẫu tài khoản, mật khẩu và tùy chọn ghi nhớ. |

### 2.2. Cấu trúc dự án

| Thành phần | Vai trò |
| --- | --- |
| `routes/web.php` | Khai báo 10 route GET có tên. Hai URL giỏ hàng và thanh toán dùng cùng màn hình. |
| `app/Http/Controllers/` | Nhận yêu cầu theo từng nhóm trang và truyền dữ liệu sang Blade. |
| `app/Support/MockData.php` | Tập trung dữ liệu mẫu về sản phẩm, danh mục, tài khoản và đơn hàng. |
| `resources/views/layouts/` và `partials/` | Khung HTML và các phần dùng chung như header và footer. |
| `resources/views/components/` | Thành phần tái sử dụng như thẻ sản phẩm, nút, badge, breadcrumb, ô nhập liệu và thẻ đơn hàng. |
| `resources/views/pages/` | Nội dung riêng của từng màn hình. |
| `README.md` | Hướng dẫn tải mã nguồn, cấu hình môi trường, build và chạy bản demo. |

Hiện tại controller truyền dữ liệu mẫu sang view. Khi chuyển sang MySQL, nhóm có thể cập nhật nguồn dữ liệu ở tầng xử lý và giữ phần lớn cấu trúc Blade đang có.

### 2.3. Kết nối cơ sở dữ liệu

- Dùng MySQL cục bộ qua XAMPP với database `group10`. Laravel đọc cấu hình kết nối từ `.env`.
- Đã kiểm tra qua chính ứng dụng Laravel bằng truy vấn đọc. Kết quả trả về driver `mysql`, database `group10` và giá trị kiểm tra `1`.
- Kết nối đã sẵn sàng cho phần triển khai logic. Các trang demo vẫn hiển thị dữ liệu mẫu. Việc đưa dữ liệu sản phẩm, người dùng và đơn hàng thật lên giao diện thuộc giai đoạn triển khai nghiệp vụ.

### 2.4. Kiểm tra đã thực hiện ở giai đoạn giao diện

- Chạy dự án trên localhost, kiểm tra 18 URL mẫu trả HTTP 200 và slug sản phẩm không hợp lệ trả HTTP 404.
- Kiểm tra 8 màn hình trên 3 chiều rộng. Font và ảnh tải được, trang không tràn ngang ở các kích thước đã thử.
- Kiểm tra luồng bấm logo về trang chủ, chọn danh mục, mở giỏ hàng, sang màn hình xác nhận và xem đơn hàng.
- Vite build thành công, Pint đạt và 2 test Laravel có sẵn đã chạy qua. Kiểm thử nghiệp vụ với dữ liệu thật sẽ thực hiện sau khi tích hợp.

## 3. Hai phần việc còn lại

### Phần 1 — Triển khai logic nghiệp vụ

| Thứ tự đề xuất | Công việc | Điều kiện hoàn thành |
| --- | --- | --- |
| 1 | Hoàn thiện cấu trúc bảng và quan hệ cho danh mục, sản phẩm, người dùng, đơn hàng và chi tiết đơn theo phạm vi đồ án. | Có dữ liệu thử và truy vấn được qua Laravel. |
| 2 | Đọc danh mục, danh sách và chi tiết sản phẩm từ MySQL. Nối tìm kiếm, lọc và phân trang. | Trang hiển thị dữ liệu trong database và xử lý được trường hợp không có kết quả. |
| 3 | Nối đăng nhập, tài khoản và phân quyền quản trị. | Người dùng xem được thông tin của mình. Trang quản trị yêu cầu quyền phù hợp. |
| 4 | Nối giỏ hàng, kiểm tra dữ liệu giao hàng, tính tổng và tạo đơn. | Người dùng thêm, sửa, xóa được sản phẩm trong giỏ. Đơn và chi tiết đơn được lưu và hiển thị lại trong tài khoản. |
| 5 | Nối dữ liệu bảng điều khiển và xử lý đơn ở màn hình quản trị. | Nhân viên xem được đơn thật và thay đổi trạng thái theo luồng đã thống nhất. |

### Phần 2 — Kiểm thử và sửa lỗi

- **Danh mục và sản phẩm** — Tìm theo từ khóa, lọc danh mục, phân trang, kiểm tra sản phẩm không tồn tại.
- **Đăng nhập và quyền truy cập** — Thông tin đúng, mật khẩu sai, khách chưa đăng nhập và tài khoản thường truy cập trang quản trị.
- **Giỏ hàng và đặt hàng** — Giỏ trống, số lượng sai, thiếu thông tin giao hàng, tính tổng đúng và lưu đúng chi tiết đơn.
- **Tài khoản và quản trị** — Người dùng chỉ xem được đơn của mình. Trạng thái đổi ở quản trị phải hiển thị đúng ở tài khoản.
- **Tích hợp và nghiệm thu** — Chạy lại luồng từ trang chủ đến xem đơn trên desktop và điện thoại. Ghi lỗi, sửa và kiểm tra lại.

## 4. Dàn ý bàn giao cho người làm slide

| Slide | Tiêu đề | Nội dung đưa lên slide | Hình minh họa |
| --- | --- | --- | --- |
| 1 | Báo cáo tiến độ lần 1 | An Tâm Pharmacy, Nhóm 10, học phần Lập trình Web. | Logo và ảnh trang chủ. |
| 2 | Mục tiêu và tiến độ | Website nhà thuốc trực tuyến. Hoàn thành Figma, giao diện Laravel, điều hướng và kết nối MySQL. Còn triển khai logic và kiểm thử. | Bảng trạng thái rút gọn từ mục 1. |
| 3 | Các màn hình đã dựng | Nhóm màn hình mua sắm gồm trang chủ, danh mục, chi tiết, giỏ hàng và xác nhận. Nhóm tài khoản gồm đăng nhập, tài khoản và quản trị. | Ghép trang chủ, danh mục, chi tiết và quản trị. |
| 4 | Cấu trúc kỹ thuật | Laravel 12, Blade, Tailwind 4, Vite và MySQL. Luồng xử lý hiện tại là Route → Controller → dữ liệu mẫu → Blade. | Sơ đồ một dòng hoặc ảnh cây thư mục. |
| 5 | Cơ sở dữ liệu | Laravel đã kết nối MySQL cục bộ. Truy vấn kiểm tra trên `group10` thành công. Bước tiếp theo là đưa dữ liệu thật vào các luồng nghiệp vụ. | Ảnh tên database trong phpMyAdmin hoặc kết quả truy vấn. |
| 6 | Demo và kiểm tra giao diện | Mở danh mục, chọn Vitamin C, mở giỏ hàng từ header, bấm đặt hàng minh họa rồi chọn Xem đơn hàng. | Demo trực tiếp hoặc ảnh giỏ hàng và xác nhận đơn. |
| 7 | Kế hoạch tiếp theo | Triển khai logic nghiệp vụ → kiểm thử từng chức năng → kiểm thử toàn luồng và sửa lỗi. | Hai nhóm đầu việc ở mục 3. |

**Câu nói mở đầu gợi ý**

> Nhóm 10 đã dựng xong 8 màn hình của An Tâm Pharmacy trên Laravel, nối điều hướng để chạy demo và xác nhận Laravel kết nối được với MySQL. Giai đoạn tiếp theo, nhóm sẽ nối dữ liệu thật, hoàn thiện các chức năng mua hàng và quản trị, sau đó kiểm thử toàn bộ luồng.

Khi làm slide, ưu tiên ảnh chụp giao diện đang chạy để thể hiện kết quả triển khai. Dùng ảnh Figma để minh họa bản thiết kế nếu cần đối chiếu. Khi chụp phần database, chỉ đưa tên database hoặc kết quả truy vấn lên slide.

- [Thiết kế Figma An Tâm Pharmacy](https://www.figma.com/design/YiKDTDY4m01iSZkQzM4SS4/An-T%C3%A2m-Pharmacy-%E2%80%A2-Website-nh%C3%A0-thu%E1%BB%91c-%7C-Nh%C3%B3m-10?node-id=0-1)
- [Repo HieuThuoc](https://github.com/ManhCuong150206/HieuThuoc)
---------
