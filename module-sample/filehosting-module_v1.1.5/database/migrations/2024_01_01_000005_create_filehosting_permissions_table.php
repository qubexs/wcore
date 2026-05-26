<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('folder_permissions', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('folder_id')
                  ->constrained('folders')
                  ->cascadeOnDelete();
            
            // Polymorphic grantee (User or Role)
            $table->morphs('grantee');
            
            // Permissions stored as JSON array: ['view', 'upload', 'delete', 'share', 'manage']
            $table->json('permissions');
            
            // Grant tracking
            $table->foreignId('granted_by')
                  ->constrained('users')
                  ->cascadeOnDelete();
            
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            // Prevent duplicate permissions
            $table->unique(['folder_id', 'grantee_type', 'grantee_id']);
            $table->index(['grantee_type', 'grantee_id']);
        });
        
        // Separate table for file-specific permissions (optional granularity)
        Schema::create('file_permissions', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('file_id')
                  ->constrained('files')
                  ->cascadeOnDelete();
            
            $table->morphs('grantee');
            $table->json('permissions'); // ['view', 'download', 'edit', 'delete', 'share']
            
            $table->foreignId('granted_by')
                  ->constrained('users')
                  ->cascadeOnDelete();
            
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->unique(['file_id', 'grantee_type', 'grantee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_permissions');
        Schema::dropIfExists('folder_permissions');
    }
};