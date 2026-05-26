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
        Schema::create('file_stats', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic activity tracking
            $table->nullableMorphs('statable'); // Can link to File or Folder
            
            $table->foreignId('file_id')
                  ->nullable()
                  ->constrained('files')
                  ->nullOnDelete();
            
            $table->foreignId('folder_id')
                  ->nullable()
                  ->constrained('folders')
                  ->nullOnDelete();
            
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            
            // Activity details
            $table->string('action', 50)->index(); // upload, download, delete, move, rename, replace, create_folder, delete_folder, share, etc.
            $table->json('metadata')->nullable(); // Additional context (IP, user agent, before/after states)
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamps();
            
            // Performance indexes
            $table->index(['file_id', 'action', 'created_at']);
            $table->index(['folder_id', 'action', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_stats');
    }
};