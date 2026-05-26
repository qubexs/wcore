<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DashboardLayout;
use App\Models\DashboardProfile;

class DashboardController extends Controller
{
    /**
     * Save user's dashboard layout
     */
    public function saveLayout(Request $request)
    {
        $validated = $request->validate([
            'layout' => 'required|array',
            'layout.*.widgetId' => 'required|string',
            'layout.*.widgetType' => 'required|string',
            'layout.*.x' => 'required|integer',
            'layout.*.y' => 'required|integer',
            'layout.*.w' => 'required|integer',
            'layout.*.h' => 'required|integer',
            'manual' => 'boolean',
            'profile_id' => 'nullable|integer|exists:dashboard_profiles,id'
        ]);

        $userId = Auth::id();
        
        // Save to user's default layout or specific profile
        $layout = DashboardLayout::updateOrCreate(
            [
                'user_id' => $userId,
                'profile_id' => $request->profile_id ?? null
            ],
            [
                'layout_data' => json_encode($validated['layout']),
                'updated_at' => now()
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Dashboard layout saved successfully',
            'layout_id' => $layout->id
        ]);
    }

    /**
     * Get user's dashboard layout
     */
    public function getLayout(Request $request)
    {
        $userId = Auth::id();
        $profileId = $request->profile_id ?? null;

        $layout = DashboardLayout::where('user_id', $userId)
            ->where('profile_id', $profileId)
            ->first();

        if ($layout) {
            return response()->json([
                'success' => true,
                'layout' => json_decode($layout->layout_data, true)
            ]);
        }

        // Return empty if no saved layout
        return response()->json([
            'success' => true,
            'layout' => []
        ]);
    }

    /**
     * Create a new dashboard profile
     */
    public function createProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_default' => 'boolean'
        ]);

        $userId = Auth::id();

        // If setting as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            DashboardProfile::where('user_id', $userId)
                ->update(['is_default' => false]);
        }

        $profile = DashboardProfile::create([
            'user_id' => $userId,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_default' => $validated['is_default'] ?? false
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dashboard profile created successfully',
            'profile' => $profile
        ]);
    }

    /**
     * Get all user's dashboard profiles
     */
    public function getProfiles()
    {
        $userId = Auth::id();

        $profiles = DashboardProfile::where('user_id', $userId)
            ->with('layout')
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'profiles' => $profiles
        ]);
    }

    /**
     * Delete a dashboard profile
     */
    public function deleteProfile($profileId)
    {
        $userId = Auth::id();

        $profile = DashboardProfile::where('id', $profileId)
            ->where('user_id', $userId)
            ->first();

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found'
            ], 404);
        }

        // Don't allow deleting default profile
        if ($profile->is_default) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete default profile'
            ], 400);
        }

        $profile->delete();

        return response()->json([
            'success' => true,
            'message' => 'Profile deleted successfully'
        ]);
    }

    /**
     * Update a dashboard profile
     */
    public function updateProfile(Request $request, $profileId)
    {
        $userId = Auth::id();

        $profile = DashboardProfile::where('id', $profileId)
            ->where('user_id', $userId)
            ->first();

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found'
            ], 404);
        }

        $profile->name = $request->name ?? $profile->name;
        $profile->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'profile' => $profile
        ]);
    }

    /**
     * Load a specific profile
     */
    public function loadProfile($profileId)
    {
        $userId = Auth::id();

        $profile = DashboardProfile::where('id', $profileId)
            ->where('user_id', $userId)
            ->with('layout')
            ->first();

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found'
            ], 404);
        }

        $layout = $profile->layout 
            ? json_decode($profile->layout->layout_data, true) 
            : [];

        return response()->json([
            'success' => true,
            'profile' => $profile,
            'layout' => $layout
        ]);
    }

    /**
     * Set a profile as default
     */
    public function setDefaultProfile($profileId)
    {
        $userId = Auth::id();

        // Unset all defaults
        DashboardProfile::where('user_id', $userId)
            ->update(['is_default' => false]);

        // Set new default
        $profile = DashboardProfile::where('id', $profileId)
            ->where('user_id', $userId)
            ->first();

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found'
            ], 404);
        }

        $profile->is_default = true;
        $profile->save();

        return response()->json([
            'success' => true,
            'message' => 'Default profile updated'
        ]);
    }

    /**
     * Duplicate a profile
     */
    public function duplicateProfile($profileId)
    {
        $userId = Auth::id();

        $original = DashboardProfile::where('id', $profileId)
            ->where('user_id', $userId)
            ->with('layout')
            ->first();

        if (!$original) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found'
            ], 404);
        }

        $duplicate = DashboardProfile::create([
            'user_id' => $userId,
            'name' => $original->name . ' (Copy)',
            'description' => $original->description,
            'is_default' => false
        ]);

        // Copy layout if exists
        if ($original->layout) {
            DashboardLayout::create([
                'user_id' => $userId,
                'profile_id' => $duplicate->id,
                'layout_data' => $original->layout->layout_data
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile duplicated successfully',
            'profile' => $duplicate
        ]);
    }

    /**
     * Get real-time dashboard statistics for widgets
     */
    public function getStats()
    {
        $stats = [
            // User statistics
            'total_users' => \App\Models\User::count(),
            'active_users' => \App\Models\User::where('status', 'active')->count(),
            'inactive_users' => \App\Models\User::where('status', 'inactive')->count(),
            'suspended_users' => \App\Models\User::where('status', 'suspended')->count(),
            
            // Role statistics
            'total_roles' => \App\Models\Role::count(),
            'roles_with_users' => \App\Models\Role::has('users')->count(),
            
            // Department statistics
            'total_departments' => class_exists(\App\Models\Department::class) 
                ? \App\Models\Department::count() 
                : 0,
            
            // Permission statistics
            'total_permissions' => \App\Models\Permission::count(),
            
            // Activity statistics
            'pending_requests' => class_exists(\App\Models\AccessRequest::class)
                ? \App\Models\AccessRequest::where('status', 'pending')->count()
                : 0,
            'recent_activity' => $this->getRecentActivity(),
            
            // System statistics
            'system_health' => $this->getSystemHealth(),
            'storage_usage' => $this->getStorageUsage(),
            'last_backup' => $this->getLastBackupTime(),
            'database_size' => $this->getDatabaseSize(),
            
            // Timestamp
            'last_updated' => now()->toIso8601String()
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Get recent activity (last 10 events)
     */
    private function getRecentActivity(): array
    {
        try {
            if (class_exists(\App\Models\ActivityLog::class)) {
                return \App\Models\ActivityLog::with('user')
                    ->latest()
                    ->take(10)
                    ->get()
                    ->map(function($log) {
                        return [
                            'user' => $log->user->name ?? 'System',
                            'event' => $log->event,
                            'description' => $log->description,
                            'time' => $log->created_at->diffForHumans()
                        ];
                    })
                    ->toArray();
            }
            
            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get system health status
     */
    private function getSystemHealth(): string
    {
        try {
            // Check if database is accessible
            \DB::connection()->getPdo();
            
            // Check if storage is writable
            $storagePath = storage_path('app');
            if (!is_writable($storagePath)) {
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
     * Get last backup time
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
                $diff = now()->diffForHumans(\Carbon\Carbon::createFromTimestamp($latest));
                return $diff;
            }
            
            return 'Never';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Get latest file time in directory
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
    private function getDatabaseSize(): string
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
    private function formatBytes($bytes): string
    {
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576)    return round($bytes / 1048576, 2)    . ' MB';
        if ($bytes >= 1024)       return round($bytes / 1024, 2)       . ' KB';
        return $bytes . ' B';
    }
}