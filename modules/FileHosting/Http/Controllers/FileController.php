<?php
// modules\FileHosting\Http\Controllers\FileController.php
namespace App\Modules\FileHosting\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\FileHosting\Models\File;
use App\Modules\FileHosting\Models\FileVersion;
use App\Modules\FileHosting\Models\Folder;
use App\Modules\FileHosting\Services\FileService;
use App\Modules\FileHosting\Services\VersionService;
use App\Modules\FileHosting\Support\PermissionHelper;
use App\Modules\FileHosting\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function __construct(
        protected FileService    $fileService,
        protected VersionService $versionService,
        protected SettingService $settingService
    ) {}

    // ---------------------------------------------------------------
    // Upload View
    // ---------------------------------------------------------------
    public function uploadView(Request $request)
    {
        abort_unless($request->user()->can('filehosting.file.upload'), 403);
        
        $files = File::with(['owner', 'folder'])
            ->orderByDesc('created_at')
            ->get();
            
        $folders = Folder::with([
                'files',
                'children',
                'children.files',
                'children.children',
                'children.children.files',
                'children.children.children',
                'children.children.children.files',
                'children.children.children.children',
                'children.children.children.children.files',
                'children.children.children.children.children',
                'children.children.children.children.children.files',
            ])
            ->orderBy('path')
            ->get();
            
        $parentMap = [];
        foreach ($folders as $folder) {
            if ($folder->parent_id && !isset($parentMap[$folder->parent_id])) {
                $parentMap[$folder->parent_id] = $folder->parent_id;
            }
        }
        
        if (!empty($parentMap)) {
            $parents = Folder::whereIn('id', array_keys($parentMap))
                ->pluck('name', 'id');
                
            foreach ($folders as $folder) {
                $folder->setAttribute('parent_name', $parents[$folder->parent_id] ?? null);
            }
        }

        $folderCounts = \DB::table('files')
            ->select('folder_id', \DB::raw('count(*) as count'))
            ->whereNotNull('folder_id')
            ->groupBy('folder_id')
            ->pluck('count', 'folder_id')
            ->toArray();

        $folderIds = Folder::pluck('id')->toArray();
        $idToParent = Folder::pluck('parent_id', 'id')->toArray();

        $totalCounts = [];
        foreach ($folderIds as $id) {
            $totalCounts[$id] = $folderCounts[$id] ?? 0;
        }

        foreach (array_reverse($folderIds) as $id) {
            $parentId = $idToParent[$id] ?? null;
            if ($parentId && isset($totalCounts[$id])) {
                $totalCounts[$parentId] = ($totalCounts[$parentId] ?? 0) + $totalCounts[$id];
            }
        }

        $folders->each(function ($folder) use ($totalCounts) {
            $folder->setAttribute('files_count', $totalCounts[$folder->id] ?? 0);
        });
            
        $maxUploadSize = $this->settingService->maxUploadBytes();
            
        return view('filehosting::upload', compact('files', 'folders', 'maxUploadSize'));
    }

    // ---------------------------------------------------------------
    // All Files View
    // ---------------------------------------------------------------
    public function all(Request $request)
    {
        abort_unless($request->user()->can('filehosting.file.view'), 403);
        
        $files = File::with(['owner', 'folder'])
            ->orderByDesc('created_at')
            ->get();
            
        $folders = Folder::with([
                'owner',
                'files',
                'children',
                'children.owner',
                'children.files',
                'children.children',
                'children.children.owner',
                'children.children.files',
                'children.children.children',
                'children.children.children.owner',
                'children.children.children.files',
                'children.children.children.children',
                'children.children.children.children.owner',
                'children.children.children.children.files',
                'children.children.children.children.children',
                'children.children.children.children.children.owner',
                'children.children.children.children.children.files',
                'children.children.children.children.children.children',
                'children.children.children.children.children.children.owner',
                'children.children.children.children.children.children.files',
                'children.children.children.children.children.children.children',
                'children.children.children.children.children.children.children.owner',
                'children.children.children.children.children.children.children.files',
                'children.children.children.children.children.children.children.children',
                'children.children.children.children.children.children.children.children.owner',
                'children.children.children.children.children.children.children.children.files',
            ])
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        $folderCounts = \DB::table('files')
            ->select('folder_id', \DB::raw('count(*) as count'))
            ->whereNotNull('folder_id')
            ->groupBy('folder_id')
            ->pluck('count', 'folder_id')
            ->toArray();

        $folderIds = Folder::orderBy('path')->pluck('id')->toArray();
        $idToParent = Folder::pluck('parent_id', 'id')->toArray();

        $totalCounts = [];
        foreach ($folderIds as $id) {
            $totalCounts[$id] = $folderCounts[$id] ?? 0;
        }

        foreach (array_reverse($folderIds) as $id) {
            $parentId = $idToParent[$id] ?? null;
            if ($parentId && isset($totalCounts[$id])) {
                $totalCounts[$parentId] = ($totalCounts[$parentId] ?? 0) + $totalCounts[$id];
            }
        }

        $allFolders = Folder::orderBy('path')->get();
        $allFolders->each(function ($folder) use ($totalCounts) {
            $folder->setAttribute('files_count', $totalCounts[$folder->id] ?? 0);
        });

        $folders->each(function ($folder) use ($totalCounts) {
            $folder->setAttribute('files_count', $totalCounts[$folder->id] ?? 0);
            $this->setFilesCountRecursive($folder, $totalCounts);
        });
            
        return view('filehosting::all', compact('files', 'folders'));
    }

    // ---------------------------------------------------------------
    // Upload
    // ---------------------------------------------------------------

    public function store(Request $request): JsonResponse
    {
        try {
            $maxUploadBytes = $this->settingService->maxUploadBytes();
            $maxUploadKb = (int) ($maxUploadBytes / 1024);
            
            $uploadedFile = $request->file('file');
            $error = $uploadedFile ? $uploadedFile->getError() : 'no file';
            $errorMsg = $uploadedFile ? $uploadedFile->getErrorMessage() : 'n/a';
            \Illuminate\Support\Facades\Log::info('Upload attempt - Max bytes: ' . $maxUploadBytes . ', Max KB: ' . $maxUploadKb . ', File: ' . ($uploadedFile ? $uploadedFile->getClientOriginalName() . ' (' . $uploadedFile->getSize() . ' bytes)' : 'NULL') . ', Error: ' . $error . ', Msg: ' . $errorMsg);
            
            if (!$uploadedFile || $uploadedFile->getError() !== UPLOAD_ERR_OK) {
                return response()->json([
                    'success' => false,
                    'message' => 'Upload error: code ' . ($uploadedFile ? $uploadedFile->getError() : 'no file') . ' - ' . ($uploadedFile ? $uploadedFile->getErrorMessage() : 'file not received'),
                ], 400);
            }
            
            $request->validate([
                'file'        => "required|file|max:{$maxUploadKb}",
            'folder_id'   => 'nullable|exists:folders,id',
            'description' => 'nullable|string|max:1000',
            'visibility'  => 'nullable|in:private,public,restricted',
            'expires_at'  => 'nullable|date|after:now',
            ]);

            \Illuminate\Support\Facades\Log::info('Validation passed, attempting upload');

            $file = $this->fileService->upload(
                $request->file('file'),
                $request->user()->id,
                $request->folder_id,
                $request->only('description', 'visibility', 'expires_at')
            );

            \Illuminate\Support\Facades\Log::info('Upload completed: ' . $file->id);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully.',
                'file'    => $file->load('thumbnails'),
            ], 201);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Upload error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ---------------------------------------------------------------
    // Show / Preview
    // ---------------------------------------------------------------

    public function show(Request $request, int $id): JsonResponse
    {
        $file = File::with(['folder', 'owner', 'thumbnails', 'versions'])->findOrFail($id);

        abort_unless(PermissionHelper::canOnFile($request->user(), $file, 'view'), 403);

        return response()->json($file);
    }

    // ---------------------------------------------------------------
    // Download
    // ---------------------------------------------------------------

    public function download(Request $request, int $id)
    {
        $file = File::findOrFail($id);
        
        // Allow download for any authenticated user with role
        if ($request->user()->hasAnyRole(['SuperAdmin', 'Admin'])) {
            return $this->fileService->download($file);
        }
        
        // Check if user has download permission
        abort_unless(
            PermissionHelper::canOnFile($request->user(), $file, 'download'),
            403
        );
        
        return $this->fileService->download($file);
    }

    // ---------------------------------------------------------------
    // Update metadata
    // ---------------------------------------------------------------

    public function update(Request $request, File $file): JsonResponse
    {
        abort_unless(PermissionHelper::canOnFile($request->user(), $file, 'edit'), 403);

        $data = $request->validate([
            'description' => 'nullable|string|max:1000',
            'visibility'  => 'nullable|in:private,public,restricted',
            'expires_at'  => 'nullable|date',
        ]);

        $file = $this->fileService->updateMeta($file, $data);

        return response()->json(['message' => 'File updated.', 'file' => $file]);
    }

    // ---------------------------------------------------------------
    // Replace (new version)
    // ---------------------------------------------------------------

    public function replace(Request $request, int $id): JsonResponse
    {
        $file = File::findOrFail($id);
        abort_unless(PermissionHelper::canOnFile($request->user(), $file, 'replace'), 403);

        $request->validate([
            'file'  => 'required|file|max:102400',
            'notes' => 'nullable|string|max:500',
        ]);

        $file = $this->fileService->replace(
            $file,
            $request->file('file'),
            $request->user()->id,
            $request->notes ?? ''
        );

        return response()->json(['message' => 'File replaced (new version created).', 'file' => $file]);
    }

    // ---------------------------------------------------------------
    // Move
    // ---------------------------------------------------------------

    public function move(Request $request, int $id): JsonResponse
    {
        $file = File::findOrFail($id);
        abort_unless(PermissionHelper::canOnFile($request->user(), $file, 'move'), 403);

        $request->validate(['folder_id' => 'nullable|exists:folders,id']);
        $file = $this->fileService->move($file, $request->folder_id);

        return response()->json(['message' => 'File moved.', 'file' => $file]);
    }

    // ---------------------------------------------------------------
    // Delete
    // ---------------------------------------------------------------

    public function destroy(Request $request, int $id): JsonResponse
    {
        $file = File::findOrFail($id);
        
        // Allow deletion if user uploaded the file or is admin/superadmin
        $canDelete = $request->user()->id === $file->uploaded_by 
            || $request->user()->hasAnyRole(['admin', 'super_admin', 'SuperAdmin', 'Admin']);

        if (!$canDelete) {
            return response()->json(['message' => 'You cannot delete this file.'], 403);
        }

        $this->fileService->delete($file);

        return response()->json(['message' => 'File deleted.']);
    }

    // ---------------------------------------------------------------
    // Search
    // ---------------------------------------------------------------

    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:2', 'folder_id' => 'nullable|exists:folders,id']);
        $results = $this->fileService->search($request->q, $request->folder_id);
        return response()->json($results);
    }

    // ---------------------------------------------------------------
    // Versions
    // ---------------------------------------------------------------

    public function versions(Request $request, int $id): JsonResponse
    {
        $file = File::findOrFail($id);
        abort_unless(PermissionHelper::canOnFile($request->user(), $file, 'view'), 403);

        return response()->json($this->versionService->listVersions($file));
    }

    public function restoreVersion(Request $request, int $fileId, int $versionId): JsonResponse
    {
        $file    = File::findOrFail($fileId);
        $version = FileVersion::where('file_id', $fileId)->findOrFail($versionId);

        abort_unless(PermissionHelper::canOnFile($request->user(), $file, 'version_manage'), 403);

        $file = $this->versionService->restore($file, $version, $request->user()->id);

        return response()->json(['message' => "Restored to version {$version->version_number}.", 'file' => $file]);
    }

    public function deleteVersion(Request $request, int $fileId, int $versionId): JsonResponse
    {
        $file    = File::findOrFail($fileId);
        $version = FileVersion::where('file_id', $fileId)->findOrFail($versionId);

        abort_unless(PermissionHelper::canOnFile($request->user(), $file, 'version_manage'), 403);

        $this->versionService->deleteVersion($version);

        return response()->json(['message' => 'Version deleted.']);
    }

    // ---------------------------------------------------------------
    // Report File
    // ---------------------------------------------------------------
    public function report(Request $request, int $id): JsonResponse
    {
        $file = File::findOrFail($id);
        
        $request->validate([
            'reason' => 'required|string|in:broken_404,forbidden_403,new_version,broken_tnc,other'
        ]);

        DB::table('file_reports')->insert([
            'file_id' => $file->id,
            'user_id' => $request->user()->id,
            'reason' => $request->reason,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Report submitted. Thank you!']);
    }

    // ---------------------------------------------------------------
    // Favorite File
    // ---------------------------------------------------------------
    public function favorite(Request $request, int $id): JsonResponse
    {
        $file = File::findOrFail($id);
        
        // Toggle favorite - check if already favorited
        $existingFavorite = DB::table('file_favorites')
            ->where('file_id', $file->id)
            ->where('user_id', $request->user()->id)
            ->first();
        
        if ($existingFavorite) {
            // Remove favorite
            DB::table('file_favorites')
                ->where('id', $existingFavorite->id)
                ->delete();
            return response()->json(['message' => 'Removed from favorites.', 'is_favorite' => false]);
        } else {
            // Add favorite
            DB::table('file_favorites')->insert([
                'file_id' => $file->id,
                'user_id' => $request->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return response()->json(['message' => 'Added to favorites!', 'is_favorite' => true]);
        }
    }

    // ---------------------------------------------------------------
    // Share File - Create share link
    // ---------------------------------------------------------------
    public function share(Request $request, int $id): JsonResponse
    {
        $file = File::findOrFail($id);
        
        $request->validate([
            'share_type' => 'nullable|string|in:link,email',
            'tag_names' => 'nullable|string|max:255'
        ]);

        $shareToken = Str::random(32);
        
        // Save share record
        $shareId = DB::table('file_shares')->insertGetId([
            'file_id' => $file->id,
            'user_id' => $request->user()->id,
            'share_token' => $shareToken,
            'tag_names' => $request->tag_names ?? null,
            'share_type' => $request->share_type ?? 'link',
            'expires_at' => now()->addDays(30),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $shareUrl = url("/filehosting/shared/{$shareToken}");

        return response()->json([
            'message' => 'Share link created!',
            'share_url' => $shareUrl,
            'token' => $shareToken
        ]);
    }

    // ---------------------------------------------------------------
    // View Shared File
    // ---------------------------------------------------------------
    public function viewShared(string $token)
    {
        $share = DB::table('file_shares')
            ->where('share_token', $token)
            ->first();

        if (!$share) {
            abort(404, 'Share link not found.');
        }

        if ($share->expires_at && now()->isAfter($share->expires_at)) {
            abort(410, 'This share link has expired.');
        }

        $file = File::with(['folder', 'owner'])->findOrFail($share->file_id);
        $fileUrl = url('/uploads/' . str_replace('uploads/', '', $file->path));

        return view('filehosting::shared', compact('file', 'fileUrl', 'share'));
    }

    protected function setFilesCountRecursive($folder, array &$totalCounts): void
    {
        $children = $folder->getRelation('children') ?? collect();
        foreach ($children as $child) {
            $child->setAttribute('files_count', $totalCounts[$child->id] ?? 0);
            $this->setFilesCountRecursive($child, $totalCounts);
        }
    }
}