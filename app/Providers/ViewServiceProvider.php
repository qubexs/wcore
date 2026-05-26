<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share unread message count and recent messages with header
        View::composer('layouts.header', function ($view) {
            if (Auth::check()) {
                // Get total unread count
                $unreadCount = Message::whereHas('conversation', function($query) {
                    $query->whereHas('participants', function($q) {
                        $q->where('user_id', Auth::id());
                    });
                })
                ->where('sender_id', '!=', Auth::id())
                ->whereNull('read_at')
                ->count();

                // Get recent messages for dropdown
                $recentMessages = Message::with(['sender', 'conversation'])
                    ->whereHas('conversation', function($query) {
                        $query->whereHas('participants', function($q) {
                            $q->where('user_id', Auth::id());
                        });
                    })
                    ->where('sender_id', '!=', Auth::id())
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();

                $view->with([
                    'unreadCount' => $unreadCount,
                    'recentMessages' => $recentMessages
                ]);
            } else {
                $view->with([
                    'unreadCount' => 0,
                    'recentMessages' => collect()
                ]);
            }
        });
    }
}