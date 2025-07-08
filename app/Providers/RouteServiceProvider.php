<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;


class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/dashboard';

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $key = $request->email ?: $request->ip();
            return Limit::perMinute(5)->by($key)->response(function() {
                return redirect()->route('login')
                    ->with('error', 'Too many login attempts. Please try again later.');
            });
        });
    }
}
