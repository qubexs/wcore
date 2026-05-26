<?php

namespace App\Modules\WebsiteMonitor\Providers;

use Illuminate\Support\ServiceProvider;

class WebsiteMonitorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'websitemonitor');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
