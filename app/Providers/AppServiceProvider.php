<?php

namespace App\Providers;

use App\Models\Setting;
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
        // Throttle the token API (Laravel 11+ no longer applies throttle:api by
        // default). Keyed per token-user, falling back to IP for safety.
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)
            ->by($request->user()?->id ?: $request->ip()));

        // Let the admin-configurable display name override APP_NAME for web
        // requests (titles, header). Skipped in the console so artisan commands
        // (migrate, queue, test runner) never depend on the settings table or
        // cache store being reachable at boot.
        if (! $this->app->runningInConsole()) {
            config(['app.name' => Setting::get('app_display_name', config('app.name'))]);
        }
    }
}
