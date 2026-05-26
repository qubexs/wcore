<?php

namespace App\Modules\FileHosting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// ============================================================
// FileVersion
// ============================================================
class FileVersion extends Model
{
    protected $fillable = [
        'file_id', 'uploaded_by', 'stored_name', 'file_path',
        'size', 'checksum', 'version_number', 'change_notes',
    ];

    protected $casts = ['size' => 'integer'];

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class, 'file_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'uploaded_by');
    }

    public function getFormattedSize(): string
    {
        $bytes = $this->size;
        if ($bytes < 1024)       return "{$bytes} B";
        if ($bytes < 1048576)    return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1073741824) return round($bytes / 1048576, 1) . ' MB';
        return round($bytes / 1073741824, 2) . ' GB';
    }
}
