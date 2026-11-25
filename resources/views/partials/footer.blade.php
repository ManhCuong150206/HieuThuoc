<footer class="site-footer" id="lien-he">
    <div class="site-container wrap">
        <div class="footer-directory">
            <div class="footer-columns">
                <div class="footer-about">
                    <p class="footer-name">An Tâm Pharmacy</p>
                    <p>Nhà thuốc trực tuyến: thuốc không kê đơn, vitamin và đồ chăm sóc cá nhân, giao tận nhà.</p>
                </div>
                <div class="footer-links">
                    <div class="footer-col">
                        <h3>Tài khoản</h3>
                        <ul>
                            <li><a href="{{ route('account.index') }}">Thông tin cá nhân</a></li>
                            <li><a href="{{ route('account.orders') }}">Đơn hàng của tôi</a></li>
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
        </div>
    </div>
    <div class="footer-bottom">
        <div class="site-container footer-legal">
            <p>© {{ now()->year }} An Tâm Pharmacy</p>

        </div>
    </div>
</footer>
