<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\Settings\SettingService::class);
    }

    public function boot(): void
    {
        // Use Bootstrap 4 pagination (consistent with AdminLTE 3)
        \Illuminate\Pagination\Paginator::useBootstrap();
    }
}
