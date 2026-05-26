<?php
namespace App\Modules\FileHosting\Models;

use Illuminate\Database\Eloquent\Model;

class FileStat extends Model
{
    protected $fillable = [
        'file_id',
        'user_id',
        'action',     // view, download, upload
        'ip',
        'user_agent'
    ];

    /** Relation to File */
    public function file()
    {
        return $this->belongsTo(File::class);
    }

    /** Relation to User (optional) */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
