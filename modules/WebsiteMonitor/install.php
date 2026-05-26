<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

$keepData = $keepData ?? false;

function addColumnIfMissing(string $table, string $column, callable $callback): void
{
    if (!Schema::hasColumn($table, $column)) {
        Schema::table($table, $callback);
        echo "➕ Column '{$column}' added to '{$table}'.\n";
    }
}

// ─── MONITOR TARGETS ──────────────────────────────────────────────

if (!Schema::hasTable('monitor_targets')) {

    Schema::create('monitor_targets', function (Blueprint $table) {
        $table->id();
        $table->string('name', 255);
        $table->string('url', 2048);
        $table->string('method', 10)->default('GET');
        $table->integer('check_interval')->default(5);
        $table->integer('timeout')->default(10);
        $table->integer('expected_status')->default(200);
        $table->string('check_string', 500)->nullable();
        $table->boolean('is_active')->default(true);
        $table->boolean('alert_on_down')->default(true);
        $table->string('alert_methods', 50)->default('message');
        $table->unsignedBigInteger('pic_user_id')->nullable();
        $table->timestamp('last_checked_at')->nullable();
        $table->integer('last_status')->nullable();
        $table->float('last_response_time')->nullable();
        $table->text('last_error')->nullable();
        $table->unsignedBigInteger('created_by');
        $table->timestamps();

        $table->index('is_active');
        $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('pic_user_id')->references('id')->on('users')->onDelete('set null');
    });

    echo "✔ Table 'monitor_targets' created.\n";

} else {
    echo "ℹ Table 'monitor_targets' exists, checking columns...\n";

    addColumnIfMissing('monitor_targets', 'name',              fn($t) => $t->string('name', 255));
    addColumnIfMissing('monitor_targets', 'url',               fn($t) => $t->string('url', 2048));
    addColumnIfMissing('monitor_targets', 'method',            fn($t) => $t->string('method', 10)->default('GET'));
    addColumnIfMissing('monitor_targets', 'check_interval',    fn($t) => $t->integer('check_interval')->default(5));
    addColumnIfMissing('monitor_targets', 'timeout',           fn($t) => $t->integer('timeout')->default(10));
    addColumnIfMissing('monitor_targets', 'expected_status',   fn($t) => $t->integer('expected_status')->default(200));
    addColumnIfMissing('monitor_targets', 'check_string',      fn($t) => $t->string('check_string', 500)->nullable());
    addColumnIfMissing('monitor_targets', 'is_active',         fn($t) => $t->boolean('is_active')->default(true));
    addColumnIfMissing('monitor_targets', 'alert_on_down',     fn($t) => $t->boolean('alert_on_down')->default(true));
    addColumnIfMissing('monitor_targets', 'alert_methods',     fn($t) => $t->string('alert_methods', 50)->default('message'));
    addColumnIfMissing('monitor_targets', 'pic_user_id',       fn($t) => $t->unsignedBigInteger('pic_user_id')->nullable());
    addColumnIfMissing('monitor_targets', 'last_checked_at',   fn($t) => $t->timestamp('last_checked_at')->nullable());
    addColumnIfMissing('monitor_targets', 'last_status',       fn($t) => $t->integer('last_status')->nullable());
    addColumnIfMissing('monitor_targets', 'last_response_time', fn($t) => $t->float('last_response_time')->nullable());
    addColumnIfMissing('monitor_targets', 'last_error',        fn($t) => $t->text('last_error')->nullable());
    addColumnIfMissing('monitor_targets', 'created_by',        fn($t) => $t->unsignedBigInteger('created_by')->default(0));

    if (!Schema::hasColumn('monitor_targets', 'pic_user_id')) {
        // no-op, already handled above
    }
}

// ─── MONITOR LOGS ─────────────────────────────────────────────────

if (!Schema::hasTable('monitor_logs')) {

    Schema::create('monitor_logs', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('monitor_target_id');
        $table->integer('status_code')->nullable();
        $table->float('response_time')->nullable();
        $table->text('error_message')->nullable();
        $table->unsignedBigInteger('checked_by')->nullable();
        $table->timestamps();

        $table->index('monitor_target_id');
        $table->index('created_at');
        $table->foreign('monitor_target_id')->references('id')->on('monitor_targets')->onDelete('cascade');
        $table->foreign('checked_by')->references('id')->on('users')->onDelete('set null');
    });

    echo "✔ Table 'monitor_logs' created.\n";

} else {
    echo "ℹ Table 'monitor_logs' exists, checking columns...\n";

    addColumnIfMissing('monitor_logs', 'monitor_target_id', fn($t) => $t->unsignedBigInteger('monitor_target_id'));
    addColumnIfMissing('monitor_logs', 'status_code',       fn($t) => $t->integer('status_code')->nullable());
    addColumnIfMissing('monitor_logs', 'response_time',     fn($t) => $t->float('response_time')->nullable());
    addColumnIfMissing('monitor_logs', 'error_message',     fn($t) => $t->text('error_message')->nullable());
    addColumnIfMissing('monitor_logs', 'checked_by',        fn($t) => $t->unsignedBigInteger('checked_by')->nullable());
}

