<?php

// app/Http/Controllers/BackupController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class BackupController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Paths
    |--------------------------------------------------------------------------
    */

    /** Where finished website backup ZIPs live (storage/app/website-backups/) */
    private string $backupDisk  = 'local';
    private string $backupDir   = 'website-backups';

    /** Staging area for uploaded 3rd-party ZIPs (storage/app/sitebackup/) */
    private string $stagingDir  = 'sitebackup';

    /** Meta JSON file that stores notes alongside each backup */
    private string $metaFile    = 'website-backups/.meta.json';

    /** Folders that may be selected for backup (relative to base_path()) */
    private array $allowedFolders = [
        'app', 'bootstrap', 'config', 'database',
        'lang', 'module-sample', 'modules', 'public',
        'resources', 'routes', 'tests',
        // 'storage',  // <-- REMOVED! Or keep it with exclusions above
    ];


    /*
    |--------------------------------------------------------------------------
    | index()  –  redirect to site-management page
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        return redirect()->route('site.management');
    }


    /*
    |--------------------------------------------------------------------------
    | run()  –  Create a new website backup ZIP
    |--------------------------------------------------------------------------
    | POST /backup/run
    | Body: folders[] (array), note (string, optional)
    */
    public function run(Request $request)
    {
        $request->validate([
            'folders'   => 'required|array|min:1',
            'folders.*' => 'in:' . implode(',', $this->allowedFolders),
            'note'      => 'nullable|string|max:300',
        ]);

        $selectedFolders = $request->input('folders');
        $note            = trim($request->input('note', ''));

        // Ensure storage directory exists
        Storage::disk($this->backupDisk)->makeDirectory($this->backupDir);

        $filename  = 'website_backup_' . date('Ymd_His') . '.zip';
        $zipPath   = storage_path('app/' . $this->backupDir . '/' . $filename);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Could not create ZIP file.');
        }

            // Build exclusion patterns (folders/files to skip)
        $exclusions = [
        // Never backup the backup directory itself!
            storage_path('app/' . $this->backupDir),
        // Also exclude common temp/cache folders
            storage_path('app/' . $this->stagingDir),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/testing'),
            storage_path('framework/views'),
            storage_path('logs'),
            base_path('vendor'),  // vendor can be huge, composer reinstalls it anyway
            base_path('.git'),    // git history not needed
            base_path('node_modules'), // npm reinstalls
        ];

