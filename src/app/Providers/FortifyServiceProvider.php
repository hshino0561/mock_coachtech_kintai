<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUserWithRequest;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\VerifyEmailViewResponse as VerifyEmailViewResponseContract;
use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;

use App\Http\Responses\RegisterResponse;
use App\Http\Responses\VerifyEmailViewResponse;
use App\Http\Responses\VerifiedEmailResponse;
use App\Http\Responses\LoginResponse;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 会員登録後のリダイレクト制御（認証画面へ）
        $this->app->singleton(RegisterResponseContract::class, RegisterResponse::class);
        $this->app->singleton(VerifyEmailViewResponseContract::class, VerifyEmailViewResponse::class);
        $this->app->singleton(VerifyEmailResponseContract::class, VerifiedEmailResponse::class);
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 会員登録画面のBlade
        Fortify::registerView(function () {
            return view('auth.pg01_register');
        });

        // メール認証画面のBlade
        Fortify::verifyEmailView(function () {
            return view('auth.verify-email');
        });

        // ログイン画面のBlade
        Fortify::loginView(function () {
            return view('auth.pg02_login');
        });

        Fortify::createUsersUsing(CreateNewUserWithRequest::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        // メール認証完了後のリダイレクト先を設定
        // Fortify::verifyEmailRedirectTo(function (Request $request) {
        //     return route('attendance.show');
        // });
    }
}