// ─── MONITOR ALERTS ───────────────────────────────────────────────

if (!Schema::hasTable('monitor_alerts')) {

    Schema::create('monitor_alerts', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('monitor_target_id');
        $table->string('alert_type', 50)->default('message');
        $table->unsignedBigInteger('sent_to_user_id')->nullable();
        $table->string('sent_to_email', 255)->nullable();
        $table->string('subject', 500)->nullable();
        $table->text('message')->nullable();
        $table->timestamp('sent_at')->nullable();
        $table->timestamps();

        $table->index('monitor_target_id');
        $table->foreign('monitor_target_id')->references('id')->on('monitor_targets')->onDelete('cascade');
        $table->foreign('sent_to_user_id')->references('id')->on('users')->onDelete('set null');
    });

    echo "✔ Table 'monitor_alerts' created.\n";

} else {
    echo "ℹ Table 'monitor_alerts' exists, skipping.\n";
}

// ─── MENU ─────────────────────────────────────────────────────────

if (Schema::hasTable('menus')) {

    $menuExists = DB::table('menus')->where('module_slug', 'websitemonitor')->exists();

    if (!$menuExists) {

        $menuId = DB::table('menus')->insertGetId([
            'title'       => 'Website Monitor',
            'route'       => 'websitemonitor.index',
            'icon'        => 'fas fa-heartbeat',
            'permission'  => null,
            'parent_id'   => null,
            'order'       => 1,
            'is_active'   => 1,
            'is_locked'   => 0,
            'module_slug' => 'websitemonitor',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        DB::table('menus')->insert([
            [
                'title'       => 'Dashboard',
                'route'       => 'websitemonitor.index',
                'icon'        => 'fas fa-tachometer-alt',
                'permission'  => null,
                'parent_id'   => $menuId,
                'order'       => 0,
                'is_active'   => 1,
                'is_locked'   => 0,
                'module_slug' => 'websitemonitor',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'title'       => 'Add Target',
                'route'       => 'websitemonitor.create',
                'icon'        => 'fas fa-plus-circle',
                'permission'  => 'websitemonitor.create',
                'parent_id'   => $menuId,
                'order'       => 1,
                'is_active'   => 1,
                'is_locked'   => 0,
                'module_slug' => 'websitemonitor',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'title'       => 'Settings',
                'route'       => 'websitemonitor.settings',
                'icon'        => 'fas fa-cog',
                'permission'  => 'websitemonitor.manage_settings',
                'parent_id'   => $menuId,
                'order'       => 2,
                'is_active'   => 1,
                'is_locked'   => 0,
                'module_slug' => 'websitemonitor',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);

        $deptIds  = DB::table('departments')->pluck('id');
        $childIds = DB::table('menus')->where('parent_id', $menuId)->pluck('id')->toArray();
        $allIds   = array_merge([$menuId], $childIds);

        foreach ($deptIds as $deptId) {
            foreach ($allIds as $mid) {
                DB::table('department_menu')->updateOrInsert(
                    ['department_id' => $deptId, 'menu_id' => $mid],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        echo "✔ Menu 'Website Monitor' added.\n";

    } else {
        echo "ℹ Menu 'Website Monitor' exists, skipping.\n";
    }
}

// ─── PERMISSIONS ──────────────────────────────────────────────────

$permissions = include __DIR__ . '/permissions.php';

foreach ($permissions['roles'] ?? [] as $roleName => $groups) {
    $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

    foreach ($groups as $group => $perms) {
        foreach ($perms as $perm) {
            $p = Permission::firstOrCreate([
                'name'       => "{$group}.{$perm}",
                'guard_name' => 'web',
            ]);
            $role->givePermissionTo($p);
        }
    }
}

echo "✔ Permissions applied.\n";
echo "\n✅ WebsiteMonitor module installed successfully!\n";
