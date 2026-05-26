<?php
// app/Core/Module/ModuleMenuInstaller.php
namespace App\Core\Module;

use App\Models\Menu;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\File;

class ModuleMenuInstaller
{
    public function install(string $modulePath, string $moduleSlug): void
    {
        $menuFile = $modulePath . '/config/menu.json';

        if (!File::exists($menuFile)) {
            return;
        }

        $menus = json_decode(File::get($menuFile), true);

        foreach ($menus as $menu) {
            $parent = Menu::updateOrCreate(
                ['route' => $menu['route']],
                [
                    'title'       => $menu['title'],
                    'icon'        => $menu['icon'] ?? 'fas fa-circle',
                    'permission'  => $menu['permission'] ?? null,
                    'order'       => $menu['order'] ?? 0,
                    'parent_id'   => null,
                    'is_active'   => 1,
                    'is_locked'   => 0,
                    'module_slug' => $moduleSlug,
                ]
            );

            // Assign permission to all superadmins by default
            if (!empty($menu['permission'])) {
                $perm = Permission::firstOrCreate([
                    'name'       => $menu['permission'],
                    'guard_name' => 'web',
                ]);

                // Give permission to SuperAdmin role automatically
                $superAdmin = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'SuperAdmin']);
                if (!$superAdmin->hasPermissionTo($perm)) {
                    $superAdmin->givePermissionTo($perm);
                }
            }

            foreach ($menu['children'] ?? [] as $child) {
                $childMenu = Menu::updateOrCreate(
                    ['route' => $child['route']],
                    [
                        'title'       => $child['title'],
                        'icon'        => $child['icon'] ?? 'fas fa-dot-circle',
                        'permission'  => $child['permission'] ?? null,
                        'order'       => $child['order'] ?? 0,
                        'parent_id'   => $parent->id,
                        'is_active'   => 1,
                        'is_locked'   => 0,
                        'module_slug' => $moduleSlug,
                    ]
                );

                if (!empty($child['permission'])) {
                    $perm = Permission::firstOrCreate([
                        'name'       => $child['permission'],
                        'guard_name' => 'web',
                    ]);
                    $superAdmin = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'SuperAdmin']);
                    if (!$superAdmin->hasPermissionTo($perm)) {
                        $superAdmin->givePermissionTo($perm);
                    }
                }
            }
        }
    }

    public function uninstall(string $moduleSlug): void
    {
        Menu::where('module_slug', $moduleSlug)->delete();
    }
}
