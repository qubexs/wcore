<?php

namespace App\Modules\FileHosting\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFileStatsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('file_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')
                  ->constrained('files')  // assumes your files table exists
                  ->cascadeOnDelete();
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->string('action'); // view, download, upload
            $table->ipAddress('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps(); // created_at = logged_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_stats');
    }

    /**
     * Return table name for safety checks in auto-migrate
     */
    public function getTableName(): string
    {
        return 'file_stats';
    }
}
