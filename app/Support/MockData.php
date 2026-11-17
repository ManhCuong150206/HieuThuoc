<?php

namespace App\Support;

final class MockData
{
    public static function categories(): array
    {
        return [
            'thuoc' => ['slug' => 'thuoc', 'name' => 'Thuốc', 'description' => 'Tra cứu & tìm hiểu', 'icon' => 'pill'],
            'thuc-pham-chuc-nang' => ['slug' => 'thuc-pham-chuc-nang', 'name' => 'Thực phẩm chức năng', 'description' => 'Bổ sung mỗi ngày', 'icon' => 'category-heart'],
            'dung-cu-y-te' => ['slug' => 'dung-cu-y-te', 'name' => 'Dụng cụ y tế', 'description' => 'Theo dõi sức khỏe', 'icon' => 'medical-plus'],
            'cham-soc-ca-nhan' => ['slug' => 'cham-soc-ca-nhan', 'name' => 'Chăm sóc cá nhân', 'description' => 'Chăm chút bản thân', 'icon' => 'category-shield'],
        ];
    }

    public static function products(): array
    {
        $items = [
            ['vitamin-c-500-mg', 'Vitamin C 500 mg', 'Hộp 60 viên', 'Thực phẩm bổ sung', 'thuc-pham-chuc-nang', '125.000đ', 'Bán chạy', 'vitamin', 'AT-001'],
            ['nuoc-muoi-sinh-ly', 'Nước muối sinh lý 0,9%', 'Chai 500 ml', 'Chăm sóc cá nhân', 'cham-soc-ca-nhan', '12.000đ', 'Thiết yếu', 'saline', 'AT-002'],
            ['nhiet-ke-dien-tu', 'Nhiệt kế điện tử', 'Hộp 1 chiếc', 'Dụng cụ y tế', 'dung-cu-y-te', '89.000đ', 'Dễ sử dụng', 'thermometer', 'AT-003'],
            ['khau-trang-y-te-4-lop', 'Khẩu trang y tế 4 lớp', 'Hộp 50 cái', 'Dụng cụ y tế', 'dung-cu-y-te', '35.000đ', 'Mới', 'mask', 'AT-004'],
            ['vitamin-tong-hop', 'Vitamin tổng hợp', 'Hộp 60 viên', 'Thực phẩm bổ sung', 'thuc-pham-chuc-nang', '185.000đ', 'Mới', 'vitamin', 'AT-005'],
            ['dung-dich-ve-sinh-tay', 'Dung dịch vệ sinh tay', 'Chai 500 ml', 'Chăm sóc cá nhân', 'cham-soc-ca-nhan', '45.000đ', 'Chăm sóc cá nhân', 'saline', 'AT-006'],
        ];

        $products = [];

        foreach ($items as [$slug, $name, $package, $type, $category, $price, $badge, $art, $sku]) {
            $products[$slug] = [
                'slug' => $slug,
                'name' => $name,
                'package' => $package,
                'type' => $type,
                'typeLabel' => mb_strtoupper($type),
                'category' => $category,
                'price' => $price,
                'badge' => $badge,
                'image' => 'product-'.$art,
                'gallery' => [$slug === 'vitamin-c-500-mg' ? 'detail-vitamin' : 'product-'.$art],
                'thumbnails' => $slug === 'vitamin-c-500-mg'
                    ? array_fill(0, 3, 'detail-vitamin-alt')
                    : ['product-'.$art],
                'facts' => $slug === 'vitamin-c-500-mg' ? [
                    'Thương hiệu: An Tâm Care (minh họa)',
                    'Dạng bào chế: Viên nén  •  Đơn vị bán: Hộp',
                    'Hạn sử dụng: xem trên bao bì sản phẩm',
                ] : [],
                'sku' => $sku,
            ];
        }

        return $products;
    }

    public static function featured(): array
    {
        return array_slice(self::products(), 0, 4);
    }

    public static function home(): array
    {
        return [
            'categories' => self::categories(),
            'products' => self::featured(),
            'promises' => [
                ['shield', 'Thông tin minh bạch', 'Nguồn gốc rõ ràng'],
                ['chat', 'Dược sĩ đồng hành', 'Giải đáp khi bạn cần'],
                ['truck', 'Giao hàng thuận tiện', 'Theo dõi từng đơn hàng'],
                ['heart', 'Chăm sóc tận tâm', 'Hỗ trợ trước và sau mua'],
            ],
            'articles' => [
                ['Sắp xếp tủ thuốc gia đình', 'Các lưu ý khi bảo quản và kiểm tra hạn dùng.'],
                ['Đọc kỹ thông tin trên nhãn', 'Tìm hiểu thành phần và hướng dẫn sử dụng.'],
                ['Chăm sóc sức khỏe chủ động', 'Những thói quen nhỏ trong sinh hoạt hằng ngày.'],
            ],
        ];
    }

    public static function catalog(?array $category = null): array
    {
        $products = self::products();

        if ($category !== null) {
            $products = array_filter($products, fn (array $product): bool => $product['category'] === $category['slug']);
        }

        return [
            'category' => $category,
            'products' => $products,
            'count' => count($products),
            'categories' => self::categories(),
            'filters' => [
                'Khoảng giá' => ['Dưới 100.000đ', '100.000đ – 300.000đ', 'Trên 300.000đ'],
                'Thương hiệu' => ['An Tâm Care', 'Thương hiệu A', 'Thương hiệu B'],
                'Dạng bào chế' => ['Viên nén', 'Viên nang', 'Dung dịch'],
            ],
        ];
    }

