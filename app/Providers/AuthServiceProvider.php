<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\File;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        $this->registerPolicies();

        /*
        |--------------------------------------------------------------------------
        | SuperAdmin bypass (GLOBAL ACCESS)
        |--------------------------------------------------------------------------
        */
        Gate::before(function ($user, $ability) {
            if (method_exists($user, 'hasRole') && ($user->hasRole('superadmin') || $user->hasRole('SuperAdmin'))) {
                return true;
            }
            return null;
        });

        $modulesPath = base_path('modules');

        if (!is_dir($modulesPath)) {
            return;
        }

        foreach (File::directories($modulesPath) as $moduleDir) {

            $permissionsFile = $moduleDir . '/permissions.php';

            if (!file_exists($permissionsFile)) {
                continue;
            }

            $permissions = include $permissionsFile;

            if (!isset($permissions['roles']) || !is_array($permissions['roles'])) {
                continue;
            }

            $module = basename($moduleDir);

            foreach ($permissions['roles'] as $role => $typeGroups) {
                // typeGroups = ['folder' => [...], 'file' => [...], 'system' => [...]]
                foreach ($typeGroups as $type => $actions) {

                    if (!is_array($actions)) {
                        continue;
                    }

                    foreach ($actions as $action) {

                        // Namespaced: e.g. "filehosting.folder.delete"
                        // Avoids collisions when the same verb appears in multiple types
                        $permission = "{$module}.{$type}.{$action}";

                        // Avoid redefining same gate
                        if (Gate::has($permission)) {
                            continue;
                        }

                        Gate::define($permission, function ($user) use ($role) {
                            return $user->hasRole($role);
                        });
                    }
                }
            }
        }
    }
}