<?php

namespace App\Services;

use App\Models\UserActivityLog;
use Illuminate\Database\Eloquent\Model;

/**
 * app\Services\ActivityLogService.php
 * 
 * Activity Logging Service
 * 
 * Simple service to log user activities throughout the application.
 * 
 * Usage:
 *   ActivityLog::log('user_created', 'Created user John Doe', $user, 'success');
 *   ActivityLog::success('backup_completed', 'Database backup completed');
 *   ActivityLog::error('backup_failed', 'Database backup failed', ['error' => $error]);
 */
class ActivityLogService
{
    /**
     * Log an activity with detailed metadata
     */
    public static function log(
        string $actionType,
        string $description,
        $metadata = null,
        string $status = 'success'
    ): UserActivityLog
    {
        $logData = [
            'user_id' => auth()->id(),
            'action_type' => $actionType,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'metadata' => is_array($metadata) ? $metadata : ['data' => $metadata],
        ];

        // If metadata is a Model, extract relevant info
        if ($metadata instanceof Model) {
            $logData['target_user_id'] = $metadata->id ?? null;
            $logData['metadata'] = [
                'model' => class_basename($metadata),
                'id' => $metadata->id,
                'data' => $metadata->toArray(),
            ];
        }

        // Add status to metadata
        $logData['metadata']['status'] = $status;

        return UserActivityLog::create($logData);
    }

    /**
     * Log a successful action
     */
    public static function success(
        string $actionType,
        string $description,
        $metadata = null
    ): UserActivityLog
    {
        return self::log($actionType, $description, $metadata, 'success');
    }

    /**
     * Log a failed action
     */
    public static function error(
        string $actionType,
        string $description,
        $metadata = null
    ): UserActivityLog
    {
        return self::log($actionType, $description, $metadata, 'error');
    }

    /**
     * Log user creation
     */
    public static function userCreated($user): UserActivityLog
    {
        return self::success('user_created', "Created user: {$user->name} {$user->last_name}", [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->roles?->first()?->name,
        ]);
    }

    /**
     * Log user update
     */
    public static function userUpdated($user, array $changes = []): UserActivityLog
    {
        $changedFields = implode(', ', array_keys($changes ?: []));
        return self::success('user_updated', "Updated user: {$user->name} {$user->last_name}" . ($changedFields ? " - Changed: {$changedFields}" : ''), [
            'user_id' => $user->id,
            'changed_fields' => array_keys($changes ?: []),
            'changes' => $changes,
        ]);
    }

    /**
     * Log user deletion
     */
    public static function userDeleted($user): UserActivityLog
    {
        return self::success('user_deleted', "Deleted user: {$user->name} {$user->last_name}", [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }

    /**
     * Log user status toggle
     */
    public static function userStatusChanged($user, string $oldStatus, string $newStatus): UserActivityLog
    {
        return self::success('user_status_changed', "Changed {$user->name} status from '{$oldStatus}' to '{$newStatus}'", [
            'user_id' => $user->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);
    }

    /**
     * Log user login
     */
    public static function userLogin($user): UserActivityLog
    {
        return self::success('user_login', "User logged in: {$user->name}", [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }

    /**
     * Log user logout
     */
    public static function userLogout($user): UserActivityLog
    {
        return self::success('user_logout', "User logged out: {$user->name}", [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Log database backup
     */
    public static function databaseBackup($filename, $size = null): UserActivityLog
    {
        return self::success('database_backup', "Database backup created: {$filename}", [
            'filename' => $filename,
            'size' => $size,
            'timestamp' => now(),
        ]);
    }

    /**
     * Log database restore
     */
    public static function databaseRestore($filename, $success = true): UserActivityLog
    {
        return self::log(
            'database_restore',
            $success ? "Database restored from: {$filename}" : "Database restore failed: {$filename}",
            ['filename' => $filename],
            $success ? 'success' : 'error'
        );
    }

    /**
     * Log website backup
     */
    public static function websiteBackup($filename, $size = null): UserActivityLog
    {
        return self::success('website_backup', "Website backup created: {$filename}", [
            'filename' => $filename,
            'size' => $size,
            'timestamp' => now(),
        ]);
    }

    /**
     * Log file upload
     */
    public static function fileUploaded($filename, $size = null, $filetype = null): UserActivityLog
    {
        return self::success('file_uploaded', "Uploaded file: {$filename}", [
            'filename' => $filename,
            'size' => $size,
            'filetype' => $filetype,
            'timestamp' => now(),
        ]);
    }

    /**
     * Log file delete
     */
    public static function fileDeleted($filename): UserActivityLog
    {
        return self::success('file_deleted', "Deleted file: {$filename}", [
            'filename' => $filename,
        ]);
    }

    /**
     * Log role creation
     */
    public static function roleCreated($role, $permissions = []): UserActivityLog
    {
        return self::success('role_created', "Created role: {$role->name}", [
            'role_id' => $role->id,
            'role_name' => $role->name,
            'permissions_count' => count($permissions),
        ]);
    }

    /**
     * Log role update
     */
    public static function roleUpdated($role, $permissions = []): UserActivityLog
    {
        return self::success('role_updated', "Updated role: {$role->name}", [
            'role_id' => $role->id,
            'role_name' => $role->name,
            'permissions_count' => count($permissions),
        ]);
    }

    /**
     * Log role deletion
     */
    public static function roleDeleted($roleName): UserActivityLog
    {
        return self::success('role_deleted', "Deleted role: {$roleName}", [
            'role_name' => $roleName,
        ]);
    }

    /**
     * Log permission change
     */
    public static function permissionChanged($user, $oldPermissions, $newPermissions): UserActivityLog
    {
        return self::success('permission_changed', "Changed permissions for {$user->name}", [
            'user_id' => $user->id,
            'removed' => array_diff($oldPermissions, $newPermissions),
            'added' => array_diff($newPermissions, $oldPermissions),
        ]);
    }

    /**
     * Log settings change
     */
    public static function settingsChanged($setting, $oldValue, $newValue): UserActivityLog
    {
        return self::success('settings_changed', "Changed setting: {$setting}", [
            'setting_name' => $setting,
            'old_value' => $oldValue,
            'new_value' => $newValue,
        ]);
    }

    /**
     * Log module activation
     */
    public static function moduleActivated($moduleName): UserActivityLog
    {
        return self::success('module_activated', "Activated module: {$moduleName}", [
            'module' => $moduleName,
        ]);
    }

    /**
     * Log module deactivation
     */
    public static function moduleDeactivated($moduleName): UserActivityLog
    {
        return self::success('module_deactivated', "Deactivated module: {$moduleName}", [
            'module' => $moduleName,
        ]);
    }

    /**
     * Log custom action
     */
    public static function custom(string $actionType, string $description, array $metadata = []): UserActivityLog
    {
        return self::log($actionType, $description, $metadata);
    }
}