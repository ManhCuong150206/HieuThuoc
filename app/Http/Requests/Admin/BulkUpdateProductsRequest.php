<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkUpdateProductsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'page' => ['required', 'integer', 'min:1'],
            'scope' => ['required', Rule::in(['selected', 'all'])],
            'selected_count' => ['required', 'integer', 'min:1'],
            'ids' => ['required', 'string', 'max:200000', 'json'],
            ...array_map(fn ($rules) => ['nullable', ...$rules], SaveResourceRequest::PRODUCT_UPDATE_RULES),
        ];
    }
}
