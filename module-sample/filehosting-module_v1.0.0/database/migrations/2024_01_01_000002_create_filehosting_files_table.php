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
        Schema::create('file_versions', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('file_id')
                  ->constrained('files')
                  ->cascadeOnDelete();
            
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            
            // Version storage
            $table->string('stored_name', 255);
            $table->string('file_path', 500);
            $table->unsignedBigInteger('size');
            $table->string('checksum', 64);
            
            // Versioning
            $table->unsignedInteger('version_number');
            $table->text('change_notes')->nullable();
            $table->boolean('is_current')->default(false)->index();
            
            $table->timestamps();
            
            // Ensure unique version numbers per file
            $table->unique(['file_id', 'version_number']);
            $table->index(['file_id', 'is_current']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_versions');
    }
};