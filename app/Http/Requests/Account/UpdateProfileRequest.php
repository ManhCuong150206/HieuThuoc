<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:254', Rule::unique('users', 'email')->ignore($this->user()->profile?->id)],
            'phone' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9][0-9 .()-]{8,18}[0-9]$/'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Vui lòng nhập :attribute.',
            'max' => ':attribute không được vượt quá :max ký tự.',
            'email.email' => 'Địa chỉ email không hợp lệ.',
            'email.unique' => 'Email này đã được tài khoản khác sử dụng.',
            'phone.regex' => 'Số điện thoại không hợp lệ.',
        ];
    }

    public function attributes(): array
    {
        return [
            'full_name' => 'họ và tên',
            'email' => 'email',
            'phone' => 'số điện thoại',
            'address' => 'địa chỉ',
        ];
    }
}
