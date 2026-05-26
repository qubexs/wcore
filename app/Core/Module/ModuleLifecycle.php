<?php
// app/Core/Module/ModuleLifecycle.php

namespace App\Core\Module;

use Illuminate\Support\Facades\File;

class ModuleLifecycle
{
    protected ModuleManager $manager;

    public function __construct(ModuleManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Activate module
     * - Ensure module directory exists
     * - Create ACTIVE flag file
     */
    public function activate(string $slug): void
    {
        $modulePath = base_path("modules/{$slug}");

        // ✅ Ensure module directory exists
        if (!File::exists($modulePath)) {
            throw new \Exception("Module directory not found: {$slug}");
        }

        // ✅ Create ACTIVE file
        File::put("{$modulePath}/ACTIVE", 'active');
    }

    /**
     * Deactivate module
     * - Remove ACTIVE flag file
     */
    public function deactivate(string $slug): void
    {
        $activeFile = base_path("modules/{$slug}/ACTIVE");

        if (File::exists($activeFile)) {
            File::delete($activeFile);
        }
    }
}
