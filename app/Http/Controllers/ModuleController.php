<?php
// app/Http/Controllers/ModuleController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Core\Module\ModuleRegistry;
use App\Core\Module\ModuleLifecycle;
use App\Core\Module\ModuleInstaller;
use App\Core\Module\ModuleMenuInstaller;
use App\Models\Module;
use App\Models\Menu;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ModuleController extends Controller
{
    protected ModuleRegistry $registry;
    protected ModuleLifecycle $lifecycle;
    protected ModuleInstaller $installer;
    protected ModuleMenuInstaller $menuInstaller;

    public function __construct(
        ModuleRegistry $registry,
        ModuleLifecycle $lifecycle,
        ModuleInstaller $installer,
        ModuleMenuInstaller $menuInstaller
    ) {
        $this->registry       = $registry;
        $this->lifecycle      = $lifecycle;
        $this->installer      = $installer;
        $this->menuInstaller  = $menuInstaller;
    }

    /**
     * 1️⃣ LIST ALL MODULES
     */
    public function index()
    {
        return view('modules.index', [
            'registry' => $this->registry,
            'modules'  => Module::all(),
        ]);
    }

    /**
     * 2️⃣ INSTALL MODULE FROM ZIP
     * Extracts ZIP, saves module to app/modules, updates DB safely
     *
     * ✅ FIX: Returns JSON response for AJAX requests
     */
    public function install(Request $request)
    {
        $request->validate([
            'module_zip' => 'required|file|mimes:zip|max:51200', // max 50MB
        ]);

        if (!class_exists(\ZipArchive::class)) {
            // ✅ FIX: Return JSON for AJAX, redirect for regular requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'PHP ZipArchive extension is not enabled.'
                ], 500);
            }
            return back()->with('error', 'PHP ZipArchive extension is not enabled.');
        }

        $zipFile  = $request->file('module_zip');
        $filename = pathinfo($zipFile->getClientOriginalName(), PATHINFO_FILENAME);

        $zipDir  = storage_path('modules/zips');
        $tempDir = storage_path('modules/temp');

        \Illuminate\Support\Facades\File::ensureDirectoryExists($zipDir);
        \Illuminate\Support\Facades\File::deleteDirectory($tempDir);
        \Illuminate\Support\Facades\File::ensureDirectoryExists($tempDir);

        $zipPath = $zipDir.'/'.$zipFile->getClientOriginalName();
        $zipFile->move($zipDir, $zipFile->getClientOriginalName());

        $zip = new \ZipArchive;
        if ($zip->open($zipPath) !== true) {
            \Illuminate\Support\Facades\File::deleteDirectory($tempDir);

            // ✅ FIX: Return JSON for AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to open ZIP file.'
                ], 400);
            }
            return back()->with('error', 'Unable to open ZIP file.');
        }

        if (!$zip->extractTo($tempDir)) {
            $zip->close();
            \Illuminate\Support\Facades\File::deleteDirectory($tempDir);

            // ✅ FIX: Return JSON for AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to extract ZIP file.'
                ], 400);
            }
            return back()->with('error', 'Failed to extract ZIP file.');
        }
        $zip->close();

        $moduleJson = collect(glob($tempDir.'/*/module.json'))->first();
        if (!$moduleJson) {
            \Illuminate\Support\Facades\File::deleteDirectory($tempDir);

            // ✅ FIX: Return JSON for AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'module.json not found in ZIP.'
                ], 400);
            }
            return back()->with('error', 'module.json not found in ZIP.');
        }

        $meta = json_decode(file_get_contents($moduleJson), true);
        $slug    = $meta['slug'] ?? $filename;
        $name    = $meta['name'] ?? $filename;
        $version = $meta['version'] ?? '1.0.0';

        $moduleFolder = dirname($moduleJson);

        try {
            $destPath = base_path("modules/{$slug}");
            if (\Illuminate\Support\Facades\File::exists($destPath)) {
                // ✅ FIX: Return JSON for AJAX
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Module folder '{$slug}' already exists."
                    ], 409);
                }
                return back()->with('error', "Module folder '{$slug}' already exists.");
            }

            \Illuminate\Support\Facades\File::ensureDirectoryExists($destPath);
            \Illuminate\Support\Facades\File::copyDirectory($moduleFolder, $destPath);

            // Run module-specific install.php safely
            $installFile = $destPath . '/install.php';
            if (file_exists($installFile)) {
                try {
                    include $installFile;
                } catch (\Throwable $e) {
                    \Log::error("Module install.php failed: {$slug}", ['error' => $e->getMessage()]);
                }
            }

            // ✅ Always create/update DB record
            \App\Models\Module::updateOrCreate(
                ['slug' => $slug],
                [
                    'name'    => $name,
                    'version' => $version,
                    'active'  => 0,
                ]
            );

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\File::deleteDirectory($tempDir);

            // ✅ FIX: Return JSON for AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Module installation failed: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Module installation failed: ' . $e->getMessage());
        }

        // Clean up temp folder
        \Illuminate\Support\Facades\File::deleteDirectory($tempDir);

        // ✅ FIX: Return JSON for AJAX requests with redirect URL
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Module '{$name}' installed successfully. Activate to enable.",
                'redirect' => route('modules.index'),
                'module' => [
                    'slug' => $slug,
                    'name' => $name,
                    'version' => $version
                ]
            ]);
        }

        return back()->with('success', "Module '{$name}' installed successfully. Activate to enable.");
    }



    /**
     * 3️⃣ ACTIVATE MODULE
     * - Create ACTIVE flag file
     * - Install menus & permissions
     * - Activate menus
     */
    public function activate(string $slug)
    {
        $module = Module::where('slug', $slug)->firstOrFail();

        // Activate module files (ACTIVE flag)
        $this->lifecycle->activate($slug);

        // Install menu.json + permissions
        $modulePath = base_path("modules/{$slug}");
        $this->menuInstaller->install($modulePath, $slug);

        // Activate menus using route prefix
        // Menu::where('route', 'like', "{$slug}.%")->update(['is_active' => 1]);
        Menu::where('module_slug', $slug)->update(['is_active' => 1]);

    // Load module-specific permissions if exists
    $permFile = $modulePath . '/permissions.php';
    if (file_exists($permFile)) {
        $permConfig = include $permFile; // dynamically load module's permissions
    } else {
        $permConfig = ['roles' => [], 'users' => []];
    }

    // Create permissions - handle both flat and nested formats
    foreach ($permConfig['roles'] ?? [] as $roleName => $perms) {
        // Handle nested format: ['system' => ['index'], 'folder' => [...]]
        if (is_array($perms) && isset($perms[array_key_first($perms)])) {
            // Check if first element is array (nested format)
            $firstVal = is_array($perms[array_key_first($perms)]) ?? false;
            if ($firstVal) {
                // Nested format: iterate over types
                foreach ($perms as $type => $actions) {
                    if (is_array($actions)) {
                        foreach ($actions as $action) {
                            $permName = "{$slug}.{$type}.{$action}";
                            Permission::firstOrCreate([
                                'name' => $permName,
                                'guard_name' => 'web'
                            ]);
                            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
                            $role->givePermissionTo($permName);
                        }
                    }
                }
                continue;
            }
        }
        
        // Flat format: iterate over string permissions
        foreach ($perms as $perm) {
            if (is_string($perm)) {
                Permission::firstOrCreate([
                    'name' => "{$slug}.{$perm}",
                    'guard_name' => 'web'
                ]);
                $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
                $role->givePermissionTo("{$slug}.{$perm}");
            }
        }
    }

    // Assign roles to users if specified
    foreach ($permConfig['users'] ?? [] as $userEmail => $roleName) {
        $user = \App\Models\User::where('email', $userEmail)->first();
        if ($user && Role::where('name', $roleName)->exists()) {
            $user->assignRole($roleName);
        }
    }

        // Mark module as active
        $module->update(['active' => 1]);

        return back()->with('success', "Module '{$module->name}' activated.");
    }

    /**
     * 4️⃣ DEACTIVATE MODULE
     * - Remove ACTIVE flag
     * - Deactivate menus
     */
    public function deactivate(string $slug)
    {
        $module = Module::where('slug', $slug)->firstOrFail();

        $this->lifecycle->deactivate($slug);

        // Deactivate menus using route prefix
        Menu::where('route', 'like', "{$slug}.%")->update(['is_active' => 0]);

        $module->update(['active' => 0]);

        return back()->with('success', "Module '{$module->name}' deactivated.");
    }

    /**
     * 5️⃣ UNINSTALL MODULE
     * - Remove module files
     * - Delete DB record
     * - Remove menus & permissions
     */
    public function uninstall(Request $request, string $slug)
    {
        $keepData = $request->input('keep_data') == 1;

        // Remove module files
        $this->installer->uninstall($slug);

        // Delete module record
        \App\Models\Module::where('slug', $slug)->delete();

        // Delete menus
        \App\Models\Menu::where('module_slug', $slug)->delete();

        // Delete module-specific permissions
        \Spatie\Permission\Models\Permission::where('name', 'like', "{$slug}.%")->delete();

        // Drop tables if keepData is false and uninstall.php exists
        $uninstallFile = base_path("modules/{$slug}/uninstall.php");
        if (!$keepData && file_exists($uninstallFile)) {
            include $uninstallFile; // execute module-specific uninstall
        }

        return back()->with('success', "Module '{$slug}' uninstalled. " . ($keepData ? 'Tables/data preserved.' : 'Tables/data deleted.'));
    }

    /**
     * 6️⃣ LOCK MODULE MENU
     * - Prevent sidebar menu from being clickable
     */
    public function lock(string $slug)
    {
        Menu::where('route', 'like', "{$slug}.%")->update(['is_locked' => 1]);
        return back()->with('success', 'Module menu locked');
    }

    /**
     * 7️⃣ UNLOCK MODULE MENU
     */
    public function unlock(string $slug)
    {
        Menu::where('route', 'like', "{$slug}.%")->update(['is_locked' => 0]);
        return back()->with('success', 'Module menu unlocked');
    }

    /**
     * 8  MODULE Update
     */
    public function update(string $slug)
    {
    $module = Module::where('slug', $slug)->firstOrFail();

    // Call your installer or updater logic here
    $this->installer->update($slug);

    return back()->with('success', "Module '{$module->name}' updated successfully.");
    }


}