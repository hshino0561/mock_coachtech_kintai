<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use Illuminate\Auth\Events\Registered;
use App\Models\User;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.pg01_register');
    }

    public function register(RegisterRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = User::create([
            'name'     => $request->input('name'),
            'email'    => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        // メール認証リンクを送る（自動送信）
        event(new Registered($user));

        // ログインは行わない
        // auth()->login($user);

        // メール認証画面にリダイレクト
        return redirect()->route('verification.notice');
    }
}
