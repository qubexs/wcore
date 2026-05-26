<?php

namespace App\Providers;

// app/Providers/ViewComposerServiceProvider.php

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class ViewComposerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        View::composer('layouts.*', function ($view) {
            if (Auth::check()) {
                // Get unread message count
                $unreadCount = Message::whereHas('conversation', function($query) {
                    $query->whereHas('participants', function($q) {
                        $q->where('user_id', Auth::id());
                    });
                })
                ->where('sender_id', '!=', Auth::id())
                ->whereNull('read_at') // Add this column if needed
                ->count();

                // Get recent messages (latest 4)
                $recentMessages = Message::with(['sender', 'conversation'])
                    ->whereHas('conversation', function($query) {
                        $query->whereHas('participants', function($q) {
                            $q->where('user_id', Auth::id());
                        });
                    })
                    ->where('sender_id', '!=', Auth::id())
                    ->latest()
                    ->take(4)
                    ->get();

                $view->with(compact('unreadCount', 'recentMessages'));
            }
        });
    }
}