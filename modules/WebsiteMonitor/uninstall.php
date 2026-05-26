<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

$deleteTables = $deleteTables ?? true;

if (Schema::hasTable('menus')) {
    DB::table('menus')->where('module_slug', 'websitemonitor')->delete();
    echo "🗑 Menus deleted.\n";
}

$permissions = Permission::where('name', 'like', 'websitemonitor.%')->get();
foreach ($permissions as $p) {
    $p->delete();
}
echo "🗑 Permissions deleted.\n";

if ($deleteTables) {
    Schema::dropIfExists('monitor_alerts');
    Schema::dropIfExists('monitor_logs');
    Schema::dropIfExists('monitor_targets');
    echo "🗑 Tables dropped.\n";
} else {
    echo "ℹ Tables preserved.\n";
}

echo "\n✅ WebsiteMonitor module uninstalled.\n";
