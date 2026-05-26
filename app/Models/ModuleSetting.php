<?php

// app/Models/ModuleSetting.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleSetting extends Model
{
    protected $table = 'module_settings';

    protected $fillable = ['module', 'key', 'value'];

    protected $casts = [
        // You can cast to boolean/integer when retrieving, but we'll do it manually in the service
    ];
}