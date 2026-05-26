<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Services\ActivityLogService;

class DatabaseBackupController extends Controller
{
    private string $backupPath = 'database-backups';

    /**
     * Show list of backups
     */
    public function index()
    {
        $files = Storage::disk('local')->files($this->backupPath);

        $backups = collect($files)->map(function ($file) {
            return [
                'name' => basename($file),
                'size' => round(Storage::size($file) / 1024 / 1024, 2) . ' MB',
                'time' => date('Y-m-d H:i:s', Storage::lastModified($file)),
            ];
        })->sortByDesc('time');

        return view('system.database-backup', compact('backups'));
    }

    /**
     * Run database backup - PHP-based (no mysqldump needed)
     */
    public function run()
    {
        $filename = 'db_' . date('Ymd_His') . '.sql';
        $fullPath = storage_path("app/database-backups/{$filename}");

        if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        try {
            $this->dumpDatabase($fullPath);
            
            if (!file_exists($fullPath) || filesize($fullPath) === 0) {
                throw new \Exception('Backup file is empty or was not created');
            }

            $fileSize = round(filesize($fullPath) / 1024 / 1024, 2) . ' MB';
            ActivityLogService::databaseBackup($filename, $fileSize);

            // Keep only latest 3 backups
            $files = collect(Storage::disk('local')->files($this->backupPath))
                ->filter(fn($f) => str_ends_with($f, '.sql'))
                ->sortByDesc(fn($f) => Storage::lastModified($f))
                ->values();

            if ($files->count() > 3) {
                $files->slice(3)->each(function ($file) {
                    Storage::delete($file);
                    ActivityLogService::databaseBackup(basename($file), 'deleted');
                });
            }

            return back()->with('success', 'Database backup created successfully.');
        } catch (\Exception $e) {
            ActivityLogService::error(
                'database_backup_failed',
                'Database backup failed: ' . $e->getMessage(),
                ['filename' => $filename, 'error' => $e->getMessage()]
            );
            return back()->withErrors('Database backup failed: ' . $e->getMessage());
        }
    }

    /**
     * Dump database using PDO (pure PHP)
     */
    private function dumpDatabase(string $filepath): void
    {
        $db = config('database.connections.mysql');
        
        $pdo = new \PDO(
            "mysql:host={$db['host']};port={$db['port']};dbname={$db['database']}",
            $db['username'],
            $db['password']
        );
        
        $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        
        $tables = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
        
        $output = '';
        
        foreach ($tables as $table) {
            $output .= "DROP TABLE IF EXISTS `{$table}`;\n\n";
            
            $create = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_NUM);
            $output .= $create[1] . ";\n\n";
            
            $rows = $pdo->query("SELECT * FROM `{$table}`");
            while ($row = $rows->fetch(\PDO::FETCH_NUM)) {
                $values = array_map(function($val) use ($pdo) {
                    if ($val === null) return 'NULL';
                    return $pdo->quote($val);
                }, $row);
                
                $output .= "INSERT INTO `{$table}` VALUES (" . implode(', ', $values) . ");\n";
            }
            
            $output .= "\n";
        }
        
        file_put_contents($filepath, $output);
    }

    /**
     * Download backup file
     */
    public function download($file)
    {
        $path = "{$this->backupPath}/{$file}";

        abort_unless(Storage::exists($path), 404);

        ActivityLogService::databaseBackup($file, 'downloaded');

        return Storage::download($path);
    }

    /**
     * Restore database from uploaded backup
     */
    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sql'
        ]);

        $db = config('database.connections.mysql');
        
        try {
            $pdo = new \PDO(
                "mysql:host={$db['host']};port={$db['port']};dbname={$db['database']}",
                $db['username'],
                $db['password']
            );
            
            $sql = file_get_contents($request->file('backup_file')->getRealPath());
            $pdo->exec($sql);
            
            $filename = $request->file('backup_file')->getClientOriginalName();
            ActivityLogService::databaseRestore($filename, true);
            return back()->with('success', 'Database restored successfully.');
        } catch (\Exception $e) {
            ActivityLogService::databaseRestore($request->file('backup_file')->getClientOriginalName(), false);
            return back()->withErrors('Database restore failed: ' . $e->getMessage());
        }
    }
}
