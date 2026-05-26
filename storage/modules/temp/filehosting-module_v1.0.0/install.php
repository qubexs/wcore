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
| Helper Function
|--------------------------------------------------------------------------
*/
function addColumnIfMissing($table, $column, $callback)
{
    if (!Schema::hasColumn($table, $column)) {
        Schema::table($table, $callback);
        echo "➕ Column '{$column}' added to '{$table}'.\n";
    }
}

/*
|--------------------------------------------------------------------------
| 1️⃣ FOLDERS TABLE
|--------------------------------------------------------------------------
*/

if (!Schema::hasTable('folders')) {

    Schema::create('folders', function (Blueprint $table) {

        $table->id();
        $table->unsignedBigInteger('parent_id')->nullable();
        $table->unsignedBigInteger('user_id');

        $table->string('name',255);
        $table->string('slug',255)->unique();

        $table->text('description')->nullable();
        $table->string('path',500);

        $table->integer('depth')->default(0);

        $table->enum('visibility',['private','public','restricted'])->default('private');

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

    addColumnIfMissing('folders','parent_id',function($table){
        $table->unsignedBigInteger('parent_id')->nullable();
    });

    addColumnIfMissing('folders','user_id',function($table){
        $table->unsignedBigInteger('user_id');
    });

    addColumnIfMissing('folders','description',function($table){
        $table->text('description')->nullable();
    });

    addColumnIfMissing('folders','path',function($table){
        $table->string('path',500);
    });

    addColumnIfMissing('folders','depth',function($table){
        $table->integer('depth')->default(0);
    });

    addColumnIfMissing('folders','visibility',function($table){
        $table->enum('visibility',['private','public','restricted'])->default('private');
    });
}


/*
|--------------------------------------------------------------------------
| 2️⃣ FILES TABLE
|--------------------------------------------------------------------------
*/

if (!Schema::hasTable('files')) {

    Schema::create('files', function (Blueprint $table) {

        $table->id();
        $table->unsignedBigInteger('folder_id')->nullable();
        $table->unsignedBigInteger('uploaded_by');

        $table->string('original_name',255);
        $table->string('stored_name',255)->unique();

        $table->string('file_path',500);
        $table->string('mime_type',100);

        $table->bigInteger('size');
        $table->string('extension',20);

        $table->string('checksum',64)->nullable();
        $table->text('description')->nullable();

        $table->enum('visibility',['private','public','restricted'])->default('private');

        $table->timestamp('expires_at')->nullable();
        $table->integer('download_count')->default(0);

        $table->timestamps();
        $table->softDeletes();

        $table->index('folder_id');
        $table->index('uploaded_by');
        $table->index('mime_type');
        $table->index('checksum');

        $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
        $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');

    });

    echo "✔ Table 'files' created.\n";

} else {

    echo "ℹ Table 'files' exists, checking columns...\n";

    addColumnIfMissing('files','folder_id',function($table){
        $table->unsignedBigInteger('folder_id')->nullable();
    });

    addColumnIfMissing('files','uploaded_by',function($table){
        $table->unsignedBigInteger('uploaded_by');
    });

    addColumnIfMissing('files','original_name',function($table){
        $table->string('original_name',255);
    });

    addColumnIfMissing('files','stored_name',function($table){
        $table->string('stored_name',255)->unique();
    });

    addColumnIfMissing('files','file_path',function($table){
        $table->string('file_path',500);
    });

    addColumnIfMissing('files','mime_type',function($table){
        $table->string('mime_type',100);
    });

    addColumnIfMissing('files','size',function($table){
        $table->bigInteger('size');
    });

    addColumnIfMissing('files','extension',function($table){
        $table->string('extension',20);
    });

    addColumnIfMissing('files','checksum',function($table){
        $table->string('checksum',64)->nullable();
    });

    addColumnIfMissing('files','description',function($table){
        $table->text('description')->nullable();
    });

    addColumnIfMissing('files','visibility',function($table){
        $table->enum('visibility',['private','public','restricted'])->default('private');
    });

    addColumnIfMissing('files','expires_at',function($table){
        $table->timestamp('expires_at')->nullable();
    });

    addColumnIfMissing('files','download_count',function($table){
        $table->integer('download_count')->default(0);
    });

}



/*
|--------------------------------------------------------------------------
| 3️⃣ FILE VERSIONS TABLE
|--------------------------------------------------------------------------
| Matches FileVersion model fillable:
|   file_id, uploaded_by, stored_name, file_path,
|   size, checksum, version_number, change_notes
*/

if (!Schema::hasTable('file_versions')) {

    Schema::create('file_versions', function (Blueprint $table) {

        $table->id();
        $table->unsignedBigInteger('file_id');
        $table->unsignedBigInteger('uploaded_by');

        $table->string('stored_name', 255);
        $table->string('file_path', 500);
        $table->bigInteger('size');
        $table->string('checksum', 64)->nullable();

        $table->unsignedInteger('version_number')->default(1);
        $table->text('change_notes')->nullable();

        $table->timestamps();

        $table->index('file_id');
        $table->index('uploaded_by');

        $table->foreign('file_id')
              ->references('id')->on('files')
              ->onDelete('cascade');

        $table->foreign('uploaded_by')
              ->references('id')->on('users')
              ->onDelete('cascade');
    });

    echo "✔ Table 'file_versions' created.\n";

} else {

    echo "ℹ Table 'file_versions' exists, checking columns...\n";

    addColumnIfMissing('file_versions', 'file_id', function ($table) {
        $table->unsignedBigInteger('file_id');
    });
    addColumnIfMissing('file_versions', 'uploaded_by', function ($table) {
        $table->unsignedBigInteger('uploaded_by');
    });
    addColumnIfMissing('file_versions', 'stored_name', function ($table) {
        $table->string('stored_name', 255);
    });
    addColumnIfMissing('file_versions', 'file_path', function ($table) {
        $table->string('file_path', 500);
    });
    addColumnIfMissing('file_versions', 'size', function ($table) {
        $table->bigInteger('size');
    });
    addColumnIfMissing('file_versions', 'checksum', function ($table) {
        $table->string('checksum', 64)->nullable();
    });
    addColumnIfMissing('file_versions', 'version_number', function ($table) {
        $table->unsignedInteger('version_number')->default(1);
    });
    addColumnIfMissing('file_versions', 'change_notes', function ($table) {
        $table->text('change_notes')->nullable();
    });
}


/*
|--------------------------------------------------------------------------
| 4️⃣ FILE STATS TABLE
|--------------------------------------------------------------------------
| Matches FileStat model fillable:
|   file_id, folder_id, uploaded_by,
|   action, metadata, ip, user_agent
*/

if (!Schema::hasTable('file_stats')) {

    Schema::create('file_stats', function (Blueprint $table) {

        $table->id();
        $table->unsignedBigInteger('file_id')->nullable();
        $table->unsignedBigInteger('folder_id')->nullable();
        $table->unsignedBigInteger('uploaded_by')->nullable();

        $table->string('action', 30)->index();
        // upload | download | delete | move | rename |
        // replace | create_folder | delete_folder | view | restore_version

        $table->json('metadata')->nullable();
        $table->string('ip', 45)->nullable();
        $table->text('user_agent')->nullable();

        $table->timestamps();

        $table->index('file_id');
        $table->index('folder_id');
        $table->index('uploaded_by');

        $table->foreign('file_id')
              ->references('id')->on('files')
              ->nullOnDelete();

        $table->foreign('folder_id')
              ->references('id')->on('folders')
              ->nullOnDelete();

        $table->foreign('uploaded_by')
              ->references('id')->on('users')
              ->nullOnDelete();
    });

    echo "✔ Table 'file_stats' created.\n";

} else {

    echo "ℹ Table 'file_stats' exists, checking columns...\n";

    addColumnIfMissing('file_stats', 'file_id', function ($table) {
        $table->unsignedBigInteger('file_id')->nullable();
    });
    addColumnIfMissing('file_stats', 'folder_id', function ($table) {
        $table->unsignedBigInteger('folder_id')->nullable();
    });
    addColumnIfMissing('file_stats', 'uploaded_by', function ($table) {
        $table->unsignedBigInteger('uploaded_by')->nullable();
    });
    addColumnIfMissing('file_stats', 'action', function ($table) {
        $table->string('action', 30)->index();
    });
    addColumnIfMissing('file_stats', 'metadata', function ($table) {
        $table->json('metadata')->nullable();
    });
    addColumnIfMissing('file_stats', 'ip', function ($table) {
        $table->string('ip', 45)->nullable();
    });
    addColumnIfMissing('file_stats', 'user_agent', function ($table) {
        $table->text('user_agent')->nullable();
    });
}


/*
|--------------------------------------------------------------------------
| 5️⃣ FOLDER PERMISSIONS TABLE
|--------------------------------------------------------------------------
| Matches FolderPermission model fillable:
|   folder_id, grantee_type, grantee_id,
|   permissions, granted_by, expires_at
*/

if (!Schema::hasTable('folder_permissions')) {

    Schema::create('folder_permissions', function (Blueprint $table) {

        $table->id();
        $table->unsignedBigInteger('folder_id');

        // Polymorphic grantee (User, Role, Department…)
        $table->string('grantee_type', 100);
        $table->unsignedBigInteger('grantee_id');

        $table->json('permissions');           // array of permission strings
        $table->unsignedBigInteger('granted_by');
        $table->timestamp('expires_at')->nullable();

        $table->timestamps();

        $table->index(['grantee_type', 'grantee_id']);
        $table->index('folder_id');

        // One permission row per folder+grantee combination
        $table->unique(['folder_id', 'grantee_type', 'grantee_id'], 'uniq_folder_grantee');

        $table->foreign('folder_id')
              ->references('id')->on('folders')
              ->onDelete('cascade');

        $table->foreign('granted_by')
              ->references('id')->on('users')
              ->onDelete('cascade');
    });

    echo "✔ Table 'folder_permissions' created.\n";

} else {

    echo "ℹ Table 'folder_permissions' exists, checking columns...\n";

    addColumnIfMissing('folder_permissions', 'folder_id', function ($table) {
        $table->unsignedBigInteger('folder_id');
    });
    addColumnIfMissing('folder_permissions', 'grantee_type', function ($table) {
        $table->string('grantee_type', 100);
    });
    addColumnIfMissing('folder_permissions', 'grantee_id', function ($table) {
        $table->unsignedBigInteger('grantee_id');
    });
    addColumnIfMissing('folder_permissions', 'permissions', function ($table) {
        $table->json('permissions');
    });
    addColumnIfMissing('folder_permissions', 'granted_by', function ($table) {
        $table->unsignedBigInteger('granted_by');
    });
    addColumnIfMissing('folder_permissions', 'expires_at', function ($table) {
        $table->timestamp('expires_at')->nullable();
    });
}


/*
|--------------------------------------------------------------------------
| 6️⃣ THUMBNAILS TABLE
|--------------------------------------------------------------------------
| Matches Thumbnail model fillable:
|   file_id, size_type, file_path, width, height, file_size
| size_type values: small (80x80) | medium (300x300) | large (800x800)
*/

if (!Schema::hasTable('thumbnails')) {

    Schema::create('thumbnails', function (Blueprint $table) {

        $table->id();
        $table->unsignedBigInteger('file_id');

        $table->enum('size_type', ['small', 'medium', 'large']);
        $table->string('file_path', 500);

        $table->unsignedInteger('width');
        $table->unsignedInteger('height');
        $table->unsignedBigInteger('file_size');   // bytes

        $table->timestamps();

        $table->index('file_id');
        $table->unique(['file_id', 'size_type']);   // one thumbnail per size per file

        $table->foreign('file_id')
              ->references('id')->on('files')
              ->onDelete('cascade');
    });

    echo "✔ Table 'thumbnails' created.\n";

} else {

    echo "ℹ Table 'thumbnails' exists, checking columns...\n";

    addColumnIfMissing('thumbnails', 'file_id', function ($table) {
        $table->unsignedBigInteger('file_id');
    });
    addColumnIfMissing('thumbnails', 'size_type', function ($table) {
        $table->enum('size_type', ['small', 'medium', 'large']);
    });
    addColumnIfMissing('thumbnails', 'file_path', function ($table) {
        $table->string('file_path', 500);
    });
    addColumnIfMissing('thumbnails', 'width', function ($table) {
        $table->unsignedInteger('width');
    });
    addColumnIfMissing('thumbnails', 'height', function ($table) {
        $table->unsignedInteger('height');
    });
    addColumnIfMissing('thumbnails', 'file_size', function ($table) {
        $table->unsignedBigInteger('file_size');
    });
}


/*
|--------------------------------------------------------------------------
| 7️⃣ FIX: folders.user_id → uploaded_by
|--------------------------------------------------------------------------
| The Folder model uses 'uploaded_by' but the original install created
| 'user_id'. Add 'uploaded_by' if it is missing.
*/

addColumnIfMissing('folders', 'uploaded_by', function ($table) {
    $table->unsignedBigInteger('uploaded_by')->nullable()->after('parent_id');
});

/*
|--------------------------------------------------------------------------
| 8️⃣ MENU INSERTION
|--------------------------------------------------------------------------
*/

if (Schema::hasTable('menus')) {

    $menuExists = DB::table('menus')->where('slug','file-hosting')->exists();

    if (!$menuExists) {

        $menuId = DB::table('menus')->insertGetId([
            'name'       => 'File Hosting',
            'slug'       => 'file-hosting',
            'parent_id'  => null,
            'url'        => '/filehosting',
            'icon'       => 'fa-folder',
            'is_active'  => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('menus')->insert([

            [
                'name'=>'Index',
                'slug'=>'filehosting.index',
                'parent_id'=>$menuId,
                'url'=>'/filehosting',
                'icon'=>'fa-list',
                'is_active'=>0,
                'created_at'=>now(),
                'updated_at'=>now(),
            ],

            [
                'name'=>'Upload File',
                'slug'=>'filehosting.files.upload',
                'parent_id'=>$menuId,
                'url'=>'/filehosting/files/upload',
                'icon'=>'fa-upload',
                'is_active'=>0,
                'created_at'=>now(),
                'updated_at'=>now(),
            ]

        ]);

        echo "✔ Menu 'File Hosting' added.\n";

    } else {
        echo "ℹ Menu 'File Hosting' exists, skipping.\n";
    }

}


/*
|--------------------------------------------------------------------------
| 9️⃣ PERMISSIONS
|--------------------------------------------------------------------------
*/

$permissions = include __DIR__.'/permissions.php';

foreach ($permissions['roles'] ?? [] as $roleName => $perms) {

    $role = Role::firstOrCreate([
        'name'=>$roleName,
        'guard_name'=>'web'
    ]);

    foreach ($perms as $perm) {

        $p = Permission::firstOrCreate([
            'name'=>"filehosting.{$perm}",
            'guard_name'=>'web'
        ]);

        $role->givePermissionTo($p);
    }
}

echo "✔ Default permissions applied.\n";

echo "\n✅ FileHosting module installed successfully!\n";