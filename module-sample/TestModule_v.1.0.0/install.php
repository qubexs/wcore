<?php
// install.php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

if (!defined('LARAVEL_START')) {
    exit('Cannot run install.php directly.');
}

try {

    // 1️⃣ Create Test_module_Table table
    if (!Schema::hasTable('Test_module_Table')) {

        Schema::create('Test_module_Table', function (Blueprint $table) {
            //$table->id();
            //$table->unsignedBigInteger('file_id');
            //$table->unsignedBigInteger('user_id')->nullable();
            //$table->string('action', 50);
            //$table->string('ip', 45)->nullable();
            //$table->text('user_agent')->nullable();
            //$table->timestamps();
            echo "✔ Table 'Test_module_Table' will create.\n";
        });

        echo "✔ Table 'Test_module_Table' created successfully.\n";

    } else {
        echo "✔ Table 'Test_module_Table' already exists, skipping.\n";
    }

    echo "✅ Infographic module installed successfully.\n";

} catch (\Exception $e) {
    echo "❌ Installation failed: " . $e->getMessage() . "\n";
}