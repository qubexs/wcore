<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Department;
use App\Models\DashboardLayout;
use App\Models\DashboardProfile;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $userId = Auth::id();
        
        // Load user's saved dashboard layout from Settings → Dashboard
        $dashboardData = $this->loadUserDashboard($userId);
        
        // Get widget statistics for real-time values
        $stats = $this->getDashboardStats();
        
        // Legacy widget data (for backwards compatibility)
        $widget = [
            'users' => User::count(),
        ];
        
        return view('home', array_merge([
            'widget' => $widget,
            'stats' => $stats,
        ], $dashboardData));
    }
    
    /**
     * Load user's saved dashboard layout and profile
     * This loads what the user designed in Settings → Dashboard tab
     */
    private function loadUserDashboard($userId)
    {
        // Try to get user's default profile
        $profile = DashboardProfile::where('user_id', $userId)
            ->where('is_default', true)
            ->first();
        
        // If no default profile, get the most recent profile
        if (!$profile) {
            $profile = DashboardProfile::where('user_id', $userId)
                ->orderBy('updated_at', 'desc')
                ->first();
        }
        
        // Load layout for this profile
        $layout = null;
        if ($profile) {
            $layout = DashboardLayout::where('user_id', $userId)
                ->where('profile_id', $profile->id)
                ->first();
        }
        
        // If no profile-specific layout, try default layout
        if (!$layout) {
            $layout = DashboardLayout::where('user_id', $userId)
                ->whereNull('profile_id')
                ->first();
        }
        
        // Decode layout data
        $dashboardLayout = [];
        if ($layout && $layout->layout_data) {
            $dashboardLayout = is_string($layout->layout_data) 
                ? json_decode($layout->layout_data, true) 
                : $layout->layout_data;
        }
        
        return [
            'dashboardLayout' => $dashboardLayout ?? [],
            'layoutWidgetCount' => count($dashboardLayout),
            'profileName' => $profile->name ?? 'Default',
            'profileId' => $profile->id ?? null
        ];
    }
    
    /**
     * Get all dashboard statistics
     */
    private function getDashboardStats()
    {
        $stats = [
            // User statistics
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'inactive_users' => User::where('status', 'inactive')->count(),
            'suspended_users' => User::where('status', 'suspended')->count(),
            
            // Role statistics
            'total_roles' => Role::count(),
            'roles_with_users' => Role::has('users')->count(),
            
            // Department statistics
            'total_departments' => class_exists(Department::class) 
                ? Department::count() 
                : 0,
            
            // Permission statistics
            'total_permissions' => Permission::count(),
            'active_permissions' => Permission::count(),
            
            // Activity statistics
            'pending_requests' => class_exists(\App\Models\AccessRequest::class)
                ? \App\Models\AccessRequest::where('status', 'pending')->count()
                : 0,
            
            // System statistics
            'system_health' => $this->getSystemHealth(),
            'storage_usage' => $this->getStorageUsage(),
            'last_backup' => $this->getLastBackupTime(),
            'database_size' => $this->getDatabaseSize(),
            
            // Additional stats
            'last_updated' => now()->format('g:i A'),
        ];
        
        return $stats;
    }
    
    /**
     * Get system health status
     */
    private function getSystemHealth()
    {
        try {
            // Check database connection
            \DB::connection()->getPdo();
            
            // Check storage writability
            if (!is_writable(storage_path('app'))) {
                return 'Warning';
            }
            
            // Check disk space
            $freeSpace = disk_free_space(base_path());
            $totalSpace = disk_total_space(base_path());
            $usagePercent = (($totalSpace - $freeSpace) / $totalSpace) * 100;
            
            if ($usagePercent > 90) {
                return 'Critical';
            } elseif ($usagePercent > 75) {
                return 'Warning';
            }
            
            return 'Healthy';
        } catch (\Exception $e) {
            return 'Error';
        }
    }
    
    /**
     * Get storage usage percentage
     */
    private function getStorageUsage()
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
     * Get last backup time
     */
    private function getLastBackupTime()
    {
        try {
            $dbBackupDir = storage_path('app/backups/db');
            $webBackupDir = storage_path('app/backups/website');
            
            $latestDb = $this->getLatestFileTime($dbBackupDir);
            $latestWeb = $this->getLatestFileTime($webBackupDir);
            
            $latest = max($latestDb, $latestWeb);
            
            if ($latest > 0) {
                return \Carbon\Carbon::createFromTimestamp($latest)->diffForHumans();
            }
            
            return 'Never';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }
    
    /**
     * Get latest file time in directory
     */
    private function getLatestFileTime($directory)
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
            if (is_file($path)) {
                $time = filemtime($path);
                if ($time > $latestTime) {
                    $latestTime = $time;
                }
            }
        }

        return $latestTime;
    }
    
    /**
     * Get database size
     */
    private function getDatabaseSize()
    {
        try {
            $dbName = env('DB_DATABASE');
            $query = "SELECT 
                        SUM(data_length + index_length) as size 
                      FROM information_schema.TABLES 
                      WHERE table_schema = ?";
            
            $result = \DB::select($query, [$dbName]);
            
            if (!empty($result) && isset($result[0]->size)) {
                return $this->formatBytes($result[0]->size);
            }
            
            return 'N/A';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }
    
    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes)
    {
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576)    return round($bytes / 1048576, 2)    . ' MB';
        if ($bytes >= 1024)       return round($bytes / 1024, 2)       . ' KB';
        return $bytes . ' B';
    }
}