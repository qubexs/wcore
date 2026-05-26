<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Personal Information
            $table->string('salutation')->nullable()->after('name');
            $table->string('professional_title')->nullable()->after('salutation');
            $table->string('job_title')->nullable()->after('professional_title');
            $table->text('bio')->nullable()->after('job_title');

            // Contact Information
            $table->string('secondary_email')->nullable()->after('email');
            $table->string('mobile_phone')->nullable()->after('phone');
            $table->string('fax')->nullable()->after('mobile_phone');

            // Professional Information
            $table->string('specialization')->nullable()->after('job_title');
            $table->string('mmc_reg_no')->nullable()->after('specialization');
            $table->date('mmc_reg_expiry')->nullable()->after('mmc_reg_no');
            $table->string('other_reg_no')->nullable()->after('mmc_reg_expiry');
            $table->date('other_reg_expiry')->nullable()->after('other_reg_no');
            $table->text('key_credentials')->nullable()->after('other_reg_expiry');

            // Preferences
            $table->string('preferred_language')->default('en')->after('avatar');
            $table->string('timezone')->default('UTC')->after('preferred_language');
            $table->boolean('two_factor_enabled')->default(false)->after('timezone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'salutation',
                'professional_title',
                'job_title',
                'bio',
                'secondary_email',
                'mobile_phone',
                'fax',
                'specialization',
                'mmc_reg_no',
                'mmc_reg_expiry',
                'other_reg_no',
                'other_reg_expiry',
                'key_credentials',
                'preferred_language',
                'timezone',
                'two_factor_enabled',
            ]);
        });
    }
};