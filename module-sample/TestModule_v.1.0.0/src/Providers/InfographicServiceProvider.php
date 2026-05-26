<?php

namespace App\Modules\Infographic\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Infographic\Services\InfographicService;

class InfographicServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(InfographicService::class, function () {
            return new InfographicService();
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'infographic');
    }
}