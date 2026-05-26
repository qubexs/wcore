<?php

namespace App\Modules\FileHosting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileStat extends Model
{
    protected $fillable = [
        'file_id', 'folder_id', 'uploaded_by',
        'action', 'metadata', 'ip', 'user_agent',
    ];

    protected $casts = ['metadata' => 'array'];

    // Valid action constants
    const ACTION_UPLOAD          = 'upload';
    const ACTION_DOWNLOAD        = 'download';
    const ACTION_DELETE          = 'delete';
    const ACTION_MOVE            = 'move';
    const ACTION_RENAME          = 'rename';
    const ACTION_REPLACE         = 'replace';
    const ACTION_CREATE_FOLDER   = 'create_folder';
    const ACTION_DELETE_FOLDER   = 'delete_folder';
    const ACTION_VIEW            = 'view';
    const ACTION_RESTORE_VERSION = 'restore_version';

    // ---------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class, 'file_id');
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'folder_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'uploaded_by');
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ---------------------------------------------------------------
    // Static factory
    // ---------------------------------------------------------------

    public static function record(
        string $action,
        ?int $fileId   = null,
        ?int $folderId = null,
        array $meta    = []
    ): self {
        return static::create([
            'action'     => $action,
            'file_id'    => $fileId,
            'folder_id'  => $folderId,
            'uploaded_by'    => auth()->id(),
            'metadata'   => $meta,
            'ip'         => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
