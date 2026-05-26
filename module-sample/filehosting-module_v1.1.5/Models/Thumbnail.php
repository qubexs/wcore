<?php

namespace Modules\FileHosting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Thumbnail extends Model
{
    protected $fillable = [
        'file_id', 'size_type', 'file_path',
        'width', 'height', 'file_size',
    ];

    protected $casts = [
        'width'     => 'integer',
        'height'    => 'integer',
        'file_size' => 'integer',
    ];

    const SIZE_SMALL  = 'small';   // 80x80
    const SIZE_MEDIUM = 'medium';  // 300x300
    const SIZE_LARGE  = 'large';   // 800x800

    const SIZE_DIMENSIONS = [
        self::SIZE_SMALL  => ['width' => 80,  'height' => 80],
        self::SIZE_MEDIUM => ['width' => 300, 'height' => 300],
        self::SIZE_LARGE  => ['width' => 800, 'height' => 800],
    ];

    // ---------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class, 'file_id');
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    public function getUrl(): string
    {
        return Storage::url($this->file_path);
    }

    public function getDimensions(): string
    {
        return "{$this->width}x{$this->height}";
    }
}
