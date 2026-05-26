<?php

namespace App\Modules\Infographic\Models;

use Illuminate\Database\Eloquent\Model;

class Infographic extends Model
{
    protected $fillable = [
        'title',
        'content',
        'user_id'
    ];

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}