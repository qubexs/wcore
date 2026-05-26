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
        Schema::create('thumbnails', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('file_id')
                  ->constrained('files')
                  ->cascadeOnDelete();
            
            // Thumbnail specs
            $table->string('size_type', 20); // thumb, small, medium, large
            $table->string('file_path', 500);
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->unsignedBigInteger('file_size');
            
            // Generation status
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            
            $table->timestamps();
            
            // One thumbnail per size per file
            $table->unique(['file_id', 'size_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thumbnails');
    }
};