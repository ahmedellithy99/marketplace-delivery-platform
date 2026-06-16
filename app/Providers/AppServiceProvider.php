<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
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
        $this->configureWhatsAppRateLimiting();
    }

    protected function configureWhatsAppRateLimiting(): void
    {
        RateLimiter::for('whatsapp', function () {
            return [
                Limit::perMinute(config('services.waclient.rate_limit_per_minute', 20)),
                Limit::perHour(config('services.waclient.rate_limit_per_hour', 300)),
            ];
        });
    }
}
