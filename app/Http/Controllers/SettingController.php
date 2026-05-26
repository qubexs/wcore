<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Display the settings page.
     * Passes a flat key => value array to the blade.
     */
    public function index()
    {
        // Flat key => value array — blade uses $settings['site_name'] etc.
        $settings = Setting::where('is_active', 1)
            ->whereNull('deleted_at')
            ->pluck('value', 'key')
            ->toArray();

        // Dashboard stats - Enhanced for Dashboard Builder
        $stats = [
            // Settings stats
            'total_settings'  => Setting::count(),
            'active_settings' => Setting::where('is_active', 1)->whereNull('deleted_at')->count(),
            'last_updated'    => optional(Setting::latest('updated_at')->first())->updated_at
                                    ? Setting::latest('updated_at')->first()->updated_at->format('d M Y')
                                    : '—',
            
            // User stats
            'total_users'     => \App\Models\User::count(),
            'active_users'    => \App\Models\User::where('status', 'active')->count(),
            'inactive_users'  => \App\Models\User::where('status', 'inactive')->count(),
            'suspended_users' => \App\Models\User::where('status', 'suspended')->count(),
            
            // Role stats
            'total_roles'     => \App\Models\Role::count(),
            'roles_with_users' => \App\Models\Role::has('users')->count(),
            
            // Department stats (if Department model exists)
            'total_departments' => class_exists(\App\Models\Department::class) 
                ? \App\Models\Department::count() 
                : 0,
            
            // Permission stats
            'total_permissions' => \App\Models\Permission::count(),
            'active_permissions' => \App\Models\Permission::count(),
            
            // Activity stats (if activity logs exist)
            'pending_requests' => class_exists(\App\Models\AccessRequest::class)
                ? \App\Models\AccessRequest::where('status', 'pending')->count()
                : 0,
            
            // System stats
            'system_health'   => 'Healthy',
            'storage_usage'   => $this->getStorageUsage(),
            'last_backup'     => $this->getLastBackupTime(),
            'database_size'   => $this->getDatabaseSize(),
            
            // Recent activity
            'recent_logins'   => $this->getRecentLogins(5),
            'activity_count'  => $this->getActivityCount(),
        ];

        // Recent changes for audit table on Dashboard tab
        $recentChanges = Setting::with('updatedBy')
            ->whereNotNull('updated_by')
            ->latest('updated_at')
            ->take(10)
            ->get();

        // Database backup list
        $backups = $this->getBackupFiles(storage_path('app/backups/db'));

        // Website backup list
        $websiteBackups = $this->getBackupFiles(storage_path('app/backups/website'));

        return view('settings.index', compact(
            'settings',
            'stats',
            'recentChanges',
            'backups',
            'websiteBackups'
        ));
    }

    /**
     * Save / update settings from the General tab form.
     * Expects input names as: settings[key_name]
     */
    public function update(Request $request)
    {
        $inputs = $request->input('settings', []);

        foreach ($inputs as $key => $value) {

            // Detect existing row to get data_type
            $existing  = Setting::where('key', $key)->first();
            $dataType  = $existing->data_type ?? 'string';
            $category  = $existing->category  ?? 'general';

            // Boolean: override value from hidden + checkbox pair
            // (Hidden sends "0", checked checkbox overwrites with "1")
            if ($dataType === 'boolean') {
                $value = $request->has("settings.$key") ? 1 : 0;
            }

            // File upload: store and replace value with path
            if ($dataType === 'file' && $request->hasFile("settings.$key")) {
                // Delete old file if exists
                if ($existing && $existing->value) {
                    Storage::disk('public')->delete($existing->value);
                }
                $path  = $request->file("settings.$key")->store('settings', 'public');
                $value = $path;
            }

            // Save — insert if not exist, update if exists
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value'      => $value,
                    'category'   => $category,
                    'data_type'  => $dataType,
                    'is_active'  => 1,
                    'updated_by' => auth()->id(),
                ]
            );
        }

        // Clear cached settings so app picks up new values immediately
        Cache::forget('app_settings');

        return back()->with('success', 'Settings saved successfully.');
    }

    /**
     * Helper: scan a directory and return backup file list.
     */
    private function getBackupFiles(string $directory): array
    {
        $backups = [];

        if (!is_dir($directory)) {
            return $backups;
        }

        $files = array_diff(scandir($directory, SCANDIR_SORT_DESCENDING), ['.', '..']);

        foreach ($files as $file) {
            $path      = $directory . DIRECTORY_SEPARATOR . $file;
            $backups[] = [
                'name' => $file,
                'size' => $this->formatBytes(filesize($path)),
                'time' => date('d M Y H:i', filemtime($path)),
            ];
        }

        return $backups;
    }

    /**
     * Helper: human-readable file size.
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576)    return round($bytes / 1048576, 2)    . ' MB';
        if ($bytes >= 1024)       return round($bytes / 1024, 2)       . ' KB';
        return $bytes . ' B';
    }

    /**
     * Get storage usage percentage
     */
    private function getStorageUsage(): string
    {
        try {
            $totalSpace = disk_total_space(base_path());
            $freeSpace = disk_free_space(base_path());
            $usedSpace = $totalSpace - $freeSpace;
            $percentage = ($usedSpace / $totalSpace) * 100;
            return round($percentage, 1) . '%';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * Get last backup timestamp
     */
    private function getLastBackupTime(): string
    {
        try {
            $dbBackupDir = storage_path('app/backups/db');
            $webBackupDir = storage_path('app/backups/website');
            
            $latestDb = $this->getLatestFileTime($dbBackupDir);
            $latestWeb = $this->getLatestFileTime($webBackupDir);
            
            $latest = max($latestDb, $latestWeb);
            
            if ($latest > 0) {
                return date('M d, H:i', $latest);
            }
            
            return 'Never';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Get latest file modification time in directory
     */
    private function getLatestFileTime(string $directory): int
    {
        if (!is_dir($directory)) {
            return 0;
        }

        $files = array_diff(scandir($directory, SCANDIR_SORT_DESCENDING), ['.', '..']);
        
        if (empty($files)) {
            return 0;
        }

        $latestTime = 0;
        foreach ($files as $file) {
            $path = $directory . DIRECTORY_SEPARATOR . $file;
            $time = filemtime($path);
            if ($time > $latestTime) {
                $latestTime = $time;
            }
        }

        return $latestTime;
    }

    /**
     * Get database size
     */
    private function getDatabaseSize(): string
    {
        try {
            $dbName = env('DB_DATABASE');
            $query = "SELECT 
                        SUM(data_length + index_length) as size 
                      FROM information_schema.TABLES 
                      WHERE table_schema = ?";
            
            $result = \DB::select($query, [$dbName]);
            
            if (!empty($result)) {
                return $this->formatBytes($result[0]->size ?? 0);
            }
            
            return 'N/A';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * Get recent login activities
     */
    private function getRecentLogins(int $limit = 5): array
    {
        try {
            // If you have an activity log or login history table
            // Replace this with your actual implementation
            if (class_exists(\App\Models\ActivityLog::class)) {
                return \App\Models\ActivityLog::where('event', 'login')
                    ->with('user')
                    ->latest()
                    ->take($limit)
                    ->get()
                    ->map(function($log) {
                        return [
                            'user' => $log->user->name ?? 'Unknown',
                            'time' => $log->created_at->diffForHumans(),
                            'ip' => $log->ip_address ?? 'N/A'
                        ];
                    })
                    ->toArray();
            }
            
            // Fallback: Get recent users
            return \App\Models\User::latest('last_login_at')
                ->take($limit)
                ->get()
                ->map(function($user) {
                    return [
                        'user' => $user->name,
                        'time' => $user->last_login_at 
                            ? $user->last_login_at->diffForHumans() 
                            : 'Never',
                        'ip' => 'N/A'
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get total activity count (last 24 hours)
     */
    private function getActivityCount(): int
    {
        try {
            if (class_exists(\App\Models\ActivityLog::class)) {
                return \App\Models\ActivityLog::where('created_at', '>=', now()->subDay())
                    ->count();
            }
            
            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Show department menu access management page
     */
    public function departmentMenus(Request $request)
    {
        $departments = \App\Models\Department::orderBy('name')->get();
        
        $menus = \App\Models\Menu::where('is_active', 1)
            ->whereNull('parent_id')
            ->with(['children' => function ($q) {
                $q->where('is_active', 1)->orderBy('order');
            }])
            ->orderBy('order')
            ->get();

        // Get existing department-menu associations
        $existingMappings = \DB::table('department_menu')
            ->get()
            ->groupBy('menu_id')
            ->map(fn($items) => $items->pluck('department_id')->toArray())
            ->toArray();

        return view('settings.department-menus', compact('departments', 'menus', 'existingMappings'));
    }

    /**
     * Update department menu access
     */
    public function updateDepartmentMenus(Request $request)
    {
        $menus = $request->input('menus', []);

        // Clear existing department_menu records
        \DB::table('department_menu')->delete();

        // Insert new mappings
        foreach ($menus as $menuId => $departmentIds) {
            foreach ($departmentIds as $deptId) {
                \DB::table('department_menu')->insert([
                    'menu_id' => $menuId,
                    'department_id' => $deptId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('settings.departmentMenus')->with('success', 'Department menu access updated successfully!');
    }
}