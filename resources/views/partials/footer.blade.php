<footer class="site-footer">
    <div class="site-container">
        <div class="footer-columns">
            <div class="footer-about">
                <a class="footer-logo" href="{{ route('home') }}" aria-label="An Tâm Pharmacy — Trang chủ">
                    <img src="{{ asset('images/logo.png') }}" alt="An Tâm Pharmacy — Nhà thuốc trực tuyến">
                </a>
                <p>Đồng hành cùng sức khỏe của bạn và gia đình.</p>
                <p class="footer-note">Địa chỉ • Hotline • Giấy phép: cập nhật khi triển khai</p>
            </div>
            <ul aria-label="Về An Tâm">
                <li>Giới thiệu</li>
                <li>Liên hệ</li>
                <li>Thông tin nhà thuốc</li>
            </ul>
            <div>
                <h2>Hỗ trợ khách hàng</h2>
                <ul>
                    <li>Hướng dẫn đặt hàng</li>
                    <li>Vận chuyển &amp; đổi trả</li>
                    <li>Chính sách bảo mật</li>
                </ul>
            </div>
            <div>
                <h2>Tài khoản</h2>
                <ul>
                    <li><a href="{{ route('account.index') }}#don-hang">Đơn hàng của tôi</a></li>
                    <li>Sổ địa chỉ</li>
                    <li>Sản phẩm yêu thích</li>
                </ul>
            </div>
        </div>
        <p class="footer-note">© 2026 An Tâm • Bản thiết kế minh họa cho đồ án Nhóm 10</p>
    </div>
</footer>
