<?php
// install.php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

// Ensure this file runs in Laravel context
if (!defined('LARAVEL_START')) {
    exit('Cannot run install.php directly.');
}

try {
    // ============================================================
    // 1️⃣ FOLDERS TABLE (Nested Hierarchy)
    // ============================================================
    if (!Schema::hasTable('folders')) {
        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable(); // For nested folders
            $table->unsignedBigInteger('user_id'); // Owner
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->text('description')->nullable();
            $table->string('path', 500); // Full path for quick lookup
            $table->integer('depth')->default(0); // Nesting level
            $table->enum('visibility', ['private', 'public', 'restricted'])->default('private');
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('parent_id');
            $table->index('user_id');
            $table->index('path');
            
            // Self-referencing foreign key
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('folders')
                  ->onDelete('cascade');
                  
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });

        echo "✔ Table 'folders' created successfully.\n";
    } else {
        echo "ℹ Table 'folders' already exists, skipping.\n";
    }

    // ============================================================
    // 2️⃣ FILES TABLE (Belongs to Folders)
    // ============================================================
    if (!Schema::hasTable('files')) {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('folder_id')->nullable(); // NULL = root level
            $table->unsignedBigInteger('user_id'); // Uploader
            $table->string('original_name', 255);
            $table->string('stored_name', 255)->unique(); // Unique storage name
            $table->string('file_path', 500); // Relative storage path
            $table->string('mime_type', 100);
            $table->bigInteger('size'); // Bytes
            $table->string('extension', 20);
            $table->string('checksum', 64)->nullable(); // SHA-256 for integrity
            $table->text('description')->nullable();
            $table->enum('visibility', ['private', 'public', 'restricted'])->default('private');
            $table->timestamp('expires_at')->nullable(); // For temporary files
            $table->integer('download_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('folder_id');
            $table->index('user_id');
            $table->index('mime_type');
            $table->index('checksum');
            $table->index('created_at');
            
            // Foreign keys
            $table->foreign('folder_id')
                  ->references('id')
                  ->on('folders')
                  ->onDelete('cascade');
                  
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });

        echo "✔ Table 'files' created successfully.\n";
    } else {
        echo "ℹ Table 'files' already exists, skipping.\n";
    }

    // ============================================================
    // 3️⃣ FILE VERSIONS (For Replace functionality)
    // ============================================================
    if (!Schema::hasTable('file_versions')) {
        Schema::create('file_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('file_id');
            $table->unsignedBigInteger('user_id'); // Who uploaded this version
            $table->string('stored_name', 255);
            $table->string('file_path', 500);
            $table->bigInteger('size');
            $table->string('checksum', 64);
            $table->integer('version_number');
            $table->text('change_notes')->nullable();
            $table->timestamps();

            $table->index('file_id');
            $table->unique(['file_id', 'version_number']);
            
            $table->foreign('file_id')
                  ->references('id')
                  ->on('files')
                  ->onDelete('cascade');
        });

        echo "✔ Table 'file_versions' created successfully.\n";
    } else {
        echo "ℹ Table 'file_versions' already exists, skipping.\n";
    }

    // ============================================================
    // 4️⃣ FILE STATS (Audit Trail)
    // ============================================================
    if (!Schema::hasTable('file_stats')) {
        Schema::create('file_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('file_id')->nullable();
            $table->unsignedBigInteger('folder_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action', 50); // upload, download, delete, move, rename, replace, create_folder, delete_folder
            $table->json('metadata')->nullable(); // Additional context
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['file_id', 'action']);
            $table->index(['folder_id', 'action']);
            $table->index('created_at');
        });

        echo "✔ Table 'file_stats' created successfully.\n";
    } else {
        echo "ℹ Table 'file_stats' already exists, skipping.\n";
    }

    // ============================================================
    // 5️⃣ FOLDER PERMISSIONS (Granular Access)
    // ============================================================
    if (!Schema::hasTable('folder_permissions')) {
        Schema::create('folder_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('folder_id');
            $table->morphs('grantee'); // user or role
            $table->json('permissions'); // ['view', 'upload', 'delete', 'share']
            $table->unsignedBigInteger('granted_by');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['folder_id', 'grantee_type', 'grantee_id']);
            
            $table->foreign('folder_id')
                  ->references('id')
                  ->on('folders')
                  ->onDelete('cascade');
        });

        echo "✔ Table 'folder_permissions' created successfully.\n";
    } else {
        echo "ℹ Table 'folder_permissions' already exists, skipping.\n";
    }

    // ============================================================
    // 6️⃣ THUMBNAILS (For image previews)
    // ============================================================
    if (!Schema::hasTable('thumbnails')) {
        Schema::create('thumbnails', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('file_id');
            $table->string('size_type', 20); // small, medium, large
            $table->string('file_path', 500);
            $table->integer('width');
            $table->integer('height');
            $table->bigInteger('file_size');
            $table->timestamps();

            $table->unique(['file_id', 'size_type']);
            
            $table->foreign('file_id')
                  ->references('id')
                  ->on('files')
                  ->onDelete('cascade');
        });

        echo "✔ Table 'thumbnails' created successfully.\n";
    } else {
        echo "ℹ Table 'thumbnails' already exists, skipping.\n";
    }

    // ============================================================
    // ✅ SUCCESS
    // ============================================================
    echo "\n✅ FileHosting module with FOLDER SUPPORT installed successfully!\n";
    echo "📁 Features: Nested folders, file versions, audit logs, thumbnails, granular permissions\n";

} catch (\Exception $e) {
    echo "❌ Installation failed: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}