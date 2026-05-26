<?php
// modules/FileHosting/src/Providers/ServiceProvider.php

namespace App\Modules\FileHosting\Providers;  // ✅ FIXED: Removed 'App\' prefix

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use App\Modules\FileHosting\Services\FileHostingService;  // ✅ Updated namespace
use App\Modules\FileHosting\Support\ModuleMigrator;       // ✅ Updated namespace

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
        $config = config('filehosting.roles', []);
        $registered = [];
        
        foreach ($config as $role => $actions) {
            if (is_array($actions)) {
                foreach ($actions as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $perm) {
                            $fullPerm = "filehosting.{$key}.{$perm}";
                            if (!in_array($fullPerm, $registered)) {
                                $registered[] = $fullPerm;
                                Gate::define($fullPerm, fn($user) => app('modules')->userCan($fullPerm, 'FileHosting'));
                            }
                        }
                    } else {
                        if (!in_array($value, $registered)) {
                            $registered[] = $value;
                            Gate::define($value, fn($user) => app('modules')->userCan($value, 'FileHosting'));
                        }
                    }
                }
            }
        }
    }

    protected function registerBladeDirectives(): void
    {
        Blade::directive('canFileHosting', fn($expression) => "<?php if(app('modules')->userCan({$expression}, 'FileHosting')): ?>");
        Blade::directive('endcanFileHosting', fn() => "<?php endif; ?>");
    }
}