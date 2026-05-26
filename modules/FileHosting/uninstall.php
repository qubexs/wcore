<?php
// uninstall.php
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// ============================================================
// ⚡ Configuration
// ============================================================
// By default, delete tables when uninstalling
$deleteTables = $deleteTables ?? true;

// ============================================================
// DELETE MENUS
// ============================================================
if (Schema::hasTable('menus')) {
    DB::table('menus')->where('slug','file-hosting')->delete();
    echo "🗑 Menus deleted.\n";
}

// ============================================================
// DELETE PERMISSIONS
// ============================================================
use Spatie\Permission\Models\Permission;

$permissions = Permission::where('name','like','filehosting.%')->get();
foreach($permissions as $p) { $p->delete(); }
echo "🗑 Permissions deleted.\n";

// ============================================================
// DROP TABLES (if requested)
// ============================================================
if ($deleteTables) {
    Schema::dropIfExists('files');
    Schema::dropIfExists('folders');
    echo "🗑 Tables dropped.\n";
} else {
    echo "ℹ Tables preserved.\n";
}

echo "\n✅ FileHosting module uninstalled.\n";