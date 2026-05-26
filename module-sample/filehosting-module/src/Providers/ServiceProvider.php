<?php
// modules/FileHosting/src/Providers/ServiceProvider.php

namespace Modules\FileHosting\Providers;  // ✅ FIXED: Removed 'App\' prefix

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Modules\FileHosting\Services\FileHostingService;  // ✅ Updated namespace
use Modules\FileHosting\Support\ModuleMigrator;       // ✅ Updated namespace

class ServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FileHostingService::class, fn() => new FileHostingService());
    }

    public function boot(): void
    {
        app()->booted(function () {
            logger()->info('🔥 FileHosting ServiceProvider booted');

            $this->registerPermissions();
            $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
            $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'filehosting');
            $this->registerBladeDirectives();

            // Auto-migrate
            ModuleMigrator::run();
        });
    }

    protected function registerPermissions(): void
    {
        $permissions = require __DIR__ . '/../../permissions.php';

        foreach ($permissions['roles'] as $role => $actions) {
            foreach ($actions as $action) {
                Gate::define($action, fn($user) => app('modules')->userCan($action, 'FileHosting'));
            }
        }
    }

    protected function registerBladeDirectives(): void
    {
        Blade::directive('canFileHosting', fn($expression) => "<?php if(app('modules')->userCan({$expression}, 'FileHosting')): ?>");
        Blade::directive('endcanFileHosting', fn() => "<?php endif; ?>");
    }
}