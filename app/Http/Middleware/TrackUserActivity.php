<?php

/**
 * MIDDLEWARE - Track User Online/Offline Status
 * 
 * File: app/Http/Middleware/TrackUserActivity.php
 * 
 * Add to app/Http/Kernel.php in $middlewareGroups['web']:
 * \App\Http\Middleware\TrackUserActivity::class,
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TrackUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Only track authenticated users
        if (auth()->check()) {
            $user = auth()->user();

            // Skip AJAX requests that are only for checking status
            if (!$request->is('api/user/status', 'user/status-check')) {
                // Update last activity
                $user->updateActivity();

                // Update device info
                $user->updateDeviceInfo();

                // Mark as online
                if (!$user->is_online) {
                    $user->markOnline();
                }
            }
        }

        return $next($request);
    }
}