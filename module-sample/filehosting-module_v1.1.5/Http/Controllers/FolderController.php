<?php

// modules\FileHosting\Http\Controllers\FolderController.php
namespace Modules\FileHosting\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\FileHosting\Models\Folder;
use Modules\FileHosting\Models\File;
use Modules\FileHosting\Services\FolderService;
use Modules\FileHosting\Support\PermissionHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FolderController extends Controller
{
    public function __construct(protected FolderService $folderService) {}

    // ---------------------------------------------------------------
    // Browse a folder
    // ---------------------------------------------------------------

    public function show(Request $request, int $id)
    {
        $folder = Folder::with(['children', 'files.thumbnails', 'owner'])->findOrFail($id);
        $user   = $request->user();

        abort_unless(
            PermissionHelper::canOnFolder($user, $folder, 'view_assigned')
            || PermissionHelper::canOnFolder($user, $folder, 'view_all')
            || $folder->visibility === 'public',
            403, 'You do not have permission to view this folder.'
        );

        $breadcrumb = $folder->getBreadcrumb();

        $files = File::where('folder_id', $folder->id)
            ->notExpired()
            ->with('thumbnails')
            ->latest()
            ->paginate(50);

        return view('filehosting::index', compact('folder', 'breadcrumb', 'files'));
    }

    // ---------------------------------------------------------------
    // Create
    // ---------------------------------------------------------------

    public function store(Request $request): JsonResponse
    {
        \Log::info('Folder store called', $request->all());
        
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'parent_id'   => 'nullable|exists:folders,id',
            'description' => 'nullable|string|max:1000',
            'visibility'  => 'nullable|in:private,public,restricted',
        ]);

        // Allow all authenticated users to create folders for now
        $folder = $this->folderService->create($data, $request->user()->id);
        
        \Log::info('Folder created', ['folder_id' => $folder->id, 'name' => $folder->name]);

        return response()->json([
            'message' => 'Folder created successfully.',
            'folder'  => $folder,
        ], 201);
    }

    // ---------------------------------------------------------------
    // Rename
    // ---------------------------------------------------------------

    public function rename(Request $request, int $id): JsonResponse
    {
        $folder = Folder::findOrFail($id);
        $user   = $request->user();

        abort_unless(PermissionHelper::canOnFolder($user, $folder, 'rename'), 403);

        $request->validate(['name' => 'required|string|max:255']);
        $folder = $this->folderService->rename($folder, $request->name);

        return response()->json(['message' => 'Folder renamed.', 'folder' => $folder]);
    }

    // ---------------------------------------------------------------
    // Move
    // ---------------------------------------------------------------

    public function move(Request $request, int $id): JsonResponse
    {
        $folder = Folder::findOrFail($id);
        $user   = $request->user();

        abort_unless(PermissionHelper::canOnFolder($user, $folder, 'move'), 403);

        $request->validate(['parent_id' => 'nullable|exists:folders,id']);
        $folder = $this->folderService->move($folder, $request->parent_id);

        return response()->json(['message' => 'Folder moved.', 'folder' => $folder]);
    }

    // ---------------------------------------------------------------
    // Delete
    // ---------------------------------------------------------------

    public function destroy(Request $request, int $id): JsonResponse
    {
        $folder = Folder::findOrFail($id);
        $user   = $request->user();

        $canDelete = PermissionHelper::canOnFolder($user, $folder, 'delete')
            || ((int)$folder->user_id === (int)$user->id
                && PermissionHelper::canOnFolder($user, $folder, 'delete_own'));

        abort_unless($canDelete, 403, 'You cannot delete this folder.');

        $this->folderService->delete($folder);

        return response()->json(['message' => 'Folder deleted.']);
    }

    // ---------------------------------------------------------------
    // Tree (for sidebar / picker)
    // ---------------------------------------------------------------

    public function tree(Request $request): JsonResponse
    {
        $tree = $this->folderService->treeForUser($request->user()->id);
        return response()->json($tree);
    }

    // ---------------------------------------------------------------
    // Permissions management
    // ---------------------------------------------------------------

    public function permissions(Request $request, int $id): JsonResponse
    {
        $folder = Folder::with('permissions.grantee')->findOrFail($id);
        abort_unless(PermissionHelper::canOnFolder($request->user(), $folder, 'manage_permissions'), 403);

        return response()->json($folder->permissions);
    }

    public function grantPermission(Request $request, int $id): JsonResponse
    {
        $folder = Folder::findOrFail($id);
        abort_unless(PermissionHelper::canOnFolder($request->user(), $folder, 'manage_permissions'), 403);

        $data = $request->validate([
            'grantee_type' => 'required|string',
            'grantee_id'   => 'required|integer',
            'permissions'  => 'required|array',
            'expires_at'   => 'nullable|date',
        ]);

        $permission = $folder->permissions()->updateOrCreate(
            [
                'grantee_type' => $data['grantee_type'],
                'grantee_id'   => $data['grantee_id'],
            ],
            [
                'permissions' => $data['permissions'],
                'granted_by'  => $request->user()->id,
                'expires_at'  => $data['expires_at'] ?? null,
            ]
        );

        return response()->json(['message' => 'Permission granted.', 'permission' => $permission], 201);
    }
}
