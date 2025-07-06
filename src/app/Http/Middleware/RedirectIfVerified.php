<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class RedirectIfVerified
{
    public function handle(Request $request, Closure $next)
    {
        if (
            $request->user() &&
            $request->user()->hasVerifiedEmail()
        ) {
            // 認証済みならそのまま処理を進める（リダイレクトしない）
            return $next($request);
        }
    
        // 未認証ならメール認証画面へ
        return redirect()->route('verification.notice');
    }    
}