foreach ($selectedFolders as $folder) {
        $folderPath = base_path($folder);
        if (!is_dir($folderPath)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($folderPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $realPath = $file->getRealPath();

            // CHECK: Skip if file is inside any excluded directory
            $shouldExclude = false;
            foreach ($exclusions as $excludePath) {
                if (str_starts_with($realPath, $excludePath)) {
                    $shouldExclude = true;
                    break;
                }
            }
            if ($shouldExclude) {
                continue;
            }

            $archivePath = $folder . DIRECTORY_SEPARATOR . substr($realPath, strlen($folderPath) + 1);
            $zip->addFile($realPath, $archivePath);
        }
    }

    $zip->close();

    // Save note in meta JSON
    $this->saveMeta($filename, $note, $selectedFolders);

    return back()->with('success', "Backup created: {$filename}");
}


    /*
    |--------------------------------------------------------------------------
    | download()  –  Stream a backup ZIP to the browser
    |--------------------------------------------------------------------------
    | GET /backup/download/{file}
    */
    public function download(string $file)
    {
        // Sanitise – no path traversal
        $file     = basename($file);
        $filePath = storage_path('app/' . $this->backupDir . '/' . $file);

        abort_unless(file_exists($filePath), 404, 'Backup file not found.');

        return response()->download($filePath);
    }


    /*
    |--------------------------------------------------------------------------
    | delete()  –  Delete a backup ZIP
    |--------------------------------------------------------------------------
    | DELETE /backup/{file}
    */
    public function delete(string $file)
    {
        $file     = basename($file);
        $storagePath = $this->backupDir . '/' . $file;

        if (Storage::disk($this->backupDisk)->exists($storagePath)) {
            Storage::disk($this->backupDisk)->delete($storagePath);
            $this->deleteMeta($file);
        }

        return back()->with('success', "Backup '{$file}' deleted.");
    }


    /*
    |--------------------------------------------------------------------------
    | restoreLocal()  –  Restore directly from a local backup ZIP (Card 3 list)
    |--------------------------------------------------------------------------
    | POST /backup/restore/local
    | Body: backup_name (string), mode (full|newer)
    */
    public function restoreLocal(Request $request)
    {
        $request->validate([
            'backup_name' => 'required|string',
            'mode'        => 'required|in:full,newer',
        ]);

        $file    = basename($request->input('backup_name'));
        $zipPath = storage_path('app/' . $this->backupDir . '/' . $file);

        abort_unless(file_exists($zipPath), 404, 'Backup file not found.');

        // Extract to a temp staging folder
        $sessionId   = uniqid('local_restore_', true);
        $stagingPath = storage_path('app/' . $this->stagingDir . '/' . $sessionId);
        mkdir($stagingPath, 0755, true);

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return back()->with('error', 'Could not open backup ZIP.');
        }
        $zip->extractTo($stagingPath);
        $zip->close();

        // Copy files to live site
        $mode     = $request->input('mode');
        $liveBase = base_path();
        $copied   = 0;
        $skipped  = 0;

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($stagingPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $f) {
            if (!$f->isFile()) continue;

            $relative  = substr($f->getRealPath(), strlen($stagingPath) + 1);
            $livePath  = $liveBase . DIRECTORY_SEPARATOR . $relative;
            $topFolder = strtok($relative, DIRECTORY_SEPARATOR);

            if (!in_array($topFolder, $this->allowedFolders)) {
                $skipped++;
                continue;
            }

            if ($mode === 'newer' && file_exists($livePath)) {
                if (filesize($f->getRealPath()) === filesize($livePath)
                    && filemtime($f->getRealPath()) === filemtime($livePath)) {
                    $skipped++;
                    continue;
                }
            }

            $destDir = dirname($livePath);
            if (!is_dir($destDir)) mkdir($destDir, 0755, true);

            copy($f->getRealPath(), $livePath);
            $copied++;
        }

        // Clean up staging
        $this->deleteDirectory($stagingPath);

        return back()->with('success', "Restore complete — {$copied} file(s) copied, {$skipped} skipped.");
    }


    /*
    | POST /backup/restore/upload
    | Returns JSON: { success, staging_path, diff }
    */
    public function restoreUpload(Request $request)
    {
        $request->validate([
            'zip_file' => 'required|file|mimes:zip|max:524288', // max 512 MB
        ]);

        // Store the uploaded zip into staging area
        $stagingBase = storage_path('app/' . $this->stagingDir);
        if (!is_dir($stagingBase)) {
            mkdir($stagingBase, 0755, true);
        }

        $sessionId   = uniqid('restore_', true);
        $stagingPath = $stagingBase . '/' . $sessionId;
        mkdir($stagingPath, 0755, true);

        $zipFile     = $request->file('zip_file');
        $zipDest     = $stagingPath . '/upload.zip';
        $zipFile->move($stagingPath, 'upload.zip');

        // Extract the ZIP into staging/{sessionId}/extracted/
        $extractPath = $stagingPath . '/extracted';
        mkdir($extractPath, 0755, true);

        $zip = new ZipArchive();
        if ($zip->open($zipDest) !== true) {
            return response()->json(['success' => false, 'message' => 'Could not open ZIP file.'], 422);
        }
        $zip->extractTo($extractPath);
        $zip->close();

        // Compare extracted files with live files
        $diff = $this->compareFiles($extractPath, base_path());

        return response()->json([
            'success'      => true,
            'staging_path' => $this->stagingDir . '/' . $sessionId . '/extracted',
            'diff'         => $diff,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | restoreExecute()  –  Copy staged files to live site
    |--------------------------------------------------------------------------
    | POST /backup/restore/execute
    | Body: staging_path (string), mode (full|newer)
    | Returns JSON: { success, copied, skipped }
    */
    public function restoreExecute(Request $request)
    {
        $request->validate([
            'staging_path' => 'required|string',
            'mode'         => 'required|in:full,newer',
        ]);

        // Safety: staging_path must start with our staging dir
        $rel          = $request->input('staging_path');
        $rel          = ltrim(str_replace(['..', '\\'], ['', '/'], $rel), '/');

        if (!str_starts_with($rel, $this->stagingDir . '/')) {
            return response()->json(['success' => false, 'message' => 'Invalid staging path.'], 422);
        }

        $stagingAbs = storage_path('app/' . $rel);
        if (!is_dir($stagingAbs)) {
            return response()->json(['success' => false, 'message' => 'Staging directory not found.'], 404);
        }

        $mode    = $request->input('mode');
        $liveBase = base_path();
        $copied  = 0;
        $skipped = 0;

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($stagingAbs, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $relative  = substr($file->getRealPath(), strlen($stagingAbs) + 1);
            $livePath  = $liveBase . DIRECTORY_SEPARATOR . $relative;

            // Only allow writing into the allowed folders
            $topFolder = strtok($relative, DIRECTORY_SEPARATOR);
            if (!in_array($topFolder, $this->allowedFolders)) {
                $skipped++;
                continue;
            }

            if ($mode === 'newer' && file_exists($livePath)) {
                // Skip if size AND mtime are identical
                $sameSize  = (filesize($file->getRealPath()) === filesize($livePath));
                $sameMtime = (filemtime($file->getRealPath()) === filemtime($livePath));
                if ($sameSize && $sameMtime) {
                    $skipped++;
                    continue;
                }
            }

            // Ensure directory exists
            $destDir = dirname($livePath);
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }

            copy($file->getRealPath(), $livePath);
            $copied++;
        }

        // Clean up staging folder
        $this->deleteDirectory(storage_path('app/' . $this->stagingDir . '/' . explode('/', $rel)[1]));

        return response()->json([
            'success' => true,
            'copied'  => $copied,
            'skipped' => $skipped,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Private Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Compare every file in $sourceDir against the equivalent path in $liveBase.
     * Returns summary counts + per-file details.
     */
    private function compareFiles(string $sourceDir, string $liveBase): array
    {
        $files    = [];
        $newCount = $modifiedCount = $sameCount = 0;

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $relative  = substr($file->getRealPath(), strlen($sourceDir) + 1);
            $livePath  = $liveBase . DIRECTORY_SEPARATOR . $relative;
            $backupSize  = $file->getSize();
            $backupMtime = date('Y-m-d H:i:s', $file->getMTime());

            if (!file_exists($livePath)) {
                $status = 'new';
                $newCount++;
                $liveSize = null;
            } else {
                $liveSize  = filesize($livePath);
                $liveMtime = filemtime($livePath);
                if ($backupSize === $liveSize && $file->getMTime() === $liveMtime) {
                    $status = 'same';
                    $sameCount++;
                } else {
                    $status = 'modified';
                    $modifiedCount++;
                }
            }

            $files[] = [
                'path'         => str_replace('\\', '/', $relative),
                'status'       => $status,
                'backup_size'  => $this->formatBytes($backupSize),
                'live_size'    => $liveSize !== null ? $this->formatBytes($liveSize) : '—',
                'backup_mtime' => $backupMtime,
            ];
        }

        // Sort: new → modified → same
        usort($files, fn($a, $b) => strcmp(
            ['new' => 0, 'modified' => 1, 'same' => 2][$a['status']],
            ['new' => 0, 'modified' => 1, 'same' => 2][$b['status']]
        ));

        return [
            'total'          => count($files),
            'new_count'      => $newCount,
            'modified_count' => $modifiedCount,
            'same_count'     => $sameCount,
            'files'          => $files,
        ];
    }

    /** Load the meta JSON (notes + folder list per backup) */
    private function loadMeta(): array
    {
        if (!Storage::disk($this->backupDisk)->exists($this->metaFile)) {
            return [];
        }
        return json_decode(Storage::disk($this->backupDisk)->get($this->metaFile), true) ?? [];
    }

    /** Save note + folders for a specific backup filename */
    private function saveMeta(string $filename, string $note, array $folders): void
    {
        $meta = $this->loadMeta();
        $meta[$filename] = [
            'note'    => $note,
            'folders' => $folders,
            'created' => now()->toDateTimeString(),
        ];
        Storage::disk($this->backupDisk)->put($this->metaFile, json_encode($meta, JSON_PRETTY_PRINT));
    }

    /** Remove a filename from meta */
    private function deleteMeta(string $filename): void
    {
        $meta = $this->loadMeta();
        unset($meta[$filename]);
        Storage::disk($this->backupDisk)->put($this->metaFile, json_encode($meta, JSON_PRETTY_PRINT));
    }

    /** Recursively delete a directory */
    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) return;
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getRealPath()) : unlink($item->getRealPath());
        }
        rmdir($dir);
    }

    /** Human-readable byte size */
    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576)    return round($bytes / 1048576, 2)    . ' MB';
        if ($bytes >= 1024)       return round($bytes / 1024, 2)       . ' KB';
        return $bytes . ' B';
    }

    /*
    |--------------------------------------------------------------------------
    | runAjax()  –  Create backup via AJAX with progress simulation
    |--------------------------------------------------------------------------
    | POST /backup/run-ajax
    */
    public function runAjax(Request $request)
    {
        $request->validate([
            'folders'   => 'required|array|min:1',
            'folders.*' => 'in:' . implode(',', $this->allowedFolders),
            'note'      => 'nullable|string|max:300',
        ]);

        $selectedFolders = $request->input('folders');
        $note            = trim($request->input('note', ''));

        // Ensure storage directory exists
        Storage::disk($this->backupDisk)->makeDirectory($this->backupDir);

        $filename  = 'website_backup_' . date('Ymd_His') . '.zip';
        $zipPath   = storage_path('app/' . $this->backupDir . '/' . $filename);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return response()->json(['success' => false, 'message' => 'Could not create ZIP file.']);
        }

        $fileCount = 0;
        $exclusions = [
            storage_path('app/' . $this->backupDir),
            storage_path('app/' . $this->stagingDir),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('logs'),
            base_path('vendor'),
            base_path('.git'),
            base_path('node_modules'),
        ];

        foreach ($selectedFolders as $folder) {
            $folderPath = base_path($folder);
            if (!is_dir($folderPath)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($folderPath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($iterator as $file) {
                if (!$file->isFile()) {
                    continue;
                }

                $realPath = $file->getRealPath();

                // Skip excluded paths
                $shouldExclude = false;
                foreach ($exclusions as $excludePath) {
                    if (str_starts_with($realPath, $excludePath)) {
                        $shouldExclude = true;
                        break;
                    }
                }
                if ($shouldExclude) {
                    continue;
                }

                $archivePath = $folder . DIRECTORY_SEPARATOR . substr($realPath, strlen($folderPath) + 1);
                $zip->addFile($realPath, $archivePath);
                $fileCount++;
            }
        }

        $zip->close();

        // Save note in meta JSON
        $this->saveMeta($filename, $note, $selectedFolders);

        return response()->json([
            'success' => true,
            'filename' => $filename,
            'files_count' => $fileCount,
            'message' => "Backup created: {$filename}"
        ]);
    }



}