<?php
// install.php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

if (!defined('LARAVEL_START')) {
    exit('Cannot run install.php directly.');
}

try {

    // 1️⃣ Create infographic_stats table
    if (!Schema::hasTable('infographic_stats')) {

        Schema::create('infographic_stats', function (Blueprint $table) {
            //$table->id();
            //$table->unsignedBigInteger('file_id');
            //$table->unsignedBigInteger('user_id')->nullable();
            //$table->string('action', 50);
            //$table->string('ip', 45)->nullable();
            //$table->text('user_agent')->nullable();
            //$table->timestamps();
            echo "✔ Table 'infographic_stats' will create.\n";
        });

        echo "✔ Table 'infographic_stats' created successfully.\n";

    } else {
        echo "✔ Table 'infographic_stats' already exists, skipping.\n";
    }

    echo "✅ Infographic module installed successfully.\n";

} catch (\Exception $e) {
    echo "❌ Installation failed: " . $e->getMessage() . "\n";
}