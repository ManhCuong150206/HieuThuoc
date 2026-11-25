<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class SelectCheckoutItemsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'integer', 'min:1', 'distinct'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Vui lòng chọn ít nhất một sản phẩm để đặt hàng.',
            'items.*.integer' => 'Sản phẩm đã chọn không hợp lệ.',
            'items.*.distinct' => 'Mỗi sản phẩm chỉ được chọn một lần.',
        ];
    }
}
