<?php
// routes\web.php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ModuleSettingsController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\DatabaseBackupController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\SiteManagementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OnlineStatusController; // ✅ Add this
use App\Http\Controllers\DashboardWidgetController; // ✅ Add this

/*
|--------------------------------------------------------------------------
| Web Routes (CORE) - HTPN Super App 
|--------------------------------------------------------------------------
|
| Guest redirects to login, authenticated users go to dashboard.
| Public pages remain accessible.
|
*/

// Root redirect
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('home')
        : redirect()->route('login');
});

// Authentication routes
Auth::routes();

// Public Pages
Route::view('/about', 'about')->name('about');


        /*
        |--------------------------------------------------------------------------
        | Auto Load Module Routes
        |--------------------------------------------------------------------------
        */
        $modulesPath = app_path('Modules');

        if (is_dir($modulesPath)) {

            foreach (scandir($modulesPath) as $module) {

                if ($module === '.' || $module === '..') {
                    continue;
                }

                $routeFile = $modulesPath . '/' . $module . '/routes/web.php';

                if (file_exists($routeFile)) {
                    require $routeFile;
                }

            }

        }


// Authenticated routes
Route::middleware(['auth'])->group(function () {

    // Force logout - clears everything (cache, cookies, session, traces)
    Route::get('/signout', function () {
        Auth::logout();
        
        // Invalidate session completely
        request()->session()->flush();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        
        // Clear all cookies
        foreach (request()->cookie() as $key => $value) {
            \Cookie::queue(\Cookie::forget($key));
        }
        
        // Clear application cache
        \Illuminate\Support\Facades\Cache::flush();
        
        // Prevent browser caching - send no-cache headers
        $response = redirect('/');
        $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->header('Pragma', 'no-cache');
        $response->header('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');
        $response->header('X-UA-Compatible', 'IE=edge');
        
        return $response;
    })->middleware('auth')->name('logout');
        
    // Dashboard
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // User Profile
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('profile');
        Route::put('/', [ProfileController::class, 'update'])->name('profile.update');
    });

    // Settings - Only users with 'manage settings'
    Route::prefix('settings')->middleware('can:manage settings')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/', [SettingController::class, 'update'])->name('settings.update');
    });

    // Role Management - 'manage roles' permission
    Route::middleware('can:manage roles')->group(function () {
        Route::resource('roles', RoleController::class);
    });

    // User Management - 'manage users' permission
    Route::get('/users', [UserController::class, 'index'])
        ->middleware('can:manage users')
        ->name('users.index');

    // Module Management - 'manage modules' permission
    Route::prefix('modules')->middleware('can:manage modules')->group(function () {

        // List modules
        Route::get('/', [ModuleController::class, 'index'])->name('modules.index');

        // Install new module
        Route::post('/install', [ModuleController::class, 'install'])->name('modules.install');

        // Activate / Deactivate
        Route::post('/activate/{slug}', [ModuleController::class, 'activate'])->name('modules.activate');
        Route::post('/deactivate/{slug}', [ModuleController::class, 'deactivate'])->name('modules.deactivate');

        // Lock / Unlock module
        Route::post('/lock/{slug}', [ModuleController::class, 'lock'])->name('modules.lock');
        Route::post('/unlock/{slug}', [ModuleController::class, 'unlock'])->name('modules.unlock');

        // Module Settings
        Route::get('/{slug}/settings', [ModuleSettingsController::class, 'index'])->name('modules.settings');
        
        // Module Update
        Route::post('/update/{slug}', [ModuleController::class, 'update'])->name('modules.update');

        // Uninstall module
        Route::delete('/uninstall/{slug}', [ModuleController::class, 'uninstall'])->name('modules.uninstall');

    });

    // Database Backup
    Route::get('/database/backup', [DatabaseBackupController::class, 'index'])
        ->name('database.backup');

    Route::post('/database/backup/run', [DatabaseBackupController::class, 'run'])
        ->name('database.backup.run');

    // Restore
    Route::post('/database/restore', [DatabaseBackupController::class, 'restore'])
        ->name('database.restore');

    Route::get('/database/download/{file}', [DatabaseBackupController::class, 'download'])
        ->name('database.backup.download');

    Route::get('/website/update', function () {
        return view('website.update');
    })->name('website.update');

    // Site management
    Route::get('/site-management', [SiteManagementController::class, 'index'])
        ->middleware('can:manage settings')
        ->name('site.management');

        // ============================================
        // WEBSITE BACKUP ROUTES
        // ============================================
        Route::prefix('backup')->middleware('can:manage settings')->group(function () {

            // Backup page
            Route::get('/', [BackupController::class, 'index'])
                ->name('website.backup.zip');

            // Run backup
            Route::post('/run', [BackupController::class, 'run'])
                ->name('website.backup.run');

            // Download backup
            Route::get('/download/{file}', [BackupController::class, 'download'])
                ->where('file', '.+')
                ->name('website.backup.download');

            // Delete backup — plain POST avoids @method('DELETE') + .zip URL conflicts
            Route::post('/delete/{file}', [BackupController::class, 'delete'])
                ->where('file', '.+')
                ->name('website.backup.delete');

            // Restore from external ZIP upload (Card 2)
            Route::post('/restore/upload', [BackupController::class, 'restoreUpload'])
                ->name('website.backup.restore.upload');

            Route::post('/restore/execute', [BackupController::class, 'restoreExecute'])
                ->name('website.backup.restore.execute');

            // Restore from local backup list (Card 3)
            Route::post('/restore/local', [BackupController::class, 'restoreLocal'])
                ->name('website.backup.restore.local');
                
            // AJAX status check for long-running restore (Card 2 & 3)
            Route::post('/backup/run-ajax', [BackupController::class, 'runAjax'])
                ->name('website.backup.run.ajax');    

        });   

                // Nav-mode preference (used by nav-mode-switcher widget in both sidebars)
            Route::post('/settings/nav-mode', [\App\Http\Controllers\NavModeController::class, 'store'])
                ->name('settings.navMode');

            Route::get('/settings/nav-mode', [\App\Http\Controllers\NavModeController::class, 'show'])
                ->name('settings.navMode.show');

            Route::post('/dashboard/layout/save', [DashboardController::class, 'saveLayout'])
                ->name('dashboard.layout.save');
            
            Route::get('/dashboard/layout/get', [DashboardController::class, 'getLayout'])
                ->name('dashboard.layout.get');
            
            // Dashboard Profile Management
            Route::get('/dashboard/profiles', [DashboardController::class, 'getProfiles'])
                ->name('dashboard.profiles.get');
            
            Route::post('/dashboard/profiles', [DashboardController::class, 'createProfile'])
                ->name('dashboard.profiles.create');
            
            Route::get('/dashboard/profiles/{id}/load', [DashboardController::class, 'loadProfile'])
                ->name('dashboard.profiles.load');
            
            Route::delete('/dashboard/profiles/{id}', [DashboardController::class, 'deleteProfile'])
                ->name('dashboard.profiles.delete');
            
            Route::post('/dashboard/profiles/{id}/default', [DashboardController::class, 'setDefaultProfile'])
                ->name('dashboard.profiles.setDefault');
            
            Route::post('/dashboard/profiles/{id}/duplicate', [DashboardController::class, 'duplicateProfile'])
                ->name('dashboard.profiles.duplicate');
            
            // Dashboard Stats API (for real-time widget updates)
            Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])
                ->name('dashboard.stats.get');


        // ============================================
        // MESSAGE ROUTES - WITH UNREAD COUNTER
        // ============================================
        Route::prefix('messages')->group(function () {
            
            // Main message pages
            Route::get('/', [MessageController::class, 'index'])->name('messages.index');
            Route::get('/{conversation}', [MessageController::class, 'show'])->name('messages.show');
            
            // Actions
            Route::post('/store', [MessageController::class, 'store'])->name('messages.store');
            Route::post('/{conversation}/send', [MessageController::class, 'sendMessage'])->name('messages.send');
            
            // API endpoints for unread counter (used by header)
            Route::get('/api/unread-count', [MessageController::class, 'getUnreadCount'])->name('messages.unread-count');
            Route::get('/api/recent', [MessageController::class, 'getRecentMessages'])->name('messages.recent');
            Route::post('/{message}/mark-read', [MessageController::class, 'markAsRead'])->name('messages.mark-read');
        });


            // User Management - 'manage users' permission
        Route::middleware('can:manage users')->group(function () {
            
            // List users
            Route::get('/users', [UserController::class, 'index'])->name('users.index');
            
            // Create user
            Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('/users', [UserController::class, 'store'])->name('users.store');
            
            // Edit user
            Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
            
            // Toggle status (activate/deactivate)
            Route::post('/users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
            
            // Show user (for AJAX modal)
            Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
            
        });

        Route::middleware(['auth', 'verified'])->group(function () {
            
            // Existing user management routes
            Route::resource('users', UserController::class);
            
            // NEW: Activity logging routes
            Route::get('/users/{user}/activity', [UserController::class, 'activityLog'])
                ->name('users.activity');
            
            Route::get('/activity', [UserController::class, 'globalActivity'])
                ->name('users.global-activity');
        });

    Route::middleware(['auth:sanctum'])->prefix('user')->name('user.')->group(function () {
    
    /**
     * User Status Endpoints
     */
    Route::prefix('status')->name('status.')->group(function () {
        // Get current user's status
        Route::get('/', [OnlineStatusController::class, 'getStatus'])
            ->name('get');
        
        // Update presence status (online, away, busy, offline)
        Route::post('/presence', [OnlineStatusController::class, 'updatePresence'])
            ->name('presence');
        
        // Mark as online
        Route::post('/online', [OnlineStatusController::class, 'markOnline'])
            ->name('online');
        
        // Mark as offline
        Route::post('/offline', [OnlineStatusController::class, 'markOffline'])
            ->name('offline');
        
        // Ping to keep alive
        Route::post('/ping', [OnlineStatusController::class, 'ping'])
            ->name('ping');
    });
    
});

    Route::middleware(['auth:sanctum'])->prefix('users')->name('users.')->group(function () {
        
        /**
         * Users Status Endpoints (check other users)
         */
        Route::prefix('status')->name('status.')->group(function () {
            
            // Get all online users
            Route::get('/online', [OnlineStatusController::class, 'getOnlineUsers'])
                ->name('online');
            
            // Get all offline users
            Route::get('/offline', [OnlineStatusController::class, 'getOfflineUsers'])
                ->name('offline');
            
            // Get online/offline statistics
            Route::get('/stats', [OnlineStatusController::class, 'getStats'])
                ->name('stats');
            
            // Get all users with status
            Route::get('/all', [OnlineStatusController::class, 'getAllUsers'])
                ->name('all');
            
            // Check specific user's status
            Route::get('/{userId}/status', [OnlineStatusController::class, 'checkUserStatus'])
                ->name('check');
        });
        
    }); 
    
    // ═══════════════════════════════════════════════════════════════════════════
    // ROLE-BASED DASHBOARD WIDGET ROUTES
    // 
    // Add to: routes/api.php or routes/web.php (authenticated middleware)
    // ═══════════════════════════════════════════════════════════════════════════

    Route::middleware(['auth'])->prefix('api/widgets')->name('widgets.')->group(function () {

        // ─────────────────────────────────────────────────────────────────
        // SUPER ADMIN WIDGETS - Server Monitoring
        // ─────────────────────────────────────────────────────────────────
        Route::middleware('permission:view server stats')->group(function () {
            Route::get('/server-health', [DashboardWidgetController::class, 'getServerHealth'])->name('server-health');
            Route::get('/database-health', [DashboardWidgetController::class, 'getDatabaseHealth'])->name('database-health');
            Route::get('/slow-queries', [DashboardWidgetController::class, 'getSlowQueries'])->name('slow-queries');
            Route::get('/error-logs', [DashboardWidgetController::class, 'getErrorLogs'])->name('error-logs');
            Route::get('/activity-timeline', [DashboardWidgetController::class, 'getActivityTimeline'])->name('activity-timeline');
            Route::get('/login-attempts', [DashboardWidgetController::class, 'getLoginAttempts'])->name('login-attempts');
            Route::get('/system-logs', [DashboardWidgetController::class, 'getSystemLogs'])->name('system-logs');
            Route::get('/audit-log', [DashboardWidgetController::class, 'getAuditLog'])->name('audit-log');
        });

        // Performance Metrics
        Route::middleware('permission:view server stats')->group(function () {
            Route::get('/response-time', [DashboardWidgetController::class, 'getResponseTime'])->name('response-time');
            Route::get('/request-rate', [DashboardWidgetController::class, 'getRequestRate'])->name('request-rate');
            Route::get('/error-rate', [DashboardWidgetController::class, 'getErrorRate'])->name('error-rate');
            Route::get('/cache-status', [DashboardWidgetController::class, 'getCacheStatus'])->name('cache-status');
        });

        // Security Metrics
        Route::middleware('permission:view server stats')->group(function () {
            Route::get('/security-threats', [DashboardWidgetController::class, 'getSecurityThreats'])->name('security-threats');
            Route::get('/failed-logins', [DashboardWidgetController::class, 'getFailedLogins'])->name('failed-logins');
            Route::get('/ssl-certificate', [DashboardWidgetController::class, 'getSslCertificate'])->name('ssl-certificate');
        });

        // ─────────────────────────────────────────────────────────────────
        // ADMIN WIDGETS
        // ─────────────────────────────────────────────────────────────────
        Route::middleware('permission:manage users')->group(function () {
            Route::get('/pending-approvals', [DashboardWidgetController::class, 'getPendingApprovals'])->name('pending-approvals');
            Route::get('/department-health', [DashboardWidgetController::class, 'getDepartmentHealth'])->name('department-health');
            Route::get('/registration-status', [DashboardWidgetController::class, 'getRegistrationStatus'])->name('registration-status');
        });

        // ─────────────────────────────────────────────────────────────────
        // USER WIDGETS - Personal
        // ─────────────────────────────────────────────────────────────────
        Route::get('/my-profile', [DashboardWidgetController::class, 'getMyProfile'])->name('my-profile');
        Route::get('/my-activity', [DashboardWidgetController::class, 'getMyActivity'])->name('my-activity');
        Route::get('/my-registrations', [DashboardWidgetController::class, 'getMyRegistrations'])->name('my-registrations');

    });
            
        // Temporary backup download routes (until controllers are implemented)
        Route::get('/database/backup/download/{filename}', function($filename) {
            return back()->with('info', 'Database backup download coming soon');
        })->name('database.backup.download');

        Route::get('/website/backup/download/{filename}', function($filename) {
            return back()->with('info', 'Website backup download coming soon');
        })->name('website.backup.download');






});