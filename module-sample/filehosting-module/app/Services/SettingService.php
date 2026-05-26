<?php

namespace App\Modules\FileHosting\Services;

use Illuminate\Support\Facades\Cache;

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
            $defaults = config('filehosting', []);
            // Optionally merge from a settings table if your app has one
            return $defaults;
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
     * Update a setting value at runtime and persist to cache.
     * For persistent changes, write to your settings DB table here.
     */
    public function set(string $key, mixed $value): void
    {
        $settings = $this->all();
        data_set($settings, $key, $value);
        Cache::put(self::CACHE_KEY, $settings, self::CACHE_TTL);
    }

    /**
     * Flush the settings cache (useful after saving new values).
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
     * Return maximum upload size in bytes from PHP ini.
     */
    public function maxUploadBytes(): int
    {
        $val = ini_get('upload_max_filesize');
        return $this->parseIniSize($val);
    }

    private function parseIniSize(string $size): int
    {
        $unit  = strtolower(substr($size, -1));
        $value = (int) $size;
        return match ($unit) {
            'g'     => $value * 1024 * 1024 * 1024,
            'm'     => $value * 1024 * 1024,
            'k'     => $value * 1024,
            default => $value,
        };
    }
}
