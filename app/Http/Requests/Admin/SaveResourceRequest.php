<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SaveResourceRequest extends FormRequest
{
    public const PRODUCT_UPDATE_RULES = [
        'category_id' => ['integer', 'exists:categories,id'],
        'price' => ['integer', 'min:0', 'max:1000000000'],
        'quantity' => ['integer', 'between:0,1000000'],
        'is_active' => ['boolean'],
    ];

    public function rules(): array
    {
        return match ($this->route('resource')) {
            'products' => [
                ...array_map(fn ($rules) => ['required', ...$rules], self::PRODUCT_UPDATE_RULES),
                'name' => ['required', 'string', 'max:200'],
                'brand' => ['nullable', 'string', 'max:100'], 'composition' => ['nullable', 'string', 'max:500'],
                'used_for' => ['nullable', 'string', 'max:500'], 'how_to_use' => ['nullable', 'string', 'max:500'],
                'contraindications' => ['nullable', 'string', 'max:500'], 'side_effects' => ['nullable', 'string', 'max:500'],
                'image_url' => ['nullable', 'url:http,https', 'max:500'],
                'image' => ['bail', 'nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
                'expiry_date' => ['nullable', 'date_format:Y-m-d'],
            ],
            'categories' => ['name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($this->route('record') === null ? null : (int) $this->route('record'))]],
            'promotions' => [
                'name' => ['required', 'string', 'max:150'], 'description' => ['nullable', 'string', 'max:500'],
                'discount_percent' => ['required', 'integer', 'between:0,100'],
                'starts_at' => ['required', 'date_format:Y-m-d'], 'ends_at' => ['required', 'date_format:Y-m-d', 'after_or_equal:starts_at'],
                'is_active' => ['required', 'boolean'],
                'applies_to_all_products' => ['sometimes', 'required', 'boolean'],
                'product_ids' => [
                    Rule::excludeIf(! in_array($this->input('applies_to_all_products'), [false, 0, '0'], true)),
                    'required', 'string', 'max:200000', 'json',
                ],
            ],
            'posts' => [
                'title' => ['required', 'string', 'max:200'], 'content' => ['required', 'string', 'max:500'],
                'cover_url' => ['nullable', 'string', 'max:500', Rule::when(
                    ! in_array($this->input('cover_url'), array_keys(config('health_articles')), true),
                    'url:http,https',
                )],
                'status' => ['required', Rule::in(['DRAFT', 'PUBLISHED'])],
                'published_at' => ['required_if:status,PUBLISHED', 'nullable', 'date_format:Y-m-d'],
            ],
        };
    }

    public function messages(): array
    {
        return [
            'image.uploaded' => 'Tải ảnh lên thất bại. Vui lòng chọn lại ảnh.',
            'image.image' => 'Tệp đã chọn phải là ảnh.',
            'image.mimes' => 'Chỉ hỗ trợ ảnh JPG, PNG hoặc WebP.',
            'image.max' => 'Ảnh sản phẩm không được vượt quá 5 MB.',
        ];
    }

    public function forProductCreation(ValidationException $exception): ValidationException
    {
        return $exception->errorBag('productCreate')->redirectTo(route('admin.resources.index', [
            'resource' => 'products', 'q' => $this->query('q'), 'page' => $this->query('page'),
        ]));
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($this->route('resource') === 'products' && $this->isMethod('POST')) {
            throw $this->forProductCreation(new ValidationException($validator));
        }

        parent::failedValidation($validator);
    }
}
