<?php
// src/Services/FileHostingService.php

namespace App\Modules\FileHosting\Services;

use App\Modules\FileHosting\Models\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;

class FileHostingService
{
    /**
     * Upload file + optional thumbnail
     */
    public function uploadFile(UploadedFile $file, int $userId): File
    {
        // ✅ Always specify disk (important for modules)
        $path = $file->store('filehosting/files', 'public');

        $thumbnail = null;

        // ✅ Safe MIME check
        if (str_starts_with($file->getMimeType(), 'image/')) {

            $thumbnailPath = 'filehosting/thumbnails/' . $file->hashName();

            $img = Image::make($file->getRealPath())
                ->resize(200, 200, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

            Storage::disk('public')->put(
                $thumbnailPath,
                (string) $img->encode()
            );

            $thumbnail = $thumbnailPath;
        }

        return File::create([
            'name'        => $file->getClientOriginalName(),
            'path'        => $path,
            'thumbnail'   => $thumbnail,
            'uploaded_by' => $userId,
        ]);
    }

    /**
     * Get all files
     */
    public function allFiles()
    {
        return File::with('user')->latest()->get();
    }

    /**
     * Delete file + thumbnail
     */
    public function deleteFile(File $file): void
    {
        Storage::disk('public')->delete($file->path);

        if ($file->thumbnail) {
            Storage::disk('public')->delete($file->thumbnail);
        }

        $file->delete();
    }
}
