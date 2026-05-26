<?php


//{
    //protected $fillable = ['name','slug','is_active'];
//}

// app/Models/Module.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $table = 'modules';

    protected $fillable = [
        'slug',
        'name',
        'version',
        'active', // <-- make sure this is here
    ];
}
