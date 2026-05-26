<?php
namespace App\Modules\FileHosting\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\FileHosting\Services\FileService;
use App\Modules\FileHosting\Services\FolderService;
use App\Modules\FileHosting\Services\VersionService;
use App\Modules\FileHosting\Services\ThumbnailService;
use App\Modules\FileHosting\Services\SettingService;
use Illuminate\Support\Facades\Gate;

class FileHostingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ── Merge module config safely
        $configPath = __DIR__ . '/../../config/filehosting.php';
        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, 'filehosting');
        }

        // ── Bind services
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
        // Define config path locally in boot() scope
        $configPath = __DIR__ . '/../../config/filehosting.php';

        // Load routes safely
        $routesPath = __DIR__ . '/../../routes/web.php';
        if (file_exists($routesPath)) {
            $this->loadRoutesFrom($routesPath);
        }

        // Load Blade views
        $viewsPath = __DIR__ . '/../../resources/views';
        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, 'filehosting');
        }

        // Publish config & views safely
        if (file_exists($configPath)) {
            $this->publishes([$configPath => config_path('filehosting.php')], 'filehosting-config');
        }

        if (is_dir($viewsPath)) {
            $this->publishes([$viewsPath => resource_path('views/vendor/filehosting')], 'filehosting-views');
        }

        // Register permissions with Gate
        $this->registerPermissions();
    }

    protected function registerPermissions(): void
    {
        $permissions = config('filehosting.roles', []);

        Gate::before(function ($user, $ability) use ($permissions) {
            $role = method_exists($user, 'getRoleNames') 
                ? $user->getRoleNames()->first() 
                : ($user->role ?? 'Employee');

            if ($role === 'SuperAdmin') return true;

            foreach ($permissions[$role] ?? [] as $type => $perms) {
                foreach ($perms as $perm) {
                    if ($ability === "filehosting.{$perm}") return true;
                }
            }

            return null;
        });
    }
}