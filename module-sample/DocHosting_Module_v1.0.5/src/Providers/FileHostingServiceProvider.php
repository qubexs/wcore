<?php
// modules/FileHosting/src/Providers/FileHostingServiceProvider.php

namespace App\Modules\FileHosting\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use App\Modules\FileHosting\Services\FileHostingService;
use App\Modules\FileHosting\Support\ModuleMigrator; // ✅ THIS WAS MISSING


class FileHostingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind the module service
        $this->app->singleton(FileHostingService::class, fn() => new FileHostingService());
    }

 public function boot(): void
    {
        // Defer EVERYTHING until app + DB are ready
        app()->booted(function () {

            logger()->info('🔥 FileHostingServiceProvider booted (deferred)');

            // 1️⃣ Register permissions
            $this->registerPermissions();

            // 2️⃣ Load routes
            $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');

            // 3️⃣ Load views
            $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'filehosting');

            // 4️⃣ Blade directives
            $this->registerBladeDirectives();

            // 🔥 5️⃣ AUTO MIGRATE (NOW DB IS GUARANTEED READY)
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
