<?php

namespace App\Modules\FileHosting\Services;

use App\Modules\FileHosting\Models\Folder;
use App\Modules\FileHosting\Models\FileStat;
use App\Modules\FileHosting\Support\PathHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FolderService
{
    /**
     * Create a new folder under an optional parent.
     */
    public function create(array $data, int $userId): Folder
    {
        $name     = PathHelper::sanitize($data['name']);
        $parentId = $data['parent_id'] ?? null;

        if (PathHelper::isForbiddenName($name)) {
            throw ValidationException::withMessages(['name' => 'This folder name is reserved.']);
        }

        $parent = $parentId ? Folder::findOrFail($parentId) : null;

        $maxDepth = config('filehosting.limits.max_folder_depth', 10);
        if ($parent && $parent->depth >= $maxDepth - 1) {
            throw ValidationException::withMessages(['name' => "Maximum folder depth of {$maxDepth} reached."]);
        }

        return DB::transaction(function () use ($name, $parentId, $parent, $userId, $data) {
            $folder = Folder::create([
                'parent_id'   => $parentId,
                'uploaded_by' => $userId,
                'name'        => $name,
                'slug'        => PathHelper::generateSlug($name, $parentId),
                'description' => $data['description'] ?? null,
                'path'        => PathHelper::buildFolderPath($parent?->path, $name),
                'depth'       => $parent ? $parent->depth + 1 : 0,
                'visibility'  => $data['visibility'] ?? 'private',
            ]);

            FileStat::record(FileStat::ACTION_CREATE_FOLDER, null, $folder->id, [
                'folder_name' => $name,
            ]);

            return $folder;
        });
    }

    /**
     * Rename a folder and rebuild all descendant paths.
     */
    public function rename(Folder $folder, string $newName): Folder
    {
        $newName = PathHelper::sanitize($newName);

        if (PathHelper::isForbiddenName($newName)) {
            throw ValidationException::withMessages(['name' => 'This folder name is reserved.']);
        }

        DB::transaction(function () use ($folder, $newName) {
            $parent     = $folder->parent;
            $oldName    = $folder->name;
            $folder->name = $newName;
            $folder->slug = PathHelper::generateSlug($newName, $folder->parent_id);
            $folder->path = PathHelper::buildFolderPath($parent?->path, $newName);
            $folder->save();

            PathHelper::rebuildDescendantPaths($folder);

            FileStat::record(FileStat::ACTION_RENAME, null, $folder->id, [
                'old_name' => $oldName,
                'new_name' => $newName,
            ]);
        });

        return $folder->fresh();
    }

    /**
     * Move a folder to a new parent (or root).
     */
    public function move(Folder $folder, ?int $newParentId): Folder
    {
        // Prevent moving into own descendant
        if ($newParentId && $this->isDescendant($folder, $newParentId)) {
            throw ValidationException::withMessages(['parent_id' => 'Cannot move a folder into its own subfolder.']);
        }

        $newParent = $newParentId ? Folder::findOrFail($newParentId) : null;

        $maxDepth  = config('filehosting.limits.max_folder_depth', 10);
        $newDepth  = $newParent ? $newParent->depth + 1 : 0;
        if ($newDepth >= $maxDepth) {
            throw ValidationException::withMessages(['parent_id' => "Maximum folder depth of {$maxDepth} reached."]);
        }

        DB::transaction(function () use ($folder, $newParentId, $newParent, $newDepth) {
            $oldPath            = $folder->path;
            $folder->parent_id  = $newParentId;
            $folder->path       = PathHelper::buildFolderPath($newParent?->path, $folder->name);
            $folder->depth      = $newDepth;
            $folder->save();

            PathHelper::rebuildDescendantPaths($folder);

            FileStat::record(FileStat::ACTION_MOVE, null, $folder->id, [
                'old_path'       => $oldPath,
                'new_path'       => $folder->path,
                'new_parent_id'  => $newParentId,
            ]);
        });

        return $folder->fresh();
    }

    /**
     * Soft-delete a folder (cascades to children via DB constraint).
     */
    public function delete(Folder $folder): void
    {
        DB::transaction(function () use ($folder) {
            FileStat::record(FileStat::ACTION_DELETE_FOLDER, null, $folder->id, [
                'folder_name' => $folder->name,
                'path'        => $folder->path,
            ]);
            $folder->delete();
        });
    }

    /**
     * Return the tree of folders visible to a user.
     */
    public function treeForUser(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return Folder::whereNull('parent_id')
            ->with(['children.children'])
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('visibility', 'public');
            })
            ->orderBy('name')
            ->get();
    }

    // ---------------------------------------------------------------
    // Private
    // ---------------------------------------------------------------

    private function isDescendant(Folder $folder, int $targetId): bool
    {
        $children = Folder::where('parent_id', $folder->id)->get();
        foreach ($children as $child) {
            if ($child->id === $targetId) return true;
            if ($this->isDescendant($child, $targetId)) return true;
        }
        return false;
    }
}
