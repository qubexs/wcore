<?php

namespace App\Modules\WebsiteMonitor\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitorAlert extends Model
{
    protected $table = 'monitor_alerts';

    protected $fillable = [
        'monitor_target_id',
        'alert_type',
        'sent_to_user_id',
        'sent_to_email',
        'subject',
        'message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function target(): BelongsTo
    {
        return $this->belongsTo(MonitorTarget::class, 'monitor_target_id');
    }

    public function sentToUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'sent_to_user_id');
    }
}
