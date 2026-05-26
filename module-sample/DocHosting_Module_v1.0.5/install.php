<?php
// install.php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

// Ensure this file runs in Laravel context
if (!defined('LARAVEL_START')) {
    exit('Cannot run install.php directly.');
}

// Wrap in a try/catch for safety
try {
    // 1️⃣ Create file_stats table
    if (!Schema::hasTable('file_stats')) {
        Schema::create('file_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('file_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action', 50); // e.g., upload, download
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            // Optional foreign keys
            // $table->foreign('file_id')->references('id')->on('files')->onDelete('cascade');
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        echo "✔ Table 'file_stats' created successfully.\n";
    } else {
        echo "ℹ Table 'file_stats' already exists, skipping.\n";
    }

    // 2️⃣ Optional: seed initial data (example)
    /*
    DB::table('file_stats')->insert([
        'file_id' => 0,
        'user_id' => null,
        'action' => 'install',
        'ip' => '127.0.0.1',
        'user_agent' => 'Module Installer',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "✔ Initial seed inserted into 'file_stats'.\n";
    */

    // 3️⃣ Success
    echo "✅ FileHosting module installed successfully.\n";

} catch (\Exception $e) {
    echo "❌ Installation failed: " . $e->getMessage() . "\n";
}
