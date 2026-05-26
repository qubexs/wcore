<?php
// install.php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

$keepData = $keepData ?? false;

/*
|--------------------------------------------------------------------------
| Helper
|--------------------------------------------------------------------------
*/
function addColumnIfMissing(string $table, string $column, callable $callback): void
{
    if (!Schema::hasColumn($table, $column)) {
        Schema::table($table, $callback);
        echo "➕ Column '{$column}' added to '{$table}'.\n";
    }
}

/*
|--------------------------------------------------------------------------
| 1️⃣  FOLDERS TABLE
|     Actual columns (from DB): id, parent_id, user_id, name, slug,
|     description, path, depth, visibility, timestamps, softDeletes
|--------------------------------------------------------------------------
*/
if (!Schema::hasTable('folders')) {

    Schema::create('folders', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('parent_id')->nullable();
        $table->unsignedBigInteger('user_id');           // ← user_id (not created_by)

        $table->string('name', 255);
        $table->string('slug', 255)->unique();
        $table->text('description')->nullable();
        $table->string('path', 500)->default('');        // ← safe default
        $table->integer('depth')->default(0);
        $table->enum('visibility', ['private','public','restricted'])->default('private');

        $table->timestamps();
        $table->softDeletes();

        $table->index('parent_id');
        $table->index('user_id');
        $table->index('path');

        $table->foreign('parent_id')->references('id')->on('folders')->onDelete('cascade');
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });

    echo "✔ Table 'folders' created.\n";

} else {

    echo "ℹ Table 'folders' exists, checking columns...\n";

    addColumnIfMissing('folders', 'parent_id',   fn($t) => $t->unsignedBigInteger('parent_id')->nullable());
    addColumnIfMissing('folders', 'user_id',      fn($t) => $t->unsignedBigInteger('user_id'));
    addColumnIfMissing('folders', 'slug',         fn($t) => $t->string('slug', 255)->unique());
    addColumnIfMissing('folders', 'description',  fn($t) => $t->text('description')->nullable());
    addColumnIfMissing('folders', 'path',         fn($t) => $t->string('path', 500)->default(''));
    addColumnIfMissing('folders', 'depth',        fn($t) => $t->integer('depth')->default(0));
    addColumnIfMissing('folders', 'visibility',   fn($t) => $t->enum('visibility', ['private','public','restricted'])->default('private'));
}

/*
|--------------------------------------------------------------------------
| 2️⃣  FILES TABLE
|     Actual columns (from DB): id, name, path, thumbnail, uploaded_by,
|     timestamps, folder_id, original_name, stored_name, file_path,
|     mime_type, size, extension, checksum, description, visibility,
|     expires_at, download_count, softDeletes
|
|     FIX: all NOT NULL columns now have safe defaults → no more 1364
|--------------------------------------------------------------------------
*/
if (!Schema::hasTable('files')) {

    Schema::create('files', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('folder_id')->nullable();
        $table->unsignedBigInteger('uploaded_by');       // ← uploaded_by (not user_id)

        $table->string('name', 255)->default('');
        $table->string('original_name', 255)->default('');
        $table->string('stored_name', 255)->default('');

        $table->string('path', 255)->default('');        // ← short path used by Storage::url()
        $table->string('file_path', 500)->default('');   // ← full relative path
        $table->string('thumbnail', 255)->nullable();

        $table->string('mime_type', 100)->default('application/octet-stream');
        $table->bigInteger('size')->default(0);
        $table->string('extension', 20)->default('');

        $table->string('checksum', 64)->nullable();
        $table->text('description')->nullable();
        $table->enum('visibility', ['private','public','restricted'])->default('private');

        $table->timestamp('expires_at')->nullable();
        $table->integer('download_count')->default(0);

        $table->timestamps();
        $table->softDeletes();

        $table->index('folder_id');
        $table->index('uploaded_by');
        $table->index('mime_type');

        $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
        $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
    });

    echo "✔ Table 'files' created.\n";

} else {

    echo "ℹ Table 'files' exists, checking columns...\n";

    addColumnIfMissing('files', 'folder_id',      fn($t) => $t->unsignedBigInteger('folder_id')->nullable());
    addColumnIfMissing('files', 'uploaded_by',    fn($t) => $t->unsignedBigInteger('uploaded_by')->default(0));
    addColumnIfMissing('files', 'name',           fn($t) => $t->string('name', 255)->default(''));
    addColumnIfMissing('files', 'original_name',  fn($t) => $t->string('original_name', 255)->default(''));
    addColumnIfMissing('files', 'stored_name',    fn($t) => $t->string('stored_name', 255)->default(''));
    addColumnIfMissing('files', 'path',           fn($t) => $t->string('path', 255)->default(''));
    addColumnIfMissing('files', 'file_path',      fn($t) => $t->string('file_path', 500)->default(''));
    addColumnIfMissing('files', 'thumbnail',      fn($t) => $t->string('thumbnail', 255)->nullable());
    addColumnIfMissing('files', 'mime_type',      fn($t) => $t->string('mime_type', 100)->default('application/octet-stream'));
    addColumnIfMissing('files', 'size',           fn($t) => $t->bigInteger('size')->default(0));
    addColumnIfMissing('files', 'extension',      fn($t) => $t->string('extension', 20)->default(''));
    addColumnIfMissing('files', 'checksum',       fn($t) => $t->string('checksum', 64)->nullable());
    addColumnIfMissing('files', 'description',    fn($t) => $t->text('description')->nullable());
    addColumnIfMissing('files', 'visibility',     fn($t) => $t->enum('visibility', ['private','public','restricted'])->default('private'));
    addColumnIfMissing('files', 'expires_at',     fn($t) => $t->timestamp('expires_at')->nullable());
    addColumnIfMissing('files', 'download_count', fn($t) => $t->integer('download_count')->default(0));

    // Fix existing NOT NULL columns that have no default — alter them to add defaults
    DB::statement("ALTER TABLE files MODIFY COLUMN `name`          VARCHAR(255)  NOT NULL DEFAULT ''");
    DB::statement("ALTER TABLE files MODIFY COLUMN `path`          VARCHAR(255)  NOT NULL DEFAULT ''");
    DB::statement("ALTER TABLE files MODIFY COLUMN `file_path`     VARCHAR(500)  NOT NULL DEFAULT ''");
    DB::statement("ALTER TABLE files MODIFY COLUMN `original_name` VARCHAR(255)  NOT NULL DEFAULT ''");
    DB::statement("ALTER TABLE files MODIFY COLUMN `stored_name`   VARCHAR(255)  NOT NULL DEFAULT ''");
    DB::statement("ALTER TABLE files MODIFY COLUMN `mime_type`     VARCHAR(100)  NOT NULL DEFAULT 'application/octet-stream'");
    DB::statement("ALTER TABLE files MODIFY COLUMN `size`          BIGINT(20)    NOT NULL DEFAULT 0");
    DB::statement("ALTER TABLE files MODIFY COLUMN `extension`     VARCHAR(20)   NOT NULL DEFAULT ''");

    echo "✔ 'files' column defaults fixed.\n";
}

