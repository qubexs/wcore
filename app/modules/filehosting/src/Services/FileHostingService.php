<?php

namespace App\Modules\FileHosting\Services;

use App\Modules\FileHosting\Models\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;

class FileHostingService
{
    public function uploadFile(UploadedFile $file, int $userId): File
    {
        $path = $file->store('files');

        $thumbnail = null;
        if (str_starts_with($file->getMimeType(), 'image/')) {
            $thumbnailPath = 'thumbnails/' . $file->hashName();
            $img = Image::make($file)->resize(200, 200, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            Storage::put($thumbnailPath, (string)$img->encode());
            $thumbnail = $thumbnailPath;
        }

        return File::create([
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'thumbnail' => $thumbnail,
            'uploaded_by' => $userId,
        ]);
    }

    public function allFiles()
    {
        return File::with('user')->get();
    }

    public function deleteFile(File $file)
    {
        Storage::delete($file->path);
        if ($file->thumbnail) {
            Storage::delete($file->thumbnail);
        }
        $file->delete();
    }
}
