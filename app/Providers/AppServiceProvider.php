<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bindings are intentionally kept in dedicated service classes.
    }

    public function boot(): void
    {
        RateLimiter::for('raffle-entry', function (Request $request): Limit {
            $token = (string) $request->route('token', 'unknown');

            return Limit::perMinute((int) config('raffle.entry_rate_limit', 30))
                ->by($request->ip().'|'.$token);
        });

        RateLimiter::for('admin-api', function (Request $request): Limit {
            return Limit::perMinute((int) config('raffle.admin_rate_limit', 120))
                ->by($request->ip());
        });
    }
}
