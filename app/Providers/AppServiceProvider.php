<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Use Bootstrap for pagination
        Paginator::useBootstrap();

        // ---------------------------
        // 1️⃣ Load static roles
        // ---------------------------
        $roles = config('permission.roles');

        // ---------------------------
        // 2️⃣ Load module permissions from all installed modules
        // ---------------------------
        $modulesPath = base_path('module-sample');
        foreach (glob($modulesPath.'/*', GLOB_ONLYDIR) as $moduleDir) {
            $permFile = $moduleDir.'/config/permissions.json';
            if (file_exists($permFile)) {
                $modulePermissions = json_decode(file_get_contents($permFile), true);
                foreach ($modulePermissions as $module => $perms) {
                    // Merge permissions into SUPERADMIN by default
                    if (!isset($roles['superadmin'])) {
                        $roles['superadmin'] = [];
                    }
                    $roles['superadmin'] = array_merge($roles['superadmin'], $perms);
                }
            }
        }

        // ---------------------------
        // 3️⃣ Store merged roles in container
        // ---------------------------
        app()->singleton('rbac.roles', fn() => $roles);

        // ---------------------------
        // 4️⃣ Share settings with all views
        // ---------------------------
        try {
            $settings = \App\Models\Setting::where('is_active', 1)
                ->whereNull('deleted_at')
                ->pluck('value', 'key')
                ->toArray();
        } catch (\Throwable $e) {
            $settings = [];
        }

        View::share('settings', $settings);

        // ---------------------------
        // 5️⃣ Override mail config from DB settings
        // ---------------------------
        $mailKeys = ['mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption', 'mail_from_address', 'mail_from_name'];
        if (!empty($settings) && isset($settings['mail_host']) && $settings['mail_host']) {
            config([
                'mail.mailers.smtp.host'       => $settings['mail_host'],
                'mail.mailers.smtp.port'       => $settings['mail_port'] ?? '587',
                'mail.mailers.smtp.username'   => $settings['mail_username'] ?? '',
                'mail.mailers.smtp.password'   => $settings['mail_password'] ?? '',
                'mail.mailers.smtp.encryption' => $settings['mail_encryption'] ?? 'tls',
                'mail.from.address'            => $settings['mail_from_address'] ?? env('MAIL_FROM_ADDRESS'),
                'mail.from.name'               => $settings['mail_from_name'] ?? env('MAIL_FROM_NAME'),
            ]);
        }
    }
}
