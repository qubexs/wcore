<?php

namespace App\Modules\FileHosting\Support;

use App\Modules\FileHosting\Models\Folder;
use App\Modules\FileHosting\Models\File;
use App\Modules\FileHosting\Models\FolderPermission;
use Illuminate\Contracts\Auth\Authenticatable;

class PermissionHelper
{
    /**
     * Check whether the given user can perform $permission on a folder.
     * Checks role-level permissions first, then folder-level grants.
     */
    public static function canOnFolder(
        Authenticatable $user,
        Folder $folder,
        string $permission
    ): bool {
        // SuperAdmin always passes
        if (static::userHasRole($user, 'SuperAdmin')) return true;

        // Role-level check
        if (static::roleAllows($user, 'folder', $permission)) return true;

        // Owner always has full control of own folder
        if ((int)$folder->user_id === (int)$user->id) return true;

        // Folder-level explicit grant
        return FolderPermission::where('folder_id', $folder->id)
            ->active()
            ->forUser($user->id)
            ->get()
            ->contains(fn ($grant) => $grant->hasPermission($permission));
    }

    /**
     * Check whether the given user can perform $permission on a file.
     */
    public static function canOnFile(
        Authenticatable $user,
        File $file,
        string $permission
    ): bool {
        if (static::userHasRole($user, 'SuperAdmin')) return true;

        if (static::roleAllows($user, 'file', $permission)) return true;

        // "own" permission variants
        $ownPermission = $permission . '_own';
        if ((int)$file->uploaded_by === (int)$user->id && static::roleAllows($user, 'file', $ownPermission)) {
            return true;
        }

        // Inherit from the file's parent folder grant
        if ($file->folder_id) {
            $folder = $file->folder;
            if ($folder && static::canOnFolder($user, $folder, $permission)) return true;
        }

        return false;
    }

    /**
     * Return all permissions a user has on a folder (merged role + grant).
     */
    public static function effectiveFolderPermissions(Authenticatable $user, Folder $folder): array
    {
        $role  = static::rolePermissions($user, 'folder');
        $grant = FolderPermission::where('folder_id', $folder->id)
            ->active()
            ->forUser($user->id)
            ->get()
            ->flatMap(fn ($g) => $g->permissions ?? [])
            ->unique()
            ->values()
            ->toArray();

        return array_unique(array_merge($role, $grant));
    }

    // ---------------------------------------------------------------
    // Private helpers
    // ---------------------------------------------------------------

    private static function userHasRole(Authenticatable $user, string $role): bool
    {
        // Works with Spatie Laravel Permission or a simple `role` attribute
        if (method_exists($user, 'hasRole')) return $user->hasRole($role);
        return ($user->role ?? null) === $role;
    }

    private static function roleAllows(Authenticatable $user, string $type, string $permission): bool
    {
        return in_array($permission, static::rolePermissions($user, $type));
    }

    private static function rolePermissions(Authenticatable $user, string $type): array
    {
        $roleName    = method_exists($user, 'getRoleNames')
            ? $user->getRoleNames()->first()
            : ($user->role ?? 'Employee');

        $permissions = config("filehosting.roles.{$roleName}.{$type}", []);
        return is_array($permissions) ? $permissions : [];
    }
}
