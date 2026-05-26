<?php

/**
 * CONTROLLER - User Online Status Management
 * 
 * File: app/Http/Controllers/OnlineStatusController.php
 */

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OnlineStatusController extends Controller
{
    /**
     * Get current user's online status
     */
    public function getStatus(): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'is_online' => $user->is_online,
                'presence_status' => $user->presence_status,
                'device' => $user->current_device,
                'browser' => $user->getBrowserName(),
                'last_activity' => $user->last_activity?->toIso8601String(),
                'last_seen' => $user->last_seen?->toIso8601String(),
                'formatted_status' => $user->getFormattedStatus(),
            ]
        ]);
    }

    /**
     * Update user's presence status (online, away, busy, offline)
     * 
     * Request: POST /api/user/presence
     * {
     *    "status": "away|busy|online|offline"
     * }
     */
    public function updatePresence(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:online,away,busy,offline'
        ]);

        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $user->setPresenceStatus($validated['status']);

        return response()->json([
            'success' => true,
            'message' => "Status changed to {$validated['status']}",
            'data' => [
                'presence_status' => $user->presence_status,
                'presence_color' => $user->getPresenceColor(),
                'presence_icon' => $user->getPresenceIcon(),
            ]
        ]);
    }

    /**
     * Mark user as online (manual trigger)
     * 
     * Request: POST /api/user/online
     */
    public function markOnline(): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $user->markOnline();

        return response()->json([
            'success' => true,
            'message' => 'Marked as online',
        ]);
    }

    /**
     * Mark user as offline (manual trigger)
     * 
     * Request: POST /api/user/offline
     */
    public function markOffline(): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $user->markOffline();

        return response()->json([
            'success' => true,
            'message' => 'Marked as offline',
        ]);
    }

    /**
     * Get list of online users
     * 
     * Request: GET /api/users/online?limit=10
     */
    public function getOnlineUsers(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);

        $users = User::getOnlineUsers($limit)
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->fullName ?? $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'status' => $user->presence_status,
                    'device' => $user->current_device,
                    'browser' => $user->getBrowserName(),
                    'formatted_status' => $user->getFormattedStatus(),
                ];
            });

        return response()->json([
            'success' => true,
            'count' => $users->count(),
            'data' => $users
        ]);
    }

    /**
     * Get list of offline users
     * 
     * Request: GET /api/users/offline?limit=10
     */
    public function getOfflineUsers(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);

        $users = User::getOfflineUsers($limit)
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->fullName ?? $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'last_seen' => $user->last_seen?->toIso8601String(),
                    'last_seen_formatted' => $user->last_seen?->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'count' => $users->count(),
            'data' => $users
        ]);
    }

    /**
     * Get online/offline statistics
     * 
     * Request: GET /api/users/stats
     */
    public function getStats(): JsonResponse
    {
        $totalUsers = User::count();
        $onlineUsers = User::getOnlineCount();
        $offlineUsers = $totalUsers - $onlineUsers;

        return response()->json([
            'success' => true,
            'stats' => [
                'total' => $totalUsers,
                'online' => $onlineUsers,
                'offline' => $offlineUsers,
                'online_percentage' => $totalUsers > 0 ? round(($onlineUsers / $totalUsers) * 100, 2) : 0,
            ]
        ]);
    }

    /**
     * Get all users with their online status
     * 
     * Request: GET /api/users/all
     */
    public function getAllUsers(): JsonResponse
    {
        $users = User::orderBy('last_activity', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->fullName ?? $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'is_online' => $user->is_online,
                    'presence_status' => $user->presence_status,
                    'presence_color' => $user->getPresenceColor(),
                    'presence_icon' => $user->getPresenceIcon(),
                    'device' => $user->current_device,
                    'device_icon' => $user->getDeviceIcon(),
                    'browser' => $user->getBrowserName(),
                    'last_activity' => $user->last_activity?->toIso8601String(),
                    'last_seen_formatted' => $user->is_online ? 'Now' : $user->last_seen?->diffForHumans(),
                    'formatted_status' => $user->getFormattedStatus(),
                ];
            });

        return response()->json([
            'success' => true,
            'count' => $users->count(),
            'data' => $users
        ]);
    }

    /**
     * Ping to keep user alive
     * Called periodically from JavaScript to keep user session active
     * 
     * Request: POST /api/user/ping
     */
    public function ping(): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $user->updateActivity();

        return response()->json([
            'success' => true,
            'message' => 'Pong',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Check if specific user is online
     * 
     * Request: GET /api/users/{userId}/status
     */
    public function checkUserStatus($userId): JsonResponse
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->fullName ?? $user->name,
                'is_online' => $user->is_online,
                'presence_status' => $user->presence_status,
                'presence_color' => $user->getPresenceColor(),
                'presence_icon' => $user->getPresenceIcon(),
                'last_seen' => $user->last_seen?->toIso8601String(),
                'formatted_status' => $user->getFormattedStatus(),
            ]
        ]);
    }
}