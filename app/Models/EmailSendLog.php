<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailSendLog extends Model
{
    protected $fillable = [
        'recipient_email',
        'type',
        'status',
    ];
}
