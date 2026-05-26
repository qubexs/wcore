<?php
// modules/FileHosting/src/Providers/FileHostingServiceProvider.php

namespace App\Modules\FileHosting\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use App\Modules\FileHosting\Services\FileHostingService;

class FileHostingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register singleton service for file operations
        $this->app->singleton(FileHostingService::class, fn() => new FileHostingService());
    }

    public function boot(): void
    {
        // 1️⃣ Register permissions (Gates) early
        $this->registerPermissions();

        // 2️⃣ Load routes AFTER gates are defined
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');

        // 3️⃣ Load views
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'filehosting');

        // 4️⃣ Optional: Blade directives
        $this->registerBladeDirectives();
    }

    protected function registerPermissions(): void
    {
        $permissions = require __DIR__ . '/../../permissions.php';

        foreach ($permissions['roles'] as $role => $actions) {
            foreach ($actions as $action) {
                Gate::define($action, function ($user) use ($action) {
                    return app('modules')->userCan($action, 'FileHosting');
                });
            }
        }
    }

    protected function registerBladeDirectives(): void
    {
        Blade::directive('canFileHosting', function ($expression) {
            return "<?php if(app('modules')->userCan({$expression}, 'FileHosting')): ?>";
        });

        Blade::directive('endcanFileHosting', fn() => "<?php endif; ?>");
    }
}
