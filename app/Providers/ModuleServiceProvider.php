<?php
// app/Providers/ModuleServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Core\Module\ModuleManager;
use App\Core\Module\ModuleLifecycle;
use App\Core\Module\ModuleInstaller;
use App\Core\Module\ModuleRegistry;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ── Dynamic PSR-4 autoloader for all modules ──────────────────────
        /** @var \Composer\Autoload\ClassLoader $loader */
        $loader = require base_path('vendor/autoload.php');

        foreach (glob(base_path('modules') . '/*', GLOB_ONLYDIR) as $moduleDir) {
            $moduleName = basename($moduleDir);
            $namespacePart = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $moduleName)));
            
            // Map to App\Modules namespace
            $namespace = "App\\Modules\\{$namespacePart}\\";
            if (is_dir($moduleDir)) {
                $loader->addPsr4($namespace, $moduleDir . DIRECTORY_SEPARATOR);
            }
            
            // Also map to Modules namespace (legacy)
            $legacyNamespace = "Modules\\{$namespacePart}\\";
            if (is_dir($moduleDir)) {
                $loader->addPsr4($legacyNamespace, $moduleDir . DIRECTORY_SEPARATOR);
            }
        }

        // Core module services
        $this->app->singleton(ModuleManager::class, fn() => new ModuleManager());
        $this->app->singleton(ModuleLifecycle::class, fn($app) => new ModuleLifecycle($app->make(ModuleManager::class)));
        $this->app->singleton(ModuleInstaller::class, fn($app) => new ModuleInstaller($app->make(ModuleManager::class)));
        $this->app->singleton(ModuleRegistry::class, fn($app) => new ModuleRegistry(
            $app->make(ModuleManager::class),
            $app->make(ModuleLifecycle::class)
        ));
    }

    public function boot(): void
    {
        $modulesPath = base_path('modules');

        foreach (glob($modulesPath . '/*') as $moduleDir) {
            $moduleName = basename($moduleDir);
            $namespacePart = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $moduleName)));
            $providerClass = "App\\Modules\\{$namespacePart}\\Providers\\{$namespacePart}ServiceProvider";

            // 1️⃣ Register module service provider safely
            if (class_exists($providerClass)) {
                $this->app->register($providerClass);
            }

            // 2️⃣ Load routes under web middleware safely
            $routesFile = $moduleDir . '/routes/web.php';
            if (file_exists($routesFile)) {
                Route::middleware('web')->group(function () use ($routesFile) {
                    require $routesFile;
                });
            }

            // 3️⃣ Load views
            $viewsPath = $moduleDir . '/resources/views';
            if (is_dir($viewsPath)) {
                $this->loadViewsFrom($viewsPath, $moduleName);
                // Also register with lowercase for compatibility
                $this->loadViewsFrom($viewsPath, strtolower($moduleName));
            }

            // 4️⃣ Load migrations
            $migrationsPath = $moduleDir . '/database/migrations';
            if (is_dir($migrationsPath)) {
                $this->loadMigrationsFrom($migrationsPath);
            }

            // 5️⃣ Optional: load helper files (procedural functions)
            $helpersFile = $moduleDir . '/helpers.php';
            if (file_exists($helpersFile)) {
                require_once $helpersFile;
            }
        }
    }
}