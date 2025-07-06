<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    // ログイン画面の表示
    public function showLoginForm()
    {
        return view('auth.pg02_login'); // ビュー名は任意に変更可
    }

    // ログイン処理
    public function login(LoginRequest $request)
    {
        $credentials = $request->only(['email', 'password']);
    
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
        
            \Log::debug('ログイン成功:', [
                'user' => Auth::user(),
                'session_id' => session()->getId(),
                'session_all' => session()->all(),
            ]);
        
            /** @var User $user */
            $user = Auth::user();
        
            if (! $user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }
        
            return redirect()->route('attendance.show');
        }
    
        return back()->withErrors([
            'email' => '認証情報が正しくありません。',
        ]);
    }

    // ログアウト処理
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
