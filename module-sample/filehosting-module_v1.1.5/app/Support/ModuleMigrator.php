<?php
// src\Support\ModuleMigrator.php
namespace App\Modules\FileHosting\Support;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;

class ModuleMigrator
{
    public static function run(): void
    {
        if (!self::dbReady()) {
            return;
        }

        try {
            self::ensureFileStatsTable();
        } catch (\Throwable $e) {
            logger()->error('FileHosting migration failed', [
                'error' => $e->getMessage()
            ]);
        }
    }

    protected static function dbReady(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected static function ensureFileStatsTable(): void
    {
        if (!Schema::hasTable('file_stats')) {
            Schema::create('file_stats', function (Blueprint $table) {
                $table->id();
                $table->string('filename');
                $table->string('path');
                $table->unsignedBigInteger('views')->default(0);
                $table->unsignedBigInteger('downloads')->default(0);
                $table->timestamp('last_accessed_at')->nullable();
                $table->timestamps();
            });

            logger()->info('FileHosting: file_stats table created');
            return;
        }

        // 🟡 TABLE EXISTS → PATCH COLUMNS
        Schema::table('file_stats', function (Blueprint $table) {

            if (!Schema::hasColumn('file_stats', 'views')) {
                $table->unsignedBigInteger('views')->default(0);
            }

            if (!Schema::hasColumn('file_stats', 'downloads')) {
                $table->unsignedBigInteger('downloads')->default(0);
            }

            if (!Schema::hasColumn('file_stats', 'last_accessed_at')) {
                $table->timestamp('last_accessed_at')->nullable();
            }
        });
    }
}
