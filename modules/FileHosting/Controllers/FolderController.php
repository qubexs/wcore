<?php

// modules/filehosting/app/Controllers/FolderController.php
namespace App\Modules\FileHosting\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Modules\FileHosting\Models\Folder;

class FolderController extends Controller
{
    /**
     * POST /filehosting/folders
     * Called by createFolder() in the blade JS
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'parent_id'   => 'nullable|integer|exists:folders,id',
            'visibility'  => 'nullable|in:public,private,restricted',
            'description' => 'nullable|string|max:1000',
        ]);

        $name      = trim($request->name);
        $parentId  = $request->parent_id ?: null;
        $parent    = $parentId ? Folder::find($parentId) : null;

        // ✅ Build slug — unique per parent scope
        $baseSlug  = Str::slug($name);
        $slug      = $this->uniqueSlug($baseSlug, $parentId);

        // ✅ Build path — e.g. "reports/2026/q1"
        $path = $parent
            ? rtrim($parent->path, '/') . '/' . $slug
            : $slug;

        // ✅ Depth
        $depth = $parent ? $parent->depth + 1 : 0;

        $folder = Folder::create([
            'user_id'     => auth()->id(),       // ✅ correct column (not uploaded_by)
            'parent_id'   => $parentId,
            'name'        => $name,
            'slug'        => $slug,              // ✅ required NOT NULL UNIQUE
            'path'        => $path,              // ✅ required NOT NULL
            'depth'       => $depth,
            'visibility'  => $request->visibility  ?? 'private',
            'description' => $request->description ?? null,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'folder'  => $folder,
            ]);
        }

        return redirect()
            ->route('filehosting.files.upload', ['tab' => 'folders'])
            ->with('success', "Folder \"{$name}\" created successfully!");
    }

    /**
     * PATCH /filehosting/folders/{folder}/rename
     * Called by renameFolder() in the blade JS
     */
    public function rename(Request $request, Folder $folder)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'visibility' => 'nullable|in:public,private,restricted',
        ]);

        $name     = trim($request->name);
        $parent   = $folder->parent;
        $baseSlug = Str::slug($name);
        $slug     = $this->uniqueSlug($baseSlug, $folder->parent_id, $folder->id);

        $path = $parent
            ? rtrim($parent->path, '/') . '/' . $slug
            : $slug;

        $folder->update([
            'name'       => $name,
            'slug'       => $slug,
            'path'       => $path,
            'visibility' => $request->visibility ?? $folder->visibility,
        ]);

        // Rebuild descendant paths if path changed
        $this->rebuildDescendantPaths($folder);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'folder' => $folder->fresh()]);
        }

        return redirect()
            ->route('filehosting.files.upload', ['tab' => 'folders'])
            ->with('success', "Folder renamed to \"{$name}\".");
    }

    /**
     * PATCH /filehosting/folders/{folder}/move
     * Called by moveFolder() in the blade JS
     */
    public function move(Request $request, Folder $folder)
    {
        $request->validate([
            'parent_id' => 'nullable|integer|exists:folders,id',
        ]);

        $newParentId = $request->parent_id;

        // Prevent moving folder into itself or its descendants
        if ($newParentId) {
            if ($newParentId == $folder->id) {
                return response()->json(['success' => false, 'message' => 'Cannot move folder into itself.'], 422);
            }

            $descendantIds = $this->getDescendantIds($folder);
            if (in_array($newParentId, $descendantIds)) {
                return response()->json(['success' => false, 'message' => 'Cannot move folder into its own descendant.'], 422);
            }
        }

        $newParent = $newParentId ? Folder::find($newParentId) : null;

        // Update slug if needed (ensure unique within new parent scope)
        $slug = $this->uniqueSlug($folder->slug, $newParentId, $folder->id);

        // Build new path
        $path = $newParent
            ? rtrim($newParent->path, '/') . '/' . $slug
            : $slug;

        $depth = $newParent ? $newParent->depth + 1 : 0;

        $folder->update([
            'parent_id' => $newParentId,
            'slug'      => $slug,
            'path'      => $path,
            'depth'     => $depth,
        ]);

        // Rebuild paths for all descendants
        $this->rebuildDescendantPaths($folder);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'folder' => $folder->fresh()]);
        }

        return redirect()
            ->route('filehosting.files.upload', ['tab' => 'folders'])
            ->with('success', "Folder moved successfully.");
    }

    /**
     * DELETE /filehosting/folders/{folder}
     * Called by deleteFolder() in the blade JS
     */
    public function destroy(Request $request, Folder $folder)
    {
        // Get all descendant folder IDs recursively
        $descendantIds = $this->getDescendantIds($folder->id);
        $allIds = array_merge([$folder->id], $descendantIds);

        // Detach all files from these folders to root
        DB::table('files')
            ->whereIn('folder_id', $allIds)
            ->update(['folder_id' => null]);

        $name = $folder->name;
        
        // Delete all descendants first, then the folder itself
        foreach ($descendantIds as $descendantId) {
            $descendant = Folder::find($descendantId);
            if ($descendant) {
                $descendant->delete();
            }
        }
        
        $folder->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->route('filehosting.files.upload', ['tab' => 'folders'])
            ->with('success', "Folder \"{$name}\" deleted.");
    }

    protected function getDescendantIds(int $folderId): array
    {
        $ids = [];
        $children = DB::table('folders')->where('parent_id', $folderId)->pluck('id');
        foreach ($children as $childId) {
            $ids[] = $childId;
            $ids = array_merge($ids, $this->getDescendantIds($childId));
        }
        return $ids;
    }

    // ─────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────

    /**
     * Generate a unique slug within the same parent scope.
     */
    protected function uniqueSlug(string $base, ?int $parentId, ?int $excludeId = null): string
    {
        $slug  = $base;
        $count = 1;

        while (true) {
            $query = Folder::where('slug', $slug);

            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            if (!$query->exists()) {
                break;
            }

            $slug = $base . '-' . $count++;
        }

        return $slug;
    }

    /**
     * Recursively rebuild path for all children after a rename/move.
     */
    protected function rebuildDescendantPaths(Folder $folder): void
    {
        foreach (Folder::where('parent_id', $folder->id)->get() as $child) {
            $child->path = rtrim($folder->path, '/') . '/' . $child->slug;
            $child->depth = $folder->depth + 1;
            $child->save();
            $this->rebuildDescendantPaths($child);
        }
    }

    /**
     * Get all descendant folder IDs recursively.
     */
    protected function getDescendantIds(Folder $folder): array
    {
        $ids = [];
        foreach (Folder::where('parent_id', $folder->id)->get() as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getDescendantIds($child));
        }
        return $ids;
    }
}