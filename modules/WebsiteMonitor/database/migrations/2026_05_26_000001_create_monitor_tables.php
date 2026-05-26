<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('monitor_targets')) {
            Schema::create('monitor_targets', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255);
                $table->string('url', 2048);
                $table->string('method', 10)->default('GET');
                $table->integer('check_interval')->default(5);
                $table->integer('timeout')->default(10);
                $table->integer('expected_status')->default(200);
                $table->string('check_string', 500)->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('alert_on_down')->default(true);
                $table->string('alert_methods', 50)->default('message');
                $table->unsignedBigInteger('pic_user_id')->nullable();
                $table->timestamp('last_checked_at')->nullable();
                $table->integer('last_status')->nullable();
                $table->float('last_response_time')->nullable();
                $table->text('last_error')->nullable();
                $table->unsignedBigInteger('created_by');
                $table->timestamps();

                $table->index('is_active');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('pic_user_id')->references('id')->on('users')->onDelete('set null');
            });
        }

        if (!Schema::hasTable('monitor_logs')) {
            Schema::create('monitor_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('monitor_target_id');
                $table->integer('status_code')->nullable();
                $table->float('response_time')->nullable();
                $table->text('error_message')->nullable();
                $table->unsignedBigInteger('checked_by')->nullable();
                $table->timestamps();

                $table->index('monitor_target_id');
                $table->index('created_at');
                $table->foreign('monitor_target_id')->references('id')->on('monitor_targets')->onDelete('cascade');
                $table->foreign('checked_by')->references('id')->on('users')->onDelete('set null');
            });
        }

        if (!Schema::hasTable('monitor_alerts')) {
            Schema::create('monitor_alerts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('monitor_target_id');
                $table->string('alert_type', 50)->default('message');
                $table->unsignedBigInteger('sent_to_user_id')->nullable();
                $table->string('sent_to_email', 255)->nullable();
                $table->string('subject', 500)->nullable();
                $table->text('message')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();

                $table->index('monitor_target_id');
                $table->foreign('monitor_target_id')->references('id')->on('monitor_targets')->onDelete('cascade');
                $table->foreign('sent_to_user_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('monitor_alerts');
        Schema::dropIfExists('monitor_logs');
        Schema::dropIfExists('monitor_targets');
    }
};
