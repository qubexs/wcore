<?php

namespace App\Core\Module;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use ZipArchive;

class ModuleInstaller
{
    protected ModuleManager $manager;

    public function __construct(ModuleManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Install a module from uploaded ZIP
     */
    public function install(string $zipPath, string $slug): void
    {
        $modulesRoot = base_path('modules');           // ✅ SINGLE SOURCE
        $modulePath  = "{$modulesRoot}/{$slug}";
        $tempPath    = "{$modulesRoot}/{$slug}_temp";

        if (!File::exists($zipPath)) {
            throw new Exception("Uploaded ZIP file does not exist.");
        }

        // Ensure modules root exists
        File::ensureDirectoryExists($modulesRoot);

        if (File::exists($modulePath)) {
            throw new Exception("Module '{$slug}' already exists.");
        }

        // Prepare temp directory
        File::deleteDirectory($tempPath);
        File::makeDirectory($tempPath, 0755, true);

        // Open ZIP
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new Exception("Failed to open ZIP file.");
        }

        $zip->extractTo($tempPath);
        $zip->close();

        /**
         * Handle ZIP structure:
         * - ZIP/module.json
         * - ZIP/{folder}/module.json
         */
        $directories = File::directories($tempPath);

        if (
            count($directories) === 1 &&
            File::exists($directories[0] . '/module.json')
        ) {
            // ZIP contains a root folder → flatten it
            File::move($directories[0], $modulePath);
            File::deleteDirectory($tempPath);
        } else {
            // ZIP already flat
            File::move($tempPath, $modulePath);
        }

        // Final safety check
        if (!File::exists("{$modulePath}/module.json")) {
            File::deleteDirectory($modulePath);
            throw new Exception("module.json not found for module '{$slug}'.");
        }

        // Sync DB
        DB::table('modules')->updateOrInsert(
            ['slug' => $slug],
            [
                'name'    => $slug,
                'version' => '1.0.0',
                'active'  => 0,
            ]
        );
    }

    /**
     * Uninstall module
     */
    public function uninstall(string $slug): void
    {
        $modulePath = base_path("modules/{$slug}");

        if (!File::exists($modulePath)) {
            throw new Exception("Module '{$slug}' folder does not exist.");
        }

        File::deleteDirectory($modulePath);

        DB::table('modules')->where('slug', $slug)->delete();
    }
}
