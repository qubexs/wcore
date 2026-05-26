<?php
// 2026_01_26_000002_create_dashboard_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1️⃣ Create the menus table
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('route')->nullable();
            $table->string('icon')->nullable();
            $table->string('permission')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2️⃣ Insert initial menu data
        $dashboardId = DB::table('menus')->insertGetId([
            'title' => 'Dashboard',
            'route' => 'home',
            'icon' => 'fas fa-fw fa-tachometer-alt',
            'order' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $settingsId = DB::table('menus')->insertGetId([
            'title' => 'Settings',
            'icon' => 'fas fa-cogs',
            'permission' => 'manage settings',
            'order' => 2,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Children of Settings
        DB::table('menus')->insert([
            [
                'title' => 'Module Management',
                'route' => 'modules.index',
                'icon' => 'fas fa-th-large',
                'permission' => 'manage settings',
                'parent_id' => $settingsId,
                'order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Role Management',
                'route' => 'roles.index',
                'icon' => 'fas fa-user-shield',
                'permission' => 'manage settings',
                'parent_id' => $settingsId,
                'order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'User Management',
                'route' => 'users.index',
                'icon' => 'fas fa-users',
                'permission' => 'manage settings',
                'parent_id' => $settingsId,
                'order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Profile & About
        DB::table('menus')->insert([
            [
                'title' => 'Profile',
                'route' => 'profile',
                'icon' => 'fas fa-user',
                'order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'About',
                'route' => 'about',
                'icon' => 'fas fa-info-circle',
                'order' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
