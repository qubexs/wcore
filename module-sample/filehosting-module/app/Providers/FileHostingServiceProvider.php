<?php

namespace App\Modules\FileHosting\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\FileHosting\Services\FileService;
use App\Modules\FileHosting\Services\FolderService;
use App\Modules\FileHosting\Services\VersionService;
use App\Modules\FileHosting\Services\ThumbnailService;
use App\Modules\FileHosting\Services\SettingService;

class FileHostingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge module config
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/filehosting.php',
            'filehosting'
        );

        // Bind services (ThumbnailService & VersionService injected into FileService)
        $this->app->singleton(ThumbnailService::class);
        $this->app->singleton(VersionService::class);
        $this->app->singleton(SettingService::class);
        $this->app->singleton(FolderService::class);

        $this->app->singleton(FileService::class, function ($app) {
            return new FileService(
                $app->make(ThumbnailService::class),
                $app->make(VersionService::class)
            );
        });
    }

    public function boot(): void
    {
        // Register routes
        $this->loadRoutesFrom(__DIR__ . '/../../route/web.php');

        // Register Blade views (namespace: filehosting)
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'filehosting');

        // Publish config
        $this->publishes([
            __DIR__ . '/../../config/filehosting.php' => config_path('filehosting.php'),
        ], 'filehosting-config');

        // Publish views
        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/filehosting'),
        ], 'filehosting-views');

        // Register permissions with Laravel Gate (if using Spatie or custom gate)
        $this->registerPermissions();
    }

    protected function registerPermissions(): void
    {
        $permissions = config('filehosting.roles', []);

        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) use ($permissions) {
            $role = method_exists($user, 'getRoleNames')
                ? $user->getRoleNames()->first()
                : ($user->role ?? 'Employee');

            if ($role === 'SuperAdmin') return true;

            // Flatten permission string e.g. "filehosting.view_logs"
            foreach ($permissions[$role] ?? [] as $type => $perms) {
                foreach ($perms as $perm) {
                    if ($ability === "filehosting.{$perm}") return true;
                }
            }

            return null; // Fall through to next gate check
        });
    }
}
