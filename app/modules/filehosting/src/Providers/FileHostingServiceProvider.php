<?php

// module  -\src\Providers\FileHostingServiceProvider.php
namespace App\Modules\FileHosting\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Modules\FileHosting\Services\FileHostingService;

class FileHostingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Singleton service for file hosting operations
        $this->app->singleton(FileHostingService::class, fn() => new FileHostingService());
    }

    public function boot(): void
    {
        // Load module routes
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');

        // Load module views (use 'filehosting::viewname' in Blade)
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'filehosting');

        // Register module RBAC (roles and permissions)
        $this->registerPermissions();

        // Optional: custom Blade directives for module permissions
        $this->registerBladeDirectives();
    }

    protected function registerPermissions(): void
    {
        // Load module permissions
        $permissions = require __DIR__ . '/../../permissions.php';

        foreach ($permissions['roles'] as $role => $actions) {
            foreach ($actions as $action) {
                // Save module-level permissions in ModuleRegistry
                // This assumes your ModuleRegistry supports per-module permissions
                \App\Core\Module\ModuleRegistry::setPermission($role, $action, 'FileHosting');
            }
        }
    }

    protected function registerBladeDirectives(): void
    {
        // Example Blade directive for checking module permissions
        Blade::directive('canFileHosting', function ($expression) {
            return "<?php if(app('modules')->userCan({$expression}, 'FileHosting')): ?>";
        });

        Blade::directive('endcanFileHosting', function () {
            return "<?php endif; ?>";
        });
    }
}
