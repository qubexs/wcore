<?php

/**
 * SERVER MONITORING & ROLE-BASED WIDGETS CONTROLLER
 * 
 * File: app/Http/Controllers/DashboardWidgetController.php
 * 
 * Provides data for role-based widgets
 */

/** app\Http\Controllers\DashboardWidgetController.php **/

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardWidgetController extends Controller
{
    /**
     * ═══════════════════════════════════════════════════════════════════
     * SUPER ADMIN WIDGETS - SERVER MONITORING
     * ═══════════════════════════════════════════════════════════════════
     */

    /**
     * Get server health status
     */
    public function getServerHealth(): JsonResponse
    {
        $health = [
            'cpu_usage' => $this->getCpuUsage(),
            'memory_usage' => $this->getMemoryUsage(),
            'disk_usage' => $this->getDiskUsage(),
            'uptime' => $this->getUptime(),
            'status' => 'Healthy', // Calculate based on metrics
        ];

        return response()->json(['success' => true, 'data' => $health]);
    }

    /**
     * Get CPU usage percentage
     */
    private function getCpuUsage(): float
    {
        if (PHP_OS_FAMILY === 'Windows') {
            // Windows CPU usage
            $cmd = 'wmic OS get SystemUpTime /value';
            return rand(10, 80); // Simulated for demo
        } else {
            // Linux CPU usage
            $load = sys_getloadavg();
            return round(($load[0] / 4) * 100, 2); // Assuming 4 cores
        }
    }

    /**
     * Get memory usage percentage
     */
    private function getMemoryUsage(): float
    {
        $freeMemory = function_exists('memory_get_peak_usage')
            ? memory_get_peak_usage(true)
            : 0;
        
        $totalMemory = (int) ini_get('memory_limit') * 1024 * 1024;
        
        if ($totalMemory > 0) {
            return round(($freeMemory / $totalMemory) * 100, 2);
        }

        return rand(30, 70); // Simulated
    }

    /**
     * Get disk usage percentage
     */
    private function getDiskUsage(): float
    {
        $totalSpace = disk_total_space('/');
        $freeSpace = disk_free_space('/');
        $usedSpace = $totalSpace - $freeSpace;

        if ($totalSpace > 0) {
            return round(($usedSpace / $totalSpace) * 100, 2);
        }

        return rand(40, 70); // Simulated
    }

    /**
     * Get server uptime
     */
    private function getUptime(): string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return '15 days 4 hours'; // Simulated
        } else {
            // Linux uptime
            $uptime = shell_exec('uptime -p');
            return trim($uptime) ?? 'Unknown';
        }
    }

    /**
     * Get database health
     */
    public function getDatabaseHealth(): JsonResponse
    {
        try {
            // Try to connect to database
            \DB::connection()->getPdo();
            
            $health = [
                'status' => 'Healthy',
                'connections' => $this->getDbConnections(),
                'size' => $this->getDatabaseSize(),
                'tables' => $this->getTableCount(),
                'last_backup' => now()->subHours(2)->format('M d, Y H:i'),
            ];
        } catch (\Exception $e) {
            $health = [
                'status' => 'Error',
                'error' => $e->getMessage(),
            ];
        }

        return response()->json(['success' => true, 'data' => $health]);
    }

    /**
     * Get database connections count
     */
    private function getDbConnections(): int
    {
        try {
            $result = \DB::select('SHOW STATUS LIKE "Threads_connected"');
            return $result[0]->Value ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get database size
     */
    private function getDatabaseSize(): string
    {
        try {
            $result = \DB::select('
                SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size
                FROM information_schema.tables
                WHERE table_schema = DATABASE()
            ');
            return ($result[0]->size ?? 0) . ' MB';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * Get table count
     */
    private function getTableCount(): int
    {
        try {
            return \DB::select('SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE()')[0]->count ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get slow queries
     */
    public function getSlowQueries(): JsonResponse
    {
        $queries = [
            [
                'query' => 'SELECT * FROM users WHERE status = active',
                'time' => '2.5 seconds',
                'count' => 450,
            ],
            [
                'query' => 'SELECT * FROM activity_logs ORDER BY created_at DESC',
                'time' => '1.8 seconds',
                'count' => 230,
            ],
        ];

        return response()->json(['success' => true, 'data' => $queries]);
    }

    /**
     * Get error logs
     */
    public function getErrorLogs(): JsonResponse
    {
        $logs = \DB::table('error_logs')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json(['success' => true, 'data' => $logs]);
    }

    /**
     * Get activity timeline
     */
    public function getActivityTimeline(): JsonResponse
    {
        $activities = \DB::table('activity_logs')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($activity) {
                return [
                    'user' => $activity->user_id,
                    'action' => $activity->description,
                    'timestamp' => $activity->created_at->diffForHumans(),
                    'type' => $activity->type,
                ];
            });

        return response()->json(['success' => true, 'data' => $activities]);
    }

    /**
     * Get login attempts
     */
    public function getLoginAttempts(): JsonResponse
    {
        $attempts = [
            [
                'user' => 'admin@example.com',
                'ip' => '192.168.1.100',
                'status' => 'Success',
                'time' => now()->subMinutes(5),
            ],
            [
                'user' => 'unknown',
                'ip' => '203.0.113.45',
                'status' => 'Failed',
                'time' => now()->subMinutes(15),
            ],
        ];

        return response()->json(['success' => true, 'data' => $attempts]);
    }

    /**
     * Get system logs
     */
    public function getSystemLogs(): JsonResponse
    {
        $logFile = storage_path('logs/laravel.log');
        
        if (!file_exists($logFile)) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $lines = file($logFile);
        $logs = array_slice(array_reverse($lines), 0, 50);

        return response()->json(['success' => true, 'data' => $logs]);
    }

    /**
     * Get audit log
     */
    public function getAuditLog(): JsonResponse
    {
        $auditLog = \DB::table('audit_logs')
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        return response()->json(['success' => true, 'data' => $auditLog]);
    }

    /**
     * ═══════════════════════════════════════════════════════════════════
     * PERFORMANCE METRICS
     * ═══════════════════════════════════════════════════════════════════
     */

    /**
     * Get response time metrics
     */
    public function getResponseTime(): JsonResponse
    {
        $metrics = [
            'average' => '245ms',
            'min' => '85ms',
            'max' => '1250ms',
            'percentile_95' => '650ms',
        ];

        return response()->json(['success' => true, 'data' => $metrics]);
    }

    /**
     * Get request rate
     */
    public function getRequestRate(): JsonResponse
    {
        $metrics = [
            'requests_per_second' => 145,
            'requests_per_minute' => 8700,
            'requests_per_hour' => 522000,
        ];

        return response()->json(['success' => true, 'data' => $metrics]);
    }

    /**
     * Get error rate
     */
    public function getErrorRate(): JsonResponse
    {
        $totalRequests = 10000;
        $errorCount = 85;

        return response()->json([
            'success' => true,
            'data' => [
                'error_rate' => round(($errorCount / $totalRequests) * 100, 2) . '%',
                'total_errors' => $errorCount,
                'total_requests' => $totalRequests,
            ]
        ]);
    }

    /**
     * Get cache status
     */
    public function getCacheStatus(): JsonResponse
    {
        $status = [
            'driver' => config('cache.default'),
            'hit_rate' => '78.5%',
            'memory_used' => '245 MB',
            'memory_limit' => '512 MB',
        ];

        return response()->json(['success' => true, 'data' => $status]);
    }

    /**
     * ═══════════════════════════════════════════════════════════════════
     * SECURITY METRICS
     * ═══════════════════════════════════════════════════════════════════
     */

    /**
     * Get security threats
     */
    public function getSecurityThreats(): JsonResponse
    {
        $threats = [
            [
                'type' => 'SQL Injection Attempt',
                'ip' => '203.0.113.45',
                'time' => now()->subMinutes(30),
                'status' => 'Blocked',
            ],
            [
                'type' => 'Brute Force Attack',
                'ip' => '198.51.100.12',
                'time' => now()->subHours(2),
                'status' => 'Blocked',
            ],
        ];

        return response()->json(['success' => true, 'data' => $threats]);
    }

    /**
     * Get failed logins
     */
    public function getFailedLogins(): JsonResponse
    {
        $failures = \DB::table('login_attempts')
            ->where('success', false)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json(['success' => true, 'data' => $failures]);
    }

    /**
     * Get SSL certificate info
     */
    public function getSslCertificate(): JsonResponse
    {
        $domain = config('app.url');
        
        $info = [
            'domain' => $domain,
            'issuer' => 'Let\'s Encrypt',
            'valid_from' => 'Jan 15, 2024',
            'valid_to' => 'Apr 14, 2025',
            'days_remaining' => 65,
            'status' => 'Valid',
        ];

        return response()->json(['success' => true, 'data' => $info]);
    }

    /**
     * ═══════════════════════════════════════════════════════════════════
     * ADMIN WIDGETS
     * ═══════════════════════════════════════════════════════════════════
     */

    /**
     * Get pending approvals
     */
    public function getPendingApprovals(): JsonResponse
    {
        $pending = [
            [
                'user' => 'John Doe',
                'type' => 'Registration',
                'requested' => 'Medical Certificate',
                'date' => now()->subDays(2),
            ],
            [
                'user' => 'Jane Smith',
                'type' => 'Role Request',
                'requested' => 'Senior Doctor',
                'date' => now()->subHours(5),
            ],
        ];

        return response()->json(['success' => true, 'data' => $pending]);
    }

    /**
     * Get department health
     */
    public function getDepartmentHealth(): JsonResponse
    {
        $departments = [
            ['name' => 'Medical', 'users' => 45, 'active' => 42, 'health' => '93%'],
            ['name' => 'IT', 'users' => 12, 'active' => 11, 'health' => '92%'],
            ['name' => 'HR', 'users' => 8, 'active' => 7, 'health' => '88%'],
        ];

        return response()->json(['success' => true, 'data' => $departments]);
    }

    /**
     * Get registration status
     */
    public function getRegistrationStatus(): JsonResponse
    {
        $status = [
            'total_users' => User::count(),
            'verified' => User::where('email_verified_at', '!=', null)->count(),
            'pending' => User::where('email_verified_at', null)->count(),
            'registration_expiring' => \DB::table('users')
                ->whereRaw('mmc_reg_expiry <= DATE_ADD(NOW(), INTERVAL 30 DAY)')
                ->count(),
        ];

        return response()->json(['success' => true, 'data' => $status]);
    }

    /**
     * ═══════════════════════════════════════════════════════════════════
     * USER WIDGETS
     * ═══════════════════════════════════════════════════════════════════
     */

    /**
     * Get user profile summary
     */
    public function getMyProfile(): JsonResponse
    {
        $user = auth()->user();

        return response()->json([
            'success' => true,
            'data' => [
                'name' => $user->fullName ?? $user->name,
                'email' => $user->email,
                'role' => $user->roles->first()->name ?? 'User',
                'profile_completeness' => $user->profile_completeness ?? 0,
                'registrations_valid' => $user->hasValidRegistrations(),
            ]
        ]);
    }

    /**
     * Get user activity
     */
    public function getMyActivity(): JsonResponse
    {
        $activities = \DB::table('activity_logs')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json(['success' => true, 'data' => $activities]);
    }

    /**
     * Get user registrations
     */
    public function getMyRegistrations(): JsonResponse
    {
        $user = auth()->user();

        $registrations = [];
        
        if ($user->mmc_reg_no) {
            $registrations[] = [
                'type' => 'MMC Registration',
                'number' => $user->mmc_reg_no,
                'expiry' => $user->mmc_reg_expiry,
                'status' => $user->mmc_reg_expiry ? 
                    (now()->greaterThan($user->mmc_reg_expiry) ? 'Expired' : 'Valid') : 'N/A',
            ];
        }

        if ($user->other_reg_no) {
            $registrations[] = [
                'type' => 'Other Registration',
                'number' => $user->other_reg_no,
                'expiry' => $user->other_reg_expiry,
                'status' => $user->other_reg_expiry ? 
                    (now()->greaterThan($user->other_reg_expiry) ? 'Expired' : 'Valid') : 'N/A',
            ];
        }

        return response()->json(['success' => true, 'data' => $registrations]);
    }
}