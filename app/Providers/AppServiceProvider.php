<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    /**
     * Configura los límites de tasa desde config/throttle.php.
     * Puedes ajustar THROTTLE_* en .env si ves "Too Many Requests" (429).
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('login_get', function (Request $request) {
            return Limit::perMinute(config('throttle.login_get', 20))->by($request->ip());
        });

        RateLimiter::for('login_post', function (Request $request) {
            return Limit::perMinute(config('throttle.login_post', 10))->by($request->ip());
        });

        RateLimiter::for('write', function (Request $request) {
            return Limit::perMinute(config('throttle.write', 60))->by($request->user()?->id_cedula ?: $request->ip());
        });

        RateLimiter::for('destroy', function (Request $request) {
            return Limit::perMinute(config('throttle.destroy', 20))->by($request->user()?->id_cedula ?: $request->ip());
        });

        RateLimiter::for('users', function (Request $request) {
            return Limit::perMinute(config('throttle.users', 20))->by($request->ip());
        });

        RateLimiter::for('users_destroy', function (Request $request) {
            return Limit::perMinute(config('throttle.users_destroy', 10))->by($request->ip());
        });
    }
}
