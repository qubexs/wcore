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
| 3️⃣ MENU INSERTION
|--------------------------------------------------------------------------
*/

if (Schema::hasTable('menus')) {

    $menuExists = DB::table('menus')->where('module_slug', 'filehosting')->exists();

    if (!$menuExists) {

        $menuId = DB::table('menus')->insertGetId([
            'title'       => 'File Hosting',
            'route'       => 'filehosting.index',
            'icon'        => 'fas fa-cloud-upload-alt',
            'permission'  => 'filehosting.system.index',
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
                'title'      => 'Upload File',
                'route'      => 'filehosting.files.upload',
                'icon'       => 'fas fa-upload',
                'permission' => 'filehosting.file.upload',
                'parent_id'  => $menuId,
                'order'      => 1,
                'is_active'  => 1,
                'is_locked'  => 0,
                'module_slug' => 'filehosting',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'title'      => 'All Files',
                'route'      => 'filehosting.files.show',
                'icon'       => 'fas fa-file',
                'permission' => 'filehosting.file.view',
                'parent_id'  => $menuId,
                'order'      => 2,
                'is_active'  => 1,
                'is_locked'  => 0,
                'module_slug' => 'filehosting',
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);

        // Add to department_menu for all departments
        $deptIds = DB::table('departments')->pluck('id');
        $menuIds = [$menuId];
        $childMenus = DB::table('menus')->where('parent_id', $menuId)->pluck('id');
        $menuIds = array_merge($menuIds, $childMenus->toArray());
        
        foreach ($deptIds as $deptId) {
            foreach ($menuIds as $menuIdItem) {
                DB::table('department_menu')->updateOrInsert([
                    'department_id' => $deptId,
                    'menu_id' => $menuIdItem,
                ], [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        echo "✔ Menu 'File Hosting' added.\n";

    } else {
        echo "ℹ Menu 'File Hosting' exists, skipping.\n";
    }

}


/*
|--------------------------------------------------------------------------
| 4️⃣ PERMISSIONS
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