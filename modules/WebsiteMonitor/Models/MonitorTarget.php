<?php

namespace App\Modules\WebsiteMonitor\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitorTarget extends Model
{
    protected $table = 'monitor_targets';

    protected $fillable = [
        'name',
        'url',
        'method',
        'check_interval',
        'timeout',
        'expected_status',
        'check_string',
        'is_active',
        'alert_on_down',
        'alert_methods',
        'pic_user_id',
        'last_checked_at',
        'last_status',
        'last_response_time',
        'last_error',
        'created_by',
    ];

    protected $casts = [
        'check_interval' => 'integer',
        'timeout' => 'integer',
        'expected_status' => 'integer',
        'is_active' => 'boolean',
        'alert_on_down' => 'boolean',
        'last_checked_at' => 'datetime',
        'last_response_time' => 'float',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(MonitorLog::class, 'monitor_target_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(MonitorAlert::class, 'monitor_target_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'pic_user_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeNeedsCheck($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('last_checked_at')
                  ->orWhereRaw('TIMESTAMPDIFF(MINUTE, last_checked_at, NOW()) >= check_interval');
            });
    }

    public function isUp(): bool
    {
        return $this->last_status === $this->expected_status;
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->last_checked_at === null) return 'Never Checked';
        return $this->isUp() ? 'Healthy' : 'Down';
    }

    public function getStatusColorAttribute(): string
    {
        if ($this->last_checked_at === null) return 'secondary';
        return $this->isUp() ? 'success' : 'danger';
    }
}
