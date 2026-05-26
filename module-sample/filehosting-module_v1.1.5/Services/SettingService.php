<?php

namespace Modules\FileHosting\Services;

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

            // Merge: DB values override defaults
            // Use array_replace_recursive for nested arrays
            return array_replace_recursive($defaults, $dbSettings);
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
    }

    /**
     * Flush the settings cache.
     */
    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
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
        // Prefer the configured limit from settings
        $configuredMb = $this->get('limits.max_upload_size_mb');
        if ($configuredMb !== null) {
            return (int) $configuredMb * 1024 * 1024;
        }

        // Fallback to PHP ini value
        $ini = ini_get('upload_max_filesize');
        return $this->parseIniSize($ini);
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