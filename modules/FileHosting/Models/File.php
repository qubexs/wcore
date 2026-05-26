<?php

namespace App\Modules\FileHosting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'folder_id',
        'uploaded_by',
        'name',
        'original_name',
        'stored_name',
        'path',
        'file_path',     // ✅ FIXED: was missing → caused SQLSTATE 1364 on every insert
        'mime_type',
        'size',
        'extension',
        'checksum',
        'description',
        'visibility',
        'expires_at',
        'download_count',
    ];

    protected $casts = [
        'size'           => 'integer',
        'download_count' => 'integer',
        'expires_at'     => 'datetime',
        'deleted_at'     => 'datetime',
    ];

    // ---------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'folder_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'uploaded_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(FileVersion::class, 'file_id')->orderByDesc('version_number');
    }

    public function thumbnails(): HasMany
    {
        return $this->hasMany(Thumbnail::class, 'file_id');
    }

    public function stats(): HasMany
    {
        return $this->hasMany(FileStat::class, 'file_id');
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }

    public function scopeByMime($query, string $mime)
    {
        return $query->where('mime_type', 'like', $mime . '%');
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getFormattedSize(): string
    {
        $bytes = $this->size;
        if ($bytes < 1024)       return "{$bytes} B";
        if ($bytes < 1048576)    return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1073741824) return round($bytes / 1048576, 1) . ' MB';
        return round($bytes / 1073741824, 2) . ' GB';
    }

    public function getUrl(): string
    {
        return Storage::url($this->path);
    }

    public function getPathAttribute(): string
    {
        return $this->attributes['path'] ?? '';
    }

    public function getFilePathAttribute(): string
    {
        // ✅ FIXED: was always returning attributes['path'], ignoring file_path column
        // Now correctly reads file_path first, falls back to path for old records
        return $this->attributes['file_path']
            ?? $this->attributes['path']
            ?? '';
    }

    public function getThumbnail(string $size = 'medium'): ?Thumbnail
    {
        return $this->thumbnails->firstWhere('size_type', $size);
    }

    public function latestVersion(): ?FileVersion
    {
        return $this->versions()->first();
    }

    public function incrementDownloads(): void
    {
        $this->increment('download_count');
    }
}