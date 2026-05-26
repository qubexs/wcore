<?php
/**  modules\filehosting\Http\Controllers\SettingController.php  **/
namespace App\Modules\FileHosting\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\FileHosting\Services\SettingService;
use App\Modules\FileHosting\Models\FileStat;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function __construct(protected SettingService $settingService) {}

    /**
     * Show the settings page (admin only).
     */
    public function index(Request $request)
    {

        abort_unless($request->user()->can('filehosting.system.manage_settings'), 403);

        $settings = $this->settingService->all();
        $maxUpload = $this->settingService->maxUploadBytes();


        // Aggregate stats for the settings dashboard
        try {
            $usageStats = [
                'total_uploads'   => FileStat::forAction(FileStat::ACTION_UPLOAD)->count(),
                'total_downloads' => FileStat::forAction(FileStat::ACTION_DOWNLOAD)->count(),
                'recent_activity' => FileStat::recent(7)->count(),
            ];
        } catch (\Exception $e) {
            $usageStats = [
                'total_uploads'   => 0,
                'total_downloads' => 0,
                'recent_activity' => 0,
            ];
        }

        return view('filehosting::settings', compact('settings', 'maxUpload', 'usageStats'));
    }

    /**
     * Return settings as JSON (for Vue/React frontends).
     */
    public function show(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('filehosting.system.manage_settings'), 403);

        return response()->json([
            'settings'   => $this->settingService->all(),
            'max_upload' => $this->settingService->maxUploadBytes(),
        ]);
    }

    /**
     * Update a setting value.
     */
    public function update(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('filehosting.system.manage_settings'), 403);

        $data = $request->validate([
            'key'   => 'required|string',
            'value' => 'required',
        ]);

        $this->settingService->set($data['key'], $data['value']);

        return response()->json(['message' => "Setting '{$data['key']}' updated."]);
    }

    /**
     * Flush the settings cache.
     */
    public function flushCache(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('filehosting.system.manage_settings'), 403);

        $this->settingService->flush();

        return response()->json(['message' => 'Settings cache cleared.']);
    }

    /**
     * Show permissions management page.
     */
    public function permissions(Request $request)
    {
        abort_unless($request->user()->can('filehosting.system.manage_settings'), 403);

        $roles = \App\Models\Role::orderBy('name')->get();
        $departments = \App\Models\Department::orderBy('name')->get();
        
        // Get FileHosting menus
        $menus = \App\Models\Menu::where('module_slug', 'filehosting')
            ->where('is_active', 1)
            ->orderBy('parent_id')
            ->orderBy('order')
            ->get()
            ->groupBy('parent_id');

        // Get current role permissions from permissions.php
        $configPerms = config('filehosting.roles', []);
        
        // Get current department menu access
        $menuDepts = \DB::table('department_menu')
            ->whereIn('menu_id', \App\Models\Menu::where('module_slug', 'filehosting')->pluck('id'))
            ->get()
            ->groupBy('menu_id')
            ->map(fn($items) => $items->pluck('department_id')->toArray())
            ->toArray();

        return view('filehosting::settings.permissions', compact('roles', 'departments', 'menus', 'configPerms', 'menuDepts'));
    }

    /**
     * Update roles permissions from settings page.
     */
    public function updateRoles(Request $request)
    {
        abort_unless($request->user()->can('filehosting.system.manage_settings'), 403);

        $rolePerms = $request->input('role_perms', []);

        if (empty($rolePerms)) {
            return back()->with('error', 'No permissions to update.');
        }

        // Save role permissions to config file
        $configPath = base_path('modules/filehosting/config/filehosting.php');
        $configContent = file_get_contents($configPath);
        
        // Build new roles section
        $newRolesSection = "'roles' => [\n";
        
        foreach ($rolePerms as $roleName => $types) {
            $newRolesSection .= "        '{$roleName}' => [\n";
            foreach ($types as $type => $perms) {
                $newRolesSection .= "            '{$type}' => ['" . implode("', '", $perms) . "'],\n";
            }
            $newRolesSection .= "        ],\n";
        }
        $newRolesSection .= "    ";
        
        // Replace the roles section
        $pattern = "/'roles' => \[[^\]]+\],/s";
        $configContent = preg_replace($pattern, $newRolesSection, $configContent);
        
        file_put_contents($configPath, $configContent);

        // Clear config cache
        \Illuminate\Support\Facades\Artisan::call('config:clear');

        return back()->with('success', 'Role permissions updated successfully!');
    }

    /**
     * Update permissions (separate page).
     */
    public function updatePermissions(Request $request)
    {
        abort_unless($request->user()->can('filehosting.system.manage_settings'), 403);

        $rolePerms = $request->input('role_perms', []);
        $menuDepts = $request->input('menu_departments', []);

        // Save role permissions to config file
        $configPath = base_path('modules/filehosting/config/filehosting.php');
        $configContent = file_get_contents($configPath);
        
        // Find and replace the 'roles' section
        $rolesSection = "'roles' => [";
        $newRolesSection = "'roles' => [\n";
        
        foreach ($rolePerms as $roleName => $types) {
            $newRolesSection .= "        '{$roleName}' => [\n";
            foreach ($types as $type => $perms) {
                $newRolesSection .= "            '{$type}' => ['" . implode("', '", $perms) . "'],\n";
            }
            $newRolesSection .= "        ],\n";
        }
        $newRolesSection .= "    ";
        
        // Replace the roles section
        $pattern = "/'roles' => \[[^\]]+\],/s";
        $configContent = preg_replace($pattern, $newRolesSection, $configContent);
        
        file_put_contents($configPath, $configContent);

        // Clear config cache
        \Illuminate\Support\Facades\Artisan::call('config:clear');

        // Update department_menu table for FileHosting menus (if provided)
        if (!empty($menuDepts)) {
            $fhMenuIds = \App\Models\Menu::where('module_slug', 'filehosting')->pluck('id')->toArray();
            \DB::table('department_menu')->whereIn('menu_id', $fhMenuIds)->delete();

            foreach ($menuDepts as $menuId => $deptIds) {
                foreach ($deptIds as $deptId) {
                    \DB::table('department_menu')->insertOrIgnore([
                        'menu_id' => $menuId,
                        'department_id' => $deptId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        return back()->with('success', 'Permissions updated successfully!');
    }
}
