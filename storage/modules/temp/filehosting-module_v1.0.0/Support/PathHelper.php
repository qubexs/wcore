<?php

namespace App\Modules\FileHosting\Support;

use App\Modules\FileHosting\Models\Folder;
use Illuminate\Support\Str;

class PathHelper
{
    /**
     * Build a full folder path from parent path + new folder name.
     * e.g.  "HR/Contracts" + "2024" => "HR/Contracts/2024"
     */
    public static function buildFolderPath(?string $parentPath, string $name): string
    {
        $name = self::sanitize($name);
        return $parentPath ? ltrim($parentPath . '/' . $name, '/') : $name;
    }

    /**
     * Build a unique storage path for an uploaded file.
     * Format:  uploads/{year}/{month}/{uuid}.{ext}
     */
    public static function buildFilePath(string $extension): string
    {
        return sprintf(
            'uploads/%s/%s/%s.%s',
            now()->year,
            now()->format('m'),
            Str::uuid(),
            strtolower($extension)
        );
    }

    /**
     * Build thumbnail storage path.
     * Format:  thumbnails/{year}/{month}/{uuid}_{size}.jpg
     */
    public static function buildThumbnailPath(string $size): string
    {
        return sprintf(
            'thumbnails/%s/%s/%s_%s.jpg',
            now()->year,
            now()->format('m'),
            Str::uuid(),
            $size
        );
    }

    /**
     * Generate a unique slug for a folder name within a parent.
     */
    public static function generateSlug(string $name, ?int $parentId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i    = 1;

        while (Folder::where('slug', $slug)->where('parent_id', $parentId)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    /**
     * Sanitize a folder/file name — strip dangerous characters.
     */
    public static function sanitize(string $name): string
    {
        // Remove null bytes, path traversal, and reserved chars
        $name = str_replace(["\0", '..', '/', '\\'], '', $name);
        $name = preg_replace('/[<>:"|?*]/', '', $name);
        return trim($name);
    }

    /**
     * Check whether a name is forbidden per config.
     */
    public static function isForbiddenName(string $name): bool
    {
        $forbidden = config('filehosting.limits.forbidden_names', []);
        $reserved  = config('filehosting.limits.reserved_paths', []);

        return in_array(strtoupper($name), array_map('strtoupper', $forbidden))
            || in_array(strtolower($name), array_map('strtolower', $reserved));
    }

    /**
     * Rebuild the paths for all descendants of a folder after a move/rename.
     */
    public static function rebuildDescendantPaths(Folder $folder): void
    {
        foreach ($folder->children as $child) {
            $child->path  = self::buildFolderPath($folder->path, $child->name);
            $child->depth = $folder->depth + 1;
            $child->save();
            self::rebuildDescendantPaths($child);
        }
    }

    /**
     * Extract extension from an original filename safely.
     */
    public static function extension(string $filename): string
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }
}
