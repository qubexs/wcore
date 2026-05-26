<?php

namespace App\Modules\WebsiteMonitor\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitorLog extends Model
{
    protected $table = 'monitor_logs';

    protected $fillable = [
        'monitor_target_id',
        'status_code',
        'response_time',
        'error_message',
        'checked_by',
    ];

    protected $casts = [
        'status_code' => 'integer',
        'response_time' => 'float',
    ];

    public function target(): BelongsTo
    {
        return $this->belongsTo(MonitorTarget::class, 'monitor_target_id');
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'checked_by');
    }
}
