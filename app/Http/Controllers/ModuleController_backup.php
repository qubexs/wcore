<?php
// app/Http/Controllers/ModuleController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Core\Module\ModuleMenuInstaller;
use App\Core\Module\ModuleRegistry;
use App\Core\Module\ModuleLifecycle;
use App\Core\Module\ModuleInstaller;
use App\Models\Module;
use App\Models\Menu;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class ModuleController extends Controller
{
    protected ModuleRegistry $registry;
    protected ModuleLifecycle $lifecycle;
    protected ModuleInstaller $installer;
    protected ModuleMenuInstaller $menuInstaller; // ✅

    public function __construct(
        ModuleRegistry $registry,
        ModuleLifecycle $lifecycle,
        ModuleInstaller $installer,
        ModuleMenuInstaller $menuInstaller // ✅ injected
    ) {
        $this->registry  = $registry;
        $this->lifecycle = $lifecycle;
        $this->installer = $installer;
        $this->menuInstaller = $menuInstaller; // ✅ injected
    }

    /**
     * 1️⃣ LIST ALL MODULES
     */
    public function index()
    {
        $modules = Module::all();
        return view('modules.index', [
            'registry' => $this->registry,
            'modules'  => $modules,
        ]);
    }

    /**
     * 2️⃣ INSTALL MODULE FROM ZIP
     */
    public function install(Request $request)
    {
        $request->validate([
            'module_zip' => 'required|file|mimes:zip|max:10240',
        ]);

        $zipFile  = $request->file('module_zip');
        $filename = pathinfo($zipFile->getClientOriginalName(), PATHINFO_FILENAME);

        $uploadDir = storage_path('modules/zips');
        $tempDir   = storage_path('modules/temp');

        File::ensureDirectoryExists($uploadDir);
        File::ensureDirectoryExists($tempDir);
        File::deleteDirectory($tempDir);

        $zipPath = $uploadDir.'/'.$zipFile->getClientOriginalName();
        $zipFile->move($uploadDir, $zipFile->getClientOriginalName());

        // Extract ZIP to temp
        $zip = new \ZipArchive;
        if ($zip->open($zipPath) !== true) {
            return back()->with('error', 'Unable to open module ZIP');
        }
        $zip->extractTo($tempDir);
        $zip->close();
        
        // Read module.json
        $moduleJson = collect(glob($tempDir.'/*/module.json'))->first();
        $meta       = $moduleJson ? json_decode(file_get_contents($moduleJson), true) : [];

        $slug    = $meta['slug'] ?? $filename;
        $version = $meta['version'] ?? '1.0.0';
        $name    = $meta['name'] ?? $filename;

        try {
            // Install via installer
            $this->installer->install($zipPath, $slug);

            // Save module
            $module = Module::updateOrCreate(
                ['slug' => $slug],
                [
                    'name'    => $name,
                    'version' => $version,
                    'active'  => 0,
                ]
            );

            // Auto-register permissions
            $permFile = collect(glob($tempDir.'/*/config/permissions.json'))->first();
            if ($permFile) {
                $permissions = json_decode(file_get_contents($permFile), true);
                foreach ($permissions['roles'] ?? [] as $roleName => $perms) {
                    $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
                    foreach ($perms as $perm) {
                        $permission = Permission::firstOrCreate([
                            'name'       => "{$perm} {$slug}",
                            'guard_name' => 'web',
                        ]);
                        $role->givePermissionTo($permission);
                    }
                }
            }

            // Auto-register menus
            $menuFile = collect(glob($tempDir.'/*/config/menu.json'))->first();
            if ($menuFile) {
                $menus = json_decode(file_get_contents($menuFile), true);
                foreach ($menus as $menu) {
                    Menu::updateOrCreate(
                        ['route' => $menu['route']],
                        [
                            'title'      => $menu['title'],
                            'icon'       => $menu['icon'] ?? 'fas fa-puzzle-piece',
                            'permission' => $menu['permission'] ?? null,
                            'parent_id'  => $menu['parent_id'] ?? null,
                            'order'      => $menu['order'] ?? 0,
                            'is_active'  => 1,
                        ]
                    );
                }
            }

            File::deleteDirectory($tempDir);

            return back()->with('success', "Module '{$name}' installed successfully.");

        } catch (\Exception $e) {
            File::deleteDirectory($tempDir);
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * 3️⃣ ACTIVATE MODULE
     */
    public function activate(string $slug)
    {
        try {
            $module = Module::where('slug', $slug)->firstOrFail();
            $module->update(['active' => 1]);
            $this->lifecycle->activate($slug);

            // Ensure menu is active
            Menu::where('route', "{$slug}.index")->update(['is_active' => 1]);

            return back()->with('success', "Module '{$module->name}' activated.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * 4️⃣ DEACTIVATE MODULE
     */
    public function deactivate(string $slug)
    {
        try {
            $module = Module::where('slug', $slug)->firstOrFail();
            $module->update(['active' => 0]);
            $this->lifecycle->deactivate($slug);

            // Disable menu
            Menu::where('route', "{$slug}.index")->update(['is_active' => 0]);

            return back()->with('success', "Module '{$module->name}' deactivated.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * 5️⃣ UNINSTALL MODULE
     */
    public function uninstall(string $slug)
    {
        try {
            $this->installer->uninstall($slug);

            Module::where('slug', $slug)->delete();
            Menu::where('route', "{$slug}.index")->delete();
            Permission::where('name', 'like', "%{$slug}%")->delete();

            return back()->with('success', "Module '{$slug}' uninstalled.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * 6️⃣ LOCK MODULE MENU
     */
    public function lock(string $slug)
    {
        Menu::where('route', "{$slug}.index")->update(['is_locked' => true]);
        return back()->with('success', 'Module menu locked');
    }

    /**
     * 7️⃣ UNLOCK MODULE MENU
     */
    public function unlock(string $slug)
    {
        Menu::where('route', "{$slug}.index")->update(['is_locked' => false]);
        return back()->with('success', 'Module menu unlocked');
    }
}
