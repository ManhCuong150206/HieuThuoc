<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_.-]+$/', 'unique:accounts,username'],
            'email' => ['required', 'email', 'max:254', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9][0-9 .()-]{8,18}[0-9]$/'],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Vui lòng nhập :attribute.',
            'max' => ':attribute không được vượt quá :max ký tự.',
            'email.email' => 'Địa chỉ email không hợp lệ.',
            'email.unique' => 'Email này đã được đăng ký.',
            'username.unique' => 'Tên đăng nhập này đã được sử dụng.',
            'username.regex' => 'Tên đăng nhập chỉ gồm chữ không dấu, số, dấu chấm, gạch dưới hoặc gạch ngang.',
            'phone.regex' => 'Số điện thoại không hợp lệ.',
            'password.min' => 'Mật khẩu cần ít nhất 8 ký tự.',
            'password.confirmed' => 'Mật khẩu nhập lại không khớp.',
        ];
    }

    public function attributes(): array
    {
        return [
            'full_name' => 'họ và tên',
            'username' => 'tên đăng nhập',
            'email' => 'email',
            'phone' => 'số điện thoại',
            'password' => 'mật khẩu',
            'password_confirmation' => 'mật khẩu xác nhận',
        ];
    }
}
