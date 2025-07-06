<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Closure;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            \Log::debug('Auth redirect triggered. Route:', [
                'name' => optional($request->route())->getName(),
                'uri'  => optional($request->route())->uri(),
                'user' => Auth::user(),
                'admin' => Auth::guard('admin')->user(),
            ]);
    
            if ($request->route() && Str::startsWith($request->route()->getName(), 'admin.')) {
                return route('admin.login');
            }
            return route('login');
        }
    }
}
