<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Admin\AdminLoginRequest;

class AdminLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.pg07_admin_login');
    }

    public function login(AdminLoginRequest $request)
    {
        $login = $request->input('login');
        $password = $request->input('password');
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
    
        $credentials = [
            $field => $login,
            'password' => $password,
        ];
    
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    
        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/admin/attendance/list');
        }
    
        return redirect()->route('admin.login');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    
        return redirect('/admin/login'); // 管理者用ログイン画面へリダイレクト
    }    
}
