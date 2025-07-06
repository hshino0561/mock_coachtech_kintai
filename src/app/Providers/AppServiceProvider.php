<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // セッションに関する判定はここでは行わない
    }

    public function boot()
    {
        // セッションに関する判定はここでは行わない
    }
}
