<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CatalogFilterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'sort' => ['nullable', Rule::in(['name', 'price_asc', 'price_desc'])],
            'q' => ['nullable', 'string', 'max:100'],
            'on_sale' => ['nullable', 'boolean'],
        ];
    }
}
