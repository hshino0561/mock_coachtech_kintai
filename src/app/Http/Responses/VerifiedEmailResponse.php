<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifiedEmailResponseContract;
use Illuminate\Support\Facades\Auth;

class VerifiedEmailResponse implements VerifiedEmailResponseContract
{
    public function toResponse($request)
    {
        // return redirect()->route('verification.notice');
        Auth::logout(); // 認証完了後にログアウト
        return redirect('/email/verify'); // ログイン画面に遷移
    }
}
