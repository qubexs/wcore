<?php

namespace App\Modules\FileHosting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Folder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parent_id', 'user_id', 'name', 'slug',
        'description', 'path', 'depth', 'visibility',
    ];

    protected $casts = [
        'depth'      => 'integer',
        'deleted_at' => 'datetime',
    ];

    // ---------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class, 'folder_id');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(FolderPermission::class, 'folder_id');
    }

    public function stats(): HasMany
    {
        return $this->hasMany(FileStat::class, 'folder_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    public function getAncestors(): \Illuminate\Support\Collection
    {
        $ancestors = collect();
        $current   = $this->parent;

        while ($current) {
            $ancestors->prepend($current);
            $current = $current->parent;
        }

        return $ancestors;
    }

    public function getBreadcrumb(): \Illuminate\Support\Collection
    {
        return $this->getAncestors()->push($this);
    }

    public function totalSize(): int
    {
        $directSize = $this->files()->sum('size');
        
        $allFolderIds = $this->getAllDescendantIds();
        if (!empty($allFolderIds)) {
            $descendantSize = \DB::table('files')
                ->whereIn('folder_id', $allFolderIds)
                ->sum('size');
            return $directSize + $descendantSize;
        }
        return $directSize;
    }

    public function totalFiles(): int
    {
        $directCount = $this->files()->count();
        
        $allFolderIds = $this->getAllDescendantIds();
        if (!empty($allFolderIds)) {
            $descendantCount = \DB::table('files')
                ->whereIn('folder_id', $allFolderIds)
                ->count();
            return $directCount + $descendantCount;
        }
        return $directCount;
    }

    protected function getAllDescendantIds(): array
    {
        return $this->getDescendantIdsFromTree($this);
    }

    protected function getDescendantIdsFromTree(Folder $folder): array
    {
        $ids = [];
        
        // Try to get from eager loaded relationship first
        $children = $this->getRelation('children');
        
        // If not loaded via setRelation, check if it's cached on the attribute
        if ($children === null) {
            $children = $folder->children()->get();
        }
        
        foreach ($children as $child) {
            $ids[] = $child->id;
            $childIds = $this->getDescendantIdsFromTree($child);
            $ids = array_merge($ids, $childIds);
        }
        
        return $ids;
    }
}
