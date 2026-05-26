<?php

namespace App\Modules\FileHosting\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\FileHosting\Models\File;
use App\Modules\FileHosting\Models\FileVersion;
use App\Modules\FileHosting\Services\FileService;
use App\Modules\FileHosting\Services\VersionService;
use App\Modules\FileHosting\Support\PermissionHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FileController extends Controller
{
    public function __construct(
        protected FileService    $fileService,
        protected VersionService $versionService
    ) {}

    // ---------------------------------------------------------------
    // Upload
    // ---------------------------------------------------------------

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file'        => 'required|file|max:102400', // 100 MB default
            'folder_id'   => 'nullable|exists:folders,id',
            'description' => 'nullable|string|max:1000',
            'visibility'  => 'nullable|in:private,public,restricted',
            'expires_at'  => 'nullable|date|after:now',
        ]);

        $file = $this->fileService->upload(
            $request->file('file'),
            $request->user()->id,
            $request->folder_id,
            $request->only('description', 'visibility', 'expires_at')
        );

        return response()->json([
            'message' => 'File uploaded successfully.',
            'file'    => $file->load('thumbnails'),
        ], 201);
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
        abort_unless(PermissionHelper::canOnFile($request->user(), $file, 'download'), 403);

        return $this->fileService->download($file);
    }

    // ---------------------------------------------------------------
    // Update metadata
    // ---------------------------------------------------------------

    public function update(Request $request, int $id): JsonResponse
    {
        $file = File::findOrFail($id);
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

        $canDelete = PermissionHelper::canOnFile($request->user(), $file, 'delete')
            || ((int)$file->uploaded_by === (int)$request->user()->id
                && PermissionHelper::canOnFile($request->user(), $file, 'delete_own'));

        abort_unless($canDelete, 403, 'You cannot delete this file.');

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
}