/*
|--------------------------------------------------------------------------
| 3️⃣  FILE_VERSIONS TABLE
|     Actual columns (from DB): id, file_id, user_id ← NOT uploaded_by!
|     stored_name, file_path, size, checksum, version_number,
|     change_notes, timestamps
|
|     FIX: use user_id (not uploaded_by) — this was causing 1054 error
|--------------------------------------------------------------------------
*/
if (!Schema::hasTable('file_versions')) {

    Schema::create('file_versions', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('file_id');
        $table->unsignedBigInteger('user_id');           // ← user_id NOT uploaded_by

        $table->string('stored_name', 255)->default('');
        $table->string('file_path', 500)->default('');
        $table->bigInteger('size')->default(0);
        $table->string('checksum', 64)->default('');
        $table->integer('version_number')->default(1);
        $table->text('change_notes')->nullable();

        $table->timestamps();

        $table->unique(['file_id', 'version_number']);
        $table->index('file_id');

        $table->foreign('file_id')->references('id')->on('files')->onDelete('cascade');
    });

    echo "✔ Table 'file_versions' created.\n";

} else {

    echo "ℹ Table 'file_versions' exists, checking columns...\n";

    addColumnIfMissing('file_versions', 'file_id',        fn($t) => $t->unsignedBigInteger('file_id'));
    addColumnIfMissing('file_versions', 'user_id',        fn($t) => $t->unsignedBigInteger('user_id')->default(0));
    addColumnIfMissing('file_versions', 'stored_name',    fn($t) => $t->string('stored_name', 255)->default(''));
    addColumnIfMissing('file_versions', 'file_path',      fn($t) => $t->string('file_path', 500)->default(''));
    addColumnIfMissing('file_versions', 'size',           fn($t) => $t->bigInteger('size')->default(0));
    addColumnIfMissing('file_versions', 'checksum',       fn($t) => $t->string('checksum', 64)->default(''));
    addColumnIfMissing('file_versions', 'version_number', fn($t) => $t->integer('version_number')->default(1));
    addColumnIfMissing('file_versions', 'change_notes',   fn($t) => $t->text('change_notes')->nullable());

    // Fix column defaults
    DB::statement("ALTER TABLE file_versions MODIFY COLUMN `stored_name`    VARCHAR(255) NOT NULL DEFAULT ''");
    DB::statement("ALTER TABLE file_versions MODIFY COLUMN `file_path`      VARCHAR(500) NOT NULL DEFAULT ''");
    DB::statement("ALTER TABLE file_versions MODIFY COLUMN `size`           BIGINT(20)   NOT NULL DEFAULT 0");
    DB::statement("ALTER TABLE file_versions MODIFY COLUMN `checksum`       VARCHAR(64)  NOT NULL DEFAULT ''");
    DB::statement("ALTER TABLE file_versions MODIFY COLUMN `version_number` INT(11)      NOT NULL DEFAULT 1");

    echo "✔ 'file_versions' column defaults fixed.\n";
}

