<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaceOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'recipient_name' => ['required', 'string', 'max:150'],
            'recipient_phone' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9][0-9 .()-]{8,18}[0-9]$/'],
            'shipping_address' => ['required', 'string', 'max:255'],
            'payment_method' => ['required', Rule::in(['COD'])],
            'promotion_id' => ['nullable', 'integer'],
            'checkout_token' => ['required', 'string', 'size:48'],
            'terms' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.in' => 'Thanh toán mã QR đang bổ sung. Vui lòng chọn tiền mặt.',
            'recipient_phone.regex' => 'Số điện thoại không hợp lệ.',
            'terms.accepted' => 'Vui lòng đồng ý với điều khoản mua hàng.',
        ];
    }
}
