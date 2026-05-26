<?php

namespace App\Modules\Infographic\Models;

use Illuminate\Database\Eloquent\Model;

class InfographicStat extends Model
{
    protected $fillable = [
        'infographic_id',
        'user_id',
        'action',
        'ip',
        'user_agent'
    ];
}