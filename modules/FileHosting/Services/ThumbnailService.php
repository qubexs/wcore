<?php
// \modules\filehosting\Services\ThumbnailService.php

namespace App\Modules\FileHosting\Services;

use App\Modules\FileHosting\Models\File;
use App\Modules\FileHosting\Models\Thumbnail;
use App\Modules\FileHosting\Support\PathHelper;
use Illuminate\Support\Facades\Storage;

class ThumbnailService
{
    /**
     * Generate all three thumbnail sizes for an image file.
     */
    public function generateAll(File $file, string $sourcePath): void
    {
        foreach (Thumbnail::SIZE_DIMENSIONS as $sizeType => $dimensions) {
            $this->generate($file, $sourcePath, $sizeType, $dimensions['width'], $dimensions['height']);
        }
    }

    /**
     * Generate a single thumbnail size.
     */
    public function generate(File $file, string $sourcePath, string $sizeType, int $maxW, int $maxH): ?Thumbnail
    {
        try {
            $driver = $this->resolveDriver();

            if ($driver === 'imagick') {
                return $this->generateWithImagick($file, $sourcePath, $sizeType, $maxW, $maxH);
            }

            return $this->generateWithGd($file, $sourcePath, $sizeType, $maxW, $maxH);
        } catch (\Exception $e) {
            \Log::warning("Thumbnail generation failed for file {$file->id}: " . $e->getMessage());
            return null;
        }
    }

    // ---------------------------------------------------------------
    // GD implementation
    // ---------------------------------------------------------------

    private function generateWithGd(File $file, string $sourcePath, string $sizeType, int $maxW, int $maxH): Thumbnail
    {
        [$origW, $origH] = getimagesize($sourcePath);
        [$newW, $newH]   = $this->scaleDimensions($origW, $origH, $maxW, $maxH);

        $src  = $this->createGdResource($sourcePath, $file->mime_type);
        $dest = imagecreatetruecolor($newW, $newH);

        // Preserve transparency for PNG
        if ($file->mime_type === 'image/png') {
            imagealphablending($dest, false);
            imagesavealpha($dest, true);
            $transparent = imagecolorallocatealpha($dest, 255, 255, 255, 127);
            imagefilledrectangle($dest, 0, 0, $newW, $newH, $transparent);
        }

        imagecopyresampled($dest, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        $thumbPath = PathHelper::buildThumbnailPath($sizeType);
        $tmpFile   = tempnam(sys_get_temp_dir(), 'thumb_');

        imagejpeg($dest, $tmpFile, 85);
        imagedestroy($src);
        imagedestroy($dest);

        Storage::disk('public')->put($thumbPath, file_get_contents($tmpFile));
        unlink($tmpFile);

        return Thumbnail::updateOrCreate(
            ['file_id' => $file->id, 'size_type' => $sizeType],
            [
                'file_path' => $thumbPath,
                'width'     => $newW,
                'height'    => $newH,
                'file_size' => Storage::disk('public')->size($thumbPath),
            ]
        );
    }

    private function createGdResource(string $path, string $mime): \GdImage
    {
        return match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png'  => imagecreatefrompng($path),
            'image/gif'  => imagecreatefromgif($path),
            'image/webp' => imagecreatefromwebp($path),
            default      => throw new \RuntimeException("Unsupported image type: {$mime}"),
        };
    }

    // ---------------------------------------------------------------
    // Imagick implementation
    // ---------------------------------------------------------------

    private function generateWithImagick(File $file, string $sourcePath, string $sizeType, int $maxW, int $maxH): Thumbnail
    {
        $imagick = new \Imagick($sourcePath);
        $imagick->setImageCompressionQuality(85);
        $imagick->thumbnailImage($maxW, $maxH, true);

        $thumbPath = PathHelper::buildThumbnailPath($sizeType);
        $tmpFile   = tempnam(sys_get_temp_dir(), 'thumb_');

        $imagick->setImageFormat('jpeg');
        $imagick->writeImage($tmpFile);

        $newW = $imagick->getImageWidth();
        $newH = $imagick->getImageHeight();
        $imagick->destroy();

        Storage::disk('public')->put($thumbPath, file_get_contents($tmpFile));
        unlink($tmpFile);

        return Thumbnail::updateOrCreate(
            ['file_id' => $file->id, 'size_type' => $sizeType],
            [
                'file_path' => $thumbPath,
                'width'     => $newW,
                'height'    => $newH,
                'file_size' => Storage::disk('public')->size($thumbPath),
            ]
        );
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function scaleDimensions(int $origW, int $origH, int $maxW, int $maxH): array
    {
        if ($origW <= $maxW && $origH <= $maxH) return [$origW, $origH];

        $ratio = min($maxW / $origW, $maxH / $origH);
        return [(int)round($origW * $ratio), (int)round($origH * $ratio)];
    }

    private function resolveDriver(): string
    {
        if (extension_loaded('imagick')) return 'imagick';
        if (extension_loaded('gd'))      return 'gd';
        throw new \RuntimeException('Neither GD nor Imagick extension is available.');
    }
}
