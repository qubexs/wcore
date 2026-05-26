<?php
// app/Core/Module/ModuleRegistry.php
namespace App\Core\Module;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ModuleRegistry
{
    protected ModuleManager $manager;
    protected ModuleLifecycle $lifecycle;
    protected ?Collection $activeCache = null;

    public function __construct(ModuleManager $manager, ModuleLifecycle $lifecycle)
    {
        $this->manager = $manager;
        $this->lifecycle = $lifecycle;
    }

    /**
     * Return all modules (from DB if exists, otherwise fallback to disk)
     */
    public function all(): Collection
    {
        $modules = DB::table('modules')->get()->map(fn($m) => [
            'slug'    => $m->slug,
            'name'    => $m->name,
            'version' => $m->version ?? '1.0.0',
            'active'  => (bool)$m->active,
        ])->toArray();

        // fallback: include modules on disk that are not in DB
        $diskModules = $this->manager->all(); // returns array
        foreach ($diskModules as $m) {
            if (!collect($modules)->contains(fn($db) => $db['slug'] === $m['slug'])) {
                $modules[] = [
                    'slug' => $m['slug'],
                    'name' => $m['name'],
                    'version' => $m['version'] ?? '1.0.0',
                    'active' => false,
                ];
            }
        }

        return collect($modules);
    }

    /**
     * Active modules
     */
    public function active(): Collection
    {
        if ($this->activeCache) return $this->activeCache;

        return $this->activeCache = $this->all()
            ->filter(fn($m) => isset($m['slug']) && $this->lifecycle->isActive($m['slug']))
            ->values();
    }

    /**
     * Inactive modules
     */
    public function inactive(): Collection
    {
        return $this->all()->reject(fn($m) => $this->lifecycle->isActive($m['slug']))->values();
    }

    /**
     * Find a module by slug
     */
    public function find(string $slug): ?array
    {
        return $this->all()->first(fn($m) => $m['slug'] === $slug);
    }

    /**
     * Check if a module is active
     */
    public function isActive(string $slug): bool
    {
        return $this->active()->contains(fn($m) => $m['slug'] === $slug);
    }
}
