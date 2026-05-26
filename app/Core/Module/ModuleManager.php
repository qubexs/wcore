<?php
// app/Core/Module/ModuleManager.php
namespace App\Core\Module;

class ModuleManager
{
    protected array $modules = [];

    public function __construct()
    {
        $this->loadModules();
    }

    protected function loadModules(): void
    {
        $modulesPath = base_path('modules');

        if (!is_dir($modulesPath)) {
            return;
        }

        foreach (scandir($modulesPath) as $folder) {
            if ($folder === '.' || $folder === '..') continue;

            $jsonFile = "{$modulesPath}/{$folder}/module.json";

            if (file_exists($jsonFile)) {
                $this->modules[] = json_decode(file_get_contents($jsonFile), true);
            }
        }
    }

    public function all(): array
    {
        return $this->modules;
    }
}