    public static function productInformation(): array
    {
        return [
            ['thanh-phan', 'Thông tin sản phẩm', 'Thành phần & công dụng', 'Khu vực hiển thị thành phần và công dụng theo nhãn được phê duyệt của từng sản phẩm. Dược sĩ kiểm tra thông tin trước khi đăng bán.'],
            ['cach-dung', 'Hướng dẫn sử dụng', 'Liều dùng & cách dùng', 'Xem hướng dẫn trên bao bì hoặc tham khảo dược sĩ. Thông tin liều dùng cần được cập nhật theo sản phẩm thực tế.'],
            ['luu-y', 'Lưu ý & bảo quản', 'Chống chỉ định & tác dụng phụ', 'Kiểm tra thành phần, cảnh báo và các trường hợp không nên sử dụng theo tài liệu của nhà sản xuất.'],
            ['danh-gia', 'Đánh giá (24)', 'Đánh giá từ khách hàng', '4,8 / 5  •  24 đánh giá minh họa. Chỉ khách hàng đã mua mới có thể viết đánh giá.'],
        ];
    }

    public static function checkout(): array
    {
        $products = self::products();

        return [
            'items' => [
                ['product' => $products['vitamin-c-500-mg'], 'image' => 'cart-vitamin'],
                ['product' => $products['khau-trang-y-te-4-lop'], 'image' => 'cart-mask'],
            ],
            'payments' => [
                ['cod', 'Thanh toán khi nhận hàng (COD)', 'Thanh toán trực tiếp cho nhân viên giao hàng'],
                ['transfer', 'Chuyển khoản ngân hàng', 'Thông tin chuyển khoản hiển thị sau khi đặt hàng'],
                ['wallet', 'Ví điện tử / VNPay', 'Chuyển đến cổng thanh toán để hoàn tất'],
            ],
            'summary' => [
                'Tạm tính (2 sản phẩm)' => '160.000đ',
                'Phí vận chuyển' => '25.000đ',
                'Giảm giá' => '0đ',
            ],
            'order' => self::order(),
        ];
    }

    public static function order(): array
    {
        return [
            'code' => '#AT1025',
            'payment' => 'COD • Thanh toán khi nhận hàng',
            'total' => '185.000đ',
            'email' => 'minhanh@example.com',
            'status' => 'Chờ xác nhận',
        ];
    }

    public static function account(): array
    {
        return [
            'profile' => ['name' => 'Nguyễn Minh Anh', 'firstName' => 'Minh Anh', 'email' => 'minhanh@example.com', 'phone' => '0901 234 567'],
            'orders' => [
                ['#AT1025', 'Chờ xác nhận', 'Vitamin C 500 mg + Khẩu trang y tế', '185.000đ', 'Xem chi tiết'],
                ['#AT1020', 'Đang giao', 'Sản phẩm chăm sóc sức khỏe', '114.000đ', 'Xem chi tiết'],
                ['#AT1008', 'Đã giao', 'Sản phẩm chăm sóc sức khỏe', '160.000đ', 'Đặt lại đơn'],
            ],
        ];
    }

    public static function dashboard(): array
    {
        return [
            'menu' => [
                ['admin-grid', 'Tổng quan'], ['admin-bag', 'Đơn hàng'], ['admin-pill', 'Sản phẩm'],
                ['admin-doc', 'Tồn kho & hạn dùng'], ['admin-user', 'Khách hàng'], ['admin-grid', 'Danh mục'],
                ['admin-truck', 'Nhà cung cấp'], ['admin-heart', 'Khuyến mãi'], ['admin-chat', 'Đánh giá'], ['admin-doc', 'Báo cáo'],
            ],
            'metrics' => [
                ['Doanh thu', '28,6 triệu', '↑ 12,5% so với tháng trước'],
                ['Đơn hàng', '186', '12 đơn chờ xác nhận'],
                ['Sản phẩm', '248', '8 sản phẩm sắp hết hàng'],
                ['Khách hàng', '124', '18 khách hàng mới'],
            ],
            'revenue' => [['5,2', 'Tuần 1', 'one'], ['6,1', 'Tuần 2', 'two'], ['7,8', 'Tuần 3', 'three'], ['9,5', 'Tuần 4', 'four']],
            'alerts' => [
                ['08 sản phẩm sắp hết', 'Kiểm tra và bổ sung tồn kho'],
                ['03 lô hàng gần hết hạn', 'Rà soát hạn dùng trong 30 ngày'],
                ['12 đơn chờ xác nhận', 'Xử lý đơn hàng mới'],
            ],
            'orders' => [
                ['#AT1025', 'Nguyễn Minh Anh', '185.000đ', 'Chờ xác nhận', '25/10/2026 • 09:41'],
                ['#AT1024', 'Trần Hoàng Nam', '248.000đ', 'Đang giao', '25/10/2026 • 09:12'],
                ['#AT1023', 'Lê Thu Hà', '125.000đ', 'Đã giao', '25/10/2026 • 08:56'],
            ],
        ];
    }
}
