<?php

namespace App\Providers;

use App\Models\Menu;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // No bindings
    }

    public function boot(): void
    {
        View::composer('*', function ($view) {
            if (!auth()->check()) {
                $view->with('menus', collect());
                return;
            }

            $menus = Menu::whereNull('parent_id')
                ->where('is_active', true)
                ->with('children') // Make sure Menu model has children() relationship
                ->orderBy('order')
                ->get();

            $view->with('menus', $menus);
        });
    }
}
