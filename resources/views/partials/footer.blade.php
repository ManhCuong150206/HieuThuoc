<footer class="site-footer @hasSection('closing')
closing
@endif" id="lien-he">
    <div class="site-container wrap">
        @hasSection('closing')
            <div class="footer-close on-dark">@yield('closing')</div>
        @endif
        <div class="footer-directory">
            <div class="footer-columns">
                <div class="footer-about">
                    <p class="footer-name">An Tâm Pharmacy</p>
                    <p>Nhà thuốc trực tuyến: thuốc không kê đơn, vitamin và đồ chăm sóc cá nhân, giao tận nhà.</p>
                </div>
                <div class="footer-links">
                    <div class="footer-col">
                        <h3>Hỗ trợ khách hàng</h3>
                        <ul>
                            <li><a href="{{ route('help') }}#dat-hang">Hướng dẫn đặt hàng</a></li>
                            <li><a href="{{ route('help') }}#van-chuyen">Vận chuyển &amp; đổi trả</a></li>
                            <li><a href="{{ route('help') }}#bao-mat">Thông tin cá nhân</a></li>
                        </ul>
                    </div>
                    <div class="footer-col">
                        <h3>Tài khoản</h3>
                        <ul>
                            <li><a href="{{ route('account.index') }}#don-hang">Đơn hàng của tôi</a></li>
                            <li><a href="{{ route('account.index') }}#profile-address">Địa chỉ giao hàng</a></li>
                            <li><a href="{{ route('favorites.index') }}">Sản phẩm yêu thích</a></li>
                        </ul>
                    </div>
                    <div class="footer-col">
                        <h3>Về An Tâm</h3>
                        {{-- Giới thiệu, Liên hệ và Thông tin nhà thuốc chưa có trang riêng nên chưa gắn link. --}}
                        <ul>
                            <li class="footer-plain">Giới thiệu</li>
                            <li class="footer-plain">Liên hệ</li>
                            <li class="footer-plain">Thông tin nhà thuốc</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="footer-legal">
                <p>© {{ now()->year }} An Tâm Pharmacy</p>
                <p>Địa chỉ, hotline và giấy phép: cập nhật khi triển khai</p>
            </div>
        </div>
    </div>
</footer>
