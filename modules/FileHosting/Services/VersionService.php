<?php

namespace App\Modules\FileHosting\Services;

use App\Modules\FileHosting\Models\File;
use App\Modules\FileHosting\Models\FileVersion;
use App\Modules\FileHosting\Models\FileStat;
use App\Modules\FileHosting\Support\PathHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VersionService
{
    /**
     * Create the initial version record when a file is first uploaded.
     */
    public function createInitial(File $file, int $userId): FileVersion
    {
        return FileVersion::create([
            'file_id'        => $file->id,
            'user_id'        => $userId,
            'stored_name'    => $file->stored_name,
            'file_path'      => $file->file_path,
            'size'           => $file->size,
            'checksum'       => $file->checksum,
            'version_number' => 1,
            'change_notes'   => 'Initial upload',
        ]);
    }

    /**
     * Archive the current file state as a version before replacement.
     */
    public function archive(File $file, int $userId, string $notes = ''): FileVersion
    {
        $nextNumber = ($file->versions()->max('version_number') ?? 0) + 1;

        // Copy the current physical file to a versioned location
        $archivePath = PathHelper::buildFilePath($file->extension);
        Storage::disk('public')->copy($file->path, $archivePath);

        return FileVersion::create([
            'file_id'        => $file->id,
            'user_id'        => $userId,
            'stored_name'    => basename($archivePath),
            'file_path'      => $archivePath,
            'size'           => $file->size,
            'checksum'       => $file->checksum,
            'version_number' => $nextNumber,
            'change_notes'   => $notes ?: "Version {$nextNumber}",
        ]);
    }

    /**
     * Restore a specific version as the current file content.
     */
    public function restore(File $file, FileVersion $version, int $userId): File
    {
        return DB::transaction(function () use ($file, $version, $userId) {
            // Archive the current state first
            $this->archive($file, $userId, 'Auto-archived before version restore');

            $file->update([
                'stored_name' => $version->stored_name,
                'file_path'   => $version->file_path,
                'size'        => $version->size,
                'checksum'    => $version->checksum,
            ]);

            FileStat::record(FileStat::ACTION_RESTORE_VERSION, $file->id, $file->folder_id, [
                'restored_version' => $version->version_number,
            ]);

            return $file->fresh();
        });
    }

    /**
     * Delete a specific version (does not affect current file).
     */
    public function deleteVersion(FileVersion $version): void
    {
        Storage::disk('public')->delete($version->file_path);
        $version->delete();
    }

    /**
     * List all versions for a file with uploader info.
     */
    public function listVersions(File $file): \Illuminate\Database\Eloquent\Collection
    {
        return $file->versions()->with('uploader')->get();
    }
}