/*
|--------------------------------------------------------------------------
| 4️⃣  FILE_STATS TABLE
|     Actual columns (from DB): id, file_id, user_id ← NOT uploaded_by!
|     action, ip, user_agent, timestamps, folder_id, metadata
|--------------------------------------------------------------------------
*/
if (!Schema::hasTable('file_stats')) {

    Schema::create('file_stats', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('file_id')->nullable();
        $table->unsignedBigInteger('user_id')->nullable();   // ← user_id NOT uploaded_by
        $table->unsignedBigInteger('folder_id')->nullable();

        $table->string('action', 50);
        $table->string('ip', 45)->nullable();
        $table->text('user_agent')->nullable();
        $table->json('metadata')->nullable();

        $table->timestamps();

        $table->index('file_id');
        $table->index('user_id');

        $table->foreign('folder_id')->references('id')->on('folders')->onDelete('set null');
    });

    echo "✔ Table 'file_stats' created.\n";

} else {

    echo "ℹ Table 'file_stats' exists, checking columns...\n";

    addColumnIfMissing('file_stats', 'file_id',    fn($t) => $t->unsignedBigInteger('file_id')->nullable());
    addColumnIfMissing('file_stats', 'user_id',    fn($t) => $t->unsignedBigInteger('user_id')->nullable());
    addColumnIfMissing('file_stats', 'folder_id',  fn($t) => $t->unsignedBigInteger('folder_id')->nullable());
    addColumnIfMissing('file_stats', 'action',     fn($t) => $t->string('action', 50));
    addColumnIfMissing('file_stats', 'ip',         fn($t) => $t->string('ip', 45)->nullable());
    addColumnIfMissing('file_stats', 'user_agent', fn($t) => $t->text('user_agent')->nullable());
    addColumnIfMissing('file_stats', 'metadata',   fn($t) => $t->json('metadata')->nullable());
}

/*
|--------------------------------------------------------------------------
| 5️⃣  MENU INSERTION
|--------------------------------------------------------------------------
*/
if (Schema::hasTable('menus')) {

    $menuExists = DB::table('menus')->where('module_slug', 'filehosting')->exists();

    if (!$menuExists) {

        $menuId = DB::table('menus')->insertGetId([
            'title'       => 'File Hosting',
            'route'       => 'filehosting.index',
            'icon'        => 'fas fa-cloud-upload-alt',
            'permission'  => null,
            'parent_id'   => null,
            'order'       => 1,
            'is_active'   => 1,
            'is_locked'   => 0,
            'module_slug' => 'filehosting',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        DB::table('menus')->insert([
            [
                'title'       => 'Upload File',
                'route'       => 'filehosting.files.upload',
                'icon'        => 'fas fa-upload',
                'permission'  => 'filehosting.file.upload',
                'parent_id'   => $menuId,
                'order'       => 1,
                'is_active'   => 1,
                'is_locked'   => 0,
                'module_slug' => 'filehosting',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'title'       => 'All Files',
                'route'       => 'filehosting.files.show',
                'icon'        => 'fas fa-file',
                'permission'  => 'filehosting.file.view',
                'parent_id'   => $menuId,
                'order'       => 2,
                'is_active'   => 1,
                'is_locked'   => 0,
                'module_slug' => 'filehosting',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'title'       => 'Settings',
                'route'       => 'filehosting.settings',
                'icon'        => 'fas fa-cog',
                'permission'  => 'filehosting.system.manage_settings',
                'parent_id'   => $menuId,
                'order'       => 3,
                'is_active'   => 1,
                'is_locked'   => 0,
                'module_slug' => 'filehosting',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);

        // Attach to all departments
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

        echo "✔ Menu 'File Hosting' added.\n";

    } else {
        echo "ℹ Menu 'File Hosting' exists, skipping.\n";
    }
}

/*
|--------------------------------------------------------------------------
| 6️⃣  PERMISSIONS
|--------------------------------------------------------------------------
*/
$permissions = include __DIR__ . '/permissions.php';

foreach ($permissions['roles'] ?? [] as $roleName => $perms) {

    $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

    foreach ($perms as $perm) {
        $p = Permission::firstOrCreate([
            'name'       => "filehosting.{$perm}",
            'guard_name' => 'web',
        ]);
        $role->givePermissionTo($p);
    }
}

echo "✔ Permissions applied.\n";
echo "\n✅ FileHosting module installed successfully!\n";