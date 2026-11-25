<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class CartQuantityRequest extends FormRequest
{
    public function rules(): array
    {
        return ['quantity' => ['required', 'integer', 'min:1', 'max:'.Product::MAX_CART_QUANTITY]];
    }
}
