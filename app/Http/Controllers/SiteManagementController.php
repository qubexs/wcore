<?php

// app/Http/Controllers/SiteManagementController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiteManagementController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Disk / paths  — must match BackupController exactly
    |--------------------------------------------------------------------------
    */
    private string $disk        = 'local';
    private string $dbDir       = 'database-backups';
    private string $webDir      = 'website-backups';
    private string $webMetaFile = 'website-backups/.meta.json';


    /*
    |--------------------------------------------------------------------------
    | index()
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        // ── Database backups ─────────────────────────────────────────────
        $databaseBackups = [];

        foreach (Storage::disk($this->disk)->files($this->dbDir) as $file) {
            $name = basename($file);
            if (str_starts_with($name, '.')) continue;

            $databaseBackups[] = [
                'name' => $name,
                'size' => $this->bytes(Storage::disk($this->disk)->size($file)),
                'time' => date('Y-m-d H:i:s', Storage::disk($this->disk)->lastModified($file)),
            ];
        }

        usort($databaseBackups, fn($a, $b) => strcmp($b['time'], $a['time']));


        // ── Website backups ──────────────────────────────────────────────
        //
        // IMPORTANT: each entry MUST include 'note' and 'folders'
        // so @json($backup) in the blade passes all fields to wbShowInfo().
        // Without these keys the Info modal shows dash for every field.
        //
        $websiteBackups = [];

        // Load the meta sidecar written by BackupController::saveMeta()
        $meta = [];
        if (Storage::disk($this->disk)->exists($this->webMetaFile)) {
            $meta = json_decode(
                Storage::disk($this->disk)->get($this->webMetaFile),
                true
            ) ?? [];
        }

        foreach (Storage::disk($this->disk)->files($this->webDir) as $file) {
            $name = basename($file);
            if (str_starts_with($name, '.')) continue;  // skip .meta.json etc.

            $websiteBackups[] = [
                'name'    => $name,
                'size'    => $this->bytes(Storage::disk($this->disk)->size($file)),
                'time'    => date('Y-m-d H:i:s', Storage::disk($this->disk)->lastModified($file)),
                'note'    => $meta[$name]['note']    ?? '',
                'folders' => $meta[$name]['folders'] ?? [],
            ];
        }

        usort($websiteBackups, fn($a, $b) => strcmp($b['time'], $a['time']));


        return view('site-management.index', [
            'backups'        => $databaseBackups,
            'websiteBackups' => $websiteBackups,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Helper
    |--------------------------------------------------------------------------
    */
    private function bytes(int $b): string
    {
        if ($b >= 1_073_741_824) return round($b / 1_073_741_824, 2) . ' GB';
        if ($b >= 1_048_576)    return round($b / 1_048_576, 2)    . ' MB';
        if ($b >= 1_024)        return round($b / 1_024, 2)        . ' KB';
        return $b . ' B';
    }
}