<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login' => 'required|string',
            'password' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'login.required' => 'メールアドレスを入力してください',
            'password.required' => 'パスワードを入力してください',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // バリデーションは通っている前提で、ここで認証を試す
            $login = $this->input('login');
            $password = $this->input('password');

            $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

            if (!Auth::attempt([$field => $login, 'password' => $password])) {
                $validator->errors()->add('login', 'ログイン情報が登録されていません');
            }
        });
    }
}
