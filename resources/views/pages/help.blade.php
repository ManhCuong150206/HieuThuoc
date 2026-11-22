@extends('layouts.app')
@section('title', 'Hướng dẫn mua hàng')
@section('content')
<div class="site-container page-stack article-body">
    <x-section-title title="Hướng dẫn mua hàng" :level="1" />
    <section class="checkout-panel" id="dat-hang"><h2>Đặt hàng tại An Tâm</h2><p>Đăng ký hoặc đăng nhập, chọn sản phẩm còn hàng và nhấn Thêm vào giỏ. Trong giỏ, cập nhật số lượng rồi chọn Tiến hành đặt hàng. Điền địa chỉ, chọn ưu đãi nếu có và xác nhận.</p><x-button :href="route('products.index')">Xem sản phẩm</x-button></section>
    <section class="checkout-panel" id="van-chuyen"><h2>Vận chuyển và hủy đơn</h2><p>Phí giao hàng hiện tại là 25.000đ cho mỗi đơn, hiển thị trước khi xác nhận. Bạn có thể hủy đơn trong tài khoản trước khi đơn chuyển sang Đang giao. Khi hủy, tồn kho được hoàn lại và thanh toán chuyển sang đã hủy.</p><p>Việc đổi trả sau khi giao cần được nhà thuốc xác nhận trực tiếp.</p></section>
    <section class="checkout-panel" id="thanh-toan"><h2>Thanh toán</h2><p>Hiện hỗ trợ tiền mặt. Khi xác nhận đặt hàng, đơn và thanh toán được tự xác nhận thành công. Thanh toán mã QR đang bổ sung.</p></section>
    <section class="checkout-panel" id="bao-mat"><h2>Thông tin cá nhân</h2><p>Hồ sơ gồm họ tên, email, số điện thoại và địa chỉ. Đơn hàng lưu thông tin người nhận tại thời điểm đặt. Bạn có thể cập nhật hồ sơ và đổi mật khẩu trong tài khoản. Chỉ chủ tài khoản và quản trị viên mới được truy cập thông tin đơn hàng liên quan.</p></section>
</div>
@endsection
