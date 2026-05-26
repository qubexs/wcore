<?php

namespace App\Modules\FileHosting\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\ModuleSetting; // or your own model

class SettingService
{
    protected const CACHE_KEY = 'filehosting_settings';
    protected const CACHE_TTL = 3600; // 1 hour

    /**
     * Get all settings, merging DB overrides with config defaults.
     */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            // Start with defaults from config file
            $defaults = config('filehosting', []);

            // Fetch all DB settings for this module
            $dbSettings = ModuleSetting::where('module', 'filehosting')
                ->pluck('value', 'key')
                ->toArray();

            // Convert flat DB keys to nested arrays (e.g., 'limits.max_upload_size_mb' -> ['limits' => ['max_upload_size_mb' => '10']])
            $nestedDbSettings = [];
            foreach ($dbSettings as $key => $value) {
                data_set($nestedDbSettings, $key, $value);
            }

            // Merge: DB values override defaults
            return array_replace_recursive($defaults, $nestedDbSettings);
        });
    }

    /**
     * Get a single setting value by dot-notation key.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return data_get($this->all(), $key, $default);
    }

    /**
     * Update a setting value persistently.
     */
    public function set(string $key, mixed $value): void
    {
        // Convert value to a storable format
        $storable = $this->toStorable($value);

        // Update or create the DB record
        ModuleSetting::updateOrCreate(
            ['module' => 'filehosting', 'key' => $key],
            ['value' => $storable]
        );

        // Clear cache so next read picks up the change
        $this->flush();
        
        // Also clear the Laravel config cache
        if (app()->has('config')) {
            app('config')->forget('filehosting');
        }
    }

    /**
     * Flush the settings cache.
     */
    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
        // Also clear any Laravel config cache
        if (app()->bound('config')) {
            app()->forgetInstance('config');
        }
    }

    /**
     * Return only the limit-related settings.
     */
    public function limits(): array
    {
        return $this->get('limits', []);
    }

    /**
     * Return maximum upload size in bytes from the configured limit (or PHP ini as fallback).
     */
    public function maxUploadBytes(): int
    {
        // Force fresh read - skip cache
        $settings = $this->allFresh();
        
        $configuredMb = $settings['limits']['max_upload_size_mb'] 
            ?? $settings['max_upload_size_mb'] ?? null;
            
        if ($configuredMb !== null && is_numeric($configuredMb)) {
            return (int) $configuredMb * 1024 * 1024;
        }

        // Fallback to config default (100MB)
        $configMb = config('filehosting.limits.max_upload_size_mb', 100);
        return $configMb * 1024 * 1024;
    }
    
    /**
     * Get fresh settings without cache.
     */
    public function allFresh(): array
    {
        // Start with defaults from config file
        $defaults = config('filehosting', []);

        // Fetch all DB settings for this module
        $dbSettings = ModuleSetting::where('module', 'filehosting')
            ->pluck('value', 'key')
            ->toArray();

        // Convert flat DB keys to nested arrays
        $nestedDbSettings = [];
        foreach ($dbSettings as $key => $value) {
            data_set($nestedDbSettings, $key, $value);
        }

        // Merge: DB values override defaults
        return array_replace_recursive($defaults, $nestedDbSettings);
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function toStorable(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_array($value)) {
            return json_encode($value);
        }
        return (string) $value;
    }

    private function parseIniSize(string $size): int
    {
        $unit = strtolower(substr($size, -1));
        $value = (int) $size;
        return match ($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => $value,
        };
    }
}