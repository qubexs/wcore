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
        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            
            // Self-referencing for nested hierarchy (Adjacency List pattern)
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('folders')
                  ->cascadeOnDelete();
            
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->text('description')->nullable();
            
            // Materialized path for fast tree traversal (e.g., "/1/5/12/")
            $table->string('path', 500)->index();
            $table->unsignedInteger('depth')->default(0)->index();
            
            // Visibility: private (owner only), public (everyone), restricted (specific users)
            $table->enum('visibility', ['private', 'public', 'restricted'])->default('private');
            
            // Soft deletes for safe removal
            $table->timestamps();
            $table->softDeletes();
            
            // Composite indexes for common queries
            $table->index(['parent_id', 'user_id']);
            $table->index(['path', 'depth']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folders');
    }
};