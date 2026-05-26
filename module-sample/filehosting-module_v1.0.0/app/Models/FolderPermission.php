<?php

namespace App\Modules\FileHosting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FolderPermission extends Model
{
    protected $fillable = [
        'folder_id', 'grantee_type', 'grantee_id',
        'permissions', 'granted_by', 'expires_at',
    ];

    protected $casts = [
        'permissions' => 'array',
        'expires_at'  => 'datetime',
    ];

    // ---------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'folder_id');
    }

    public function grantee(): MorphTo
    {
        return $this->morphTo();
    }

    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'granted_by');
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isExpired()) return false;
        return in_array($permission, $this->permissions ?? []);
    }

    public function addPermission(string $permission): void
    {
        $perms = $this->permissions ?? [];
        if (!in_array($permission, $perms)) {
            $perms[] = $permission;
            $this->update(['permissions' => $perms]);
        }
    }

    public function removePermission(string $permission): void
    {
        $this->update([
            'permissions' => array_values(
                array_filter($this->permissions ?? [], fn ($p) => $p !== $permission)
            ),
        ]);
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('grantee_type', \App\Models\User::class)
                     ->where('grantee_id', $userId);
    }
}
