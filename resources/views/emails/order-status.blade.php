<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $mailSubject }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #172626; line-height: 1.5;">
    <main style="max-width: 620px; margin: 0 auto; padding: 16px;">
        <h1 style="font-size: 24px;">{{ $mailSubject }}</h1>
        <p>Xin chào {{ $order->user?->full_name ?: $order->recipient_name }},</p>
        <p>{{ $statusMessage }}</p>

        <table border="1" cellpadding="8" cellspacing="0" style="width: 100%; border-collapse: collapse; border-color: #d3dedc;">
            <caption style="text-align: left; font-weight: bold; margin-bottom: 8px;">Sản phẩm trong đơn hàng</caption>
            <thead style="background-color: #edf5f3; text-align: left;">
                <tr>
                    <th scope="col">Tên sản phẩm</th>
                    <th scope="col">Số lượng</th>
                    <th scope="col">Đơn giá</th>
                    <th scope="col">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $item->product_name ?: $item->product?->name }}</td>
                        <td style="text-align: center;">{{ $item->quantity }}</td>
                        <td style="text-align: right; white-space: nowrap;">{{ number_format($item->unit_price, 0, ',', '.') }}đ</td>
                        <td style="text-align: right; white-space: nowrap;">{{ number_format($item->unit_price * $item->quantity, 0, ',', '.') }}đ</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p><strong>Tổng tiền:</strong> {{ number_format($order->total_amount, 0, ',', '.') }}đ</p>
        <p>
            <strong>Người nhận:</strong> {{ $order->recipient_name }}<br>
            <strong>Địa chỉ nhận hàng:</strong> {{ $order->shipping_address }}
            @if (filled($order->recipient_phone))
                <br><strong>Số điện thoại:</strong> {{ $order->recipient_phone }}
            @endif
        </p>
        <p>Trân trọng,<br>An Tâm Pharmacy</p>
    </main>
</body>
</html>
