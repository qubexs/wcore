<?php

// src\Models\File.php

namespace App\Modules\FileHosting\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = ['name', 'path', 'thumbnail', 'uploaded_by'];
}
