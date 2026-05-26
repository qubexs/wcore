<?php
// modules/FileHosting/src/Services/FileHostingService.php

namespace App\Modules\FileHosting\Services;

use App\Modules\FileHosting\Models\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;

class FileHostingService
{
    /**
     * Upload a file and optionally create a thumbnail for images
     *
     * @param UploadedFile $file
     * @param int $userId
     * @return File
     */
    public function uploadFile(UploadedFile $file, int $userId): File
    {
        // ✅ Ensure folders exist
        $fileFolder  = 'filehosting/files';
        $thumbFolder = 'filehosting/thumbnails';

        if (!Storage::disk('public')->exists($fileFolder)) {
            Storage::disk('public')->makeDirectory($fileFolder, 0755, true);
        }

        if (!Storage::disk('public')->exists($thumbFolder)) {
            Storage::disk('public')->makeDirectory($thumbFolder, 0755, true);
        }

        // Store the main file
        $path = $file->store($fileFolder, 'public');

        $thumbnail = null;

        // ✅ Generate thumbnail if the file is an image
        if (str_starts_with($file->getMimeType(), 'image/')) {
            $thumbnailPath = $thumbFolder . '/' . $file->hashName();

            $img = Image::make($file->getRealPath())
                ->resize(200, 200, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

            Storage::disk('public')->put($thumbnailPath, (string) $img->encode());
            $thumbnail = $thumbnailPath;
        }

        // Save file info in database
        return File::create([
            'name'        => $file->getClientOriginalName(),
            'stored_name' => basename($path), // actual stored filename
            'path'        => $path,           // path relative to public disk
            'thumbnail'   => $thumbnail,      // null if not image
            'uploaded_by' => $userId,
        ]);
    }

    /**
     * Get all uploaded files, including user relation
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function allFiles()
    {
        return File::with('user')->latest()->get();
    }

    /**
     * Delete a file and its thumbnail
     *
     * @param File $file
     * @return void
     */
    public function deleteFile(File $file): void
    {
        // Delete main file if exists
        if (Storage::disk('public')->exists($file->path)) {
            Storage::disk('public')->delete($file->path);
        }

        // Delete thumbnail if exists
        if ($file->thumbnail && Storage::disk('public')->exists($file->thumbnail)) {
            Storage::disk('public')->delete($file->thumbnail);
        }

        // Delete DB record
        $file->delete();
    }
}
