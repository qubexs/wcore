<?php

namespace App\Modules\FileHosting\Services;

use App\Modules\FileHosting\Models\File;
use App\Modules\FileHosting\Models\Folder;
use App\Modules\FileHosting\Models\FileStat;
use App\Modules\FileHosting\Support\PathHelper;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FileService
{
    public function __construct(
        protected ThumbnailService $thumbnailService,
        protected VersionService   $versionService
    ) {}

    // ---------------------------------------------------------------
    // Upload
    // ---------------------------------------------------------------

    /**
     * Upload a new file, generate thumbnails if applicable.
     */
    public function upload(
        UploadedFile $uploadedFile,
        int $userId,
        ?int $folderId = null,
        array $meta    = []
    ): File {
        $maxFiles = config('filehosting.limits.max_files_per_folder', 1000);

        if ($folderId) {
            $count = File::where('folder_id', $folderId)->count();
            if ($count >= $maxFiles) {
                throw new \RuntimeException("Folder has reached the maximum of {$maxFiles} files.");
            }
        }

        $extension = PathHelper::extension($uploadedFile->getClientOriginalName());
        $storedPath = PathHelper::buildFilePath($extension);

        return DB::transaction(function () use ($uploadedFile, $userId, $folderId, $meta, $extension, $storedPath) {
            Storage::disk('public')->put($storedPath, file_get_contents($uploadedFile->getRealPath()));

            $file = File::create([
                'folder_id'     => $folderId,
                'uploaded_by'       => $userId,
                'name'          => $uploadedFile->getClientOriginalName(),
                'original_name' => $uploadedFile->getClientOriginalName(),
                'stored_name'   => basename($storedPath),
                'path'     => $storedPath,
                'mime_type'     => $uploadedFile->getMimeType(),
                'size'          => $uploadedFile->getSize(),
                'extension'     => $extension,
                'checksum'      => hash_file('sha256', $uploadedFile->getRealPath()),
                'description'   => $meta['description'] ?? null,
                'visibility'    => $meta['visibility']  ?? 'private',
                'expires_at'    => $meta['expires_at']  ?? null,
            ]);

            // Generate thumbnails for images
            if ($file->isImage()) {
                $this->thumbnailService->generateAll($file, $uploadedFile->getRealPath());
            }

            // Record first version
            $this->versionService->createInitial($file, $userId);

            FileStat::record(FileStat::ACTION_UPLOAD, $file->id, $folderId);

            return $file;
        });
    }

    // ---------------------------------------------------------------
    // Replace (creates a new version)
    // ---------------------------------------------------------------

    public function replace(File $file, UploadedFile $newFile, int $userId, string $notes = ''): File
    {
        return DB::transaction(function () use ($file, $newFile, $userId, $notes) {
            // Archive old version before replacement
            $this->versionService->archive($file, $userId, $notes);

            $extension  = PathHelper::extension($newFile->getClientOriginalName());
            $storedPath = PathHelper::buildFilePath($extension);

            Storage::disk('public')->put($storedPath, file_get_contents($newFile->getRealPath()));

            $file->update([
                'original_name' => $newFile->getClientOriginalName(),
                'stored_name'   => basename($storedPath),
                'path'     => $storedPath,
                'mime_type'     => $newFile->getMimeType(),
                'size'          => $newFile->getSize(),
                'extension'     => $extension,
                'checksum'      => hash_file('sha256', $newFile->getRealPath()),
            ]);

            // Regenerate thumbnails
            if ($file->isImage()) {
                $file->thumbnails()->delete();
                $this->thumbnailService->generateAll($file, $newFile->getRealPath());
            }

            FileStat::record(FileStat::ACTION_REPLACE, $file->id, $file->folder_id, [
                'notes' => $notes,
            ]);

            return $file->fresh();
        });
    }

    // ---------------------------------------------------------------
    // Move
    // ---------------------------------------------------------------

    public function move(File $file, ?int $newFolderId): File
    {
        $old = $file->folder_id;
        $file->update(['folder_id' => $newFolderId]);

        FileStat::record(FileStat::ACTION_MOVE, $file->id, $newFolderId, [
            'from_folder_id' => $old,
            'to_folder_id'   => $newFolderId,
        ]);

        return $file->fresh();
    }

    // ---------------------------------------------------------------
    // Delete
    // ---------------------------------------------------------------

    public function delete(File $file): void
    {
        DB::transaction(function () use ($file) {
            FileStat::record(FileStat::ACTION_DELETE, $file->id, $file->folder_id, [
                'filename' => $file->original_name,
            ]);
            $file->delete();
        });
    }

    // ---------------------------------------------------------------
    // Download
    // ---------------------------------------------------------------

    public function download(File $file): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        if (!Storage::exists($file->file_path)) {
            abort(404, 'File not found on storage.');
        }

        $file->incrementDownloads();
        FileStat::record(FileStat::ACTION_DOWNLOAD, $file->id, $file->folder_id);

        return Storage::download($file->file_path, $file->original_name);
    }

    // ---------------------------------------------------------------
    // Update metadata
    // ---------------------------------------------------------------

    public function updateMeta(File $file, array $data): File
    {
        $file->update(array_filter([
            'description' => $data['description'] ?? $file->description,
            'visibility'  => $data['visibility']  ?? $file->visibility,
            'expires_at'  => $data['expires_at']  ?? $file->expires_at,
        ]));

        FileStat::record(FileStat::ACTION_RENAME, $file->id, $file->folder_id, [
            'changes' => $data,
        ]);

        return $file->fresh();
    }

    // ---------------------------------------------------------------
    // Search
    // ---------------------------------------------------------------

    public function search(string $query, ?int $folderId = null): \Illuminate\Database\Eloquent\Collection
    {
        return File::when($folderId, fn ($q) => $q->where('folder_id', $folderId))
            ->where(function ($q) use ($query) {
                $q->where('original_name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->notExpired()
            ->latest()
            ->limit(50)
            ->get();
    }
}
