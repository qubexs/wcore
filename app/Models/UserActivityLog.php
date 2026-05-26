<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivityLog extends Model
{
    use ActivityLogHelpers;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_activity_logs';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'target_user_id',
        'ip_address',
        'user_agent',
        'action_type',
        'description',
        'metadata',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who was affected by the action
     */
    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    // ════════════════════════════════════════════════════════════════
    // LOGGING METHODS
    // ════════════════════════════════════════════════════════════════

    /**
     * Log a user creation
     */
    public static function logCreate(User $user)
    {
        return self::create([
            'user_id' => auth()->id(),
            'target_user_id' => $user->id,
            'action_type' => 'create',
            'description' => "Created user: {$user->name} {$user->last_name} ({$user->email})",
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'metadata' => [
                'new_values' => $user->only(['id', 'name', 'last_name', 'email', 'ein', 'phone', 'status']),
            ],
        ]);
    }

    /**
     * Log a user update
     */
    public static function logUpdate(User $user, array $oldData, array $newData)
    {
        $changedFields = [];
        foreach ($oldData as $key => $oldValue) {
            if (($newData[$key] ?? null) !== $oldValue) {
                $changedFields[] = $key;
            }
        }

        return self::create([
            'user_id' => auth()->id(),
            'target_user_id' => $user->id,
            'action_type' => 'update',
            'description' => "Updated user: {$user->name} {$user->last_name} - Changed: " . implode(', ', $changedFields),
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'metadata' => [
                'old_values' => $oldData,
                'new_values' => $newData,
                'changed_fields' => $changedFields,
            ],
        ]);
    }

    /**
     * Log a user deletion
     */
    public static function logDelete(User $user)
    {
        return self::create([
            'user_id' => auth()->id(),
            'target_user_id' => $user->id,
            'action_type' => 'delete',
            'description' => "Deleted user: {$user->name} {$user->last_name} ({$user->email})",
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'metadata' => [
                'old_values' => $user->only(['id', 'name', 'last_name', 'email', 'ein', 'phone', 'status']),
            ],
        ]);
    }

    /**
     * Log a user status toggle
     */
    public static function logToggle(User $user, string $oldStatus, string $newStatus)
    {
        return self::create([
            'user_id' => auth()->id(),
            'target_user_id' => $user->id,
            'action_type' => 'toggle',
            'description' => "Changed {$user->name} {$user->last_name} status from '{$oldStatus}' to '{$newStatus}'",
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'metadata' => [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ],
        ]);
    }

    // ════════════════════════════════════════════════════════════════
    // QUERY SCOPES
    // ════════════════════════════════════════════════════════════════

    /**
     * Get logs for a specific user (as performer)
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId)->latest();
    }

    /**
     * Get logs affecting a specific user (as target)
     */
    public function scopeAffecting($query, $userId)
    {
        return $query->where('target_user_id', $userId)->latest();
    }

    /**
     * Get logs for specific action type(s)
     */
    public function scopeAction($query, $actionType)
    {
        if (is_array($actionType)) {
            return $query->whereIn('action_type', $actionType)->latest();
        }
        return $query->where('action_type', $actionType)->latest();
    }

    /**
     * Get logs within date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate])->latest();
    }

    /**
     * Get logs from specific IP
     */
    public function scopeFromIp($query, $ip)
    {
        return $query->where('ip_address', $ip)->latest();
    }

    /**
     * Get today's logs
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today())->latest();
    }

    // ════════════════════════════════════════════════════════════════
    // HELPER METHODS
    // ════════════════════════════════════════════════════════════════

    /**
     * Get action label
     */
    public function getActionLabel(): string
    {
        return match($this->action_type) {
            'create' => 'Created',
            'update' => 'Updated',
            'delete' => 'Deleted',
            'toggle' => 'Status Changed',
            'login' => 'Logged In',
            'logout' => 'Logged Out',
            default => ucfirst($this->action_type),
        };
    }

    /**
     * Get action color
     */
    public function getActionColor(): string
    {
        return match($this->action_type) {
            'create' => '#34C759',
            'update' => '#007AFF',
            'delete' => '#FF3B30',
            'toggle' => '#FF9500',
            'login' => '#34C759',
            'logout' => '#FF9500',
            default => '#8E8E93',
        };
    }

    /**
     * Get action icon
     */
    public function getActionIcon(): string
    {
        return match($this->action_type) {
            'create' => 'fas fa-plus-circle',
            'update' => 'fas fa-edit',
            'delete' => 'fas fa-trash-alt',
            'toggle' => 'fas fa-toggle-on',
            'login' => 'fas fa-sign-in-alt',
            'logout' => 'fas fa-sign-out-alt',
            default => 'fas fa-circle',
        };
    }

    /**
     * Get time ago
     */
    public function getTimeAgo(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get formatted date
     */
    public function getFormattedDate(): string
    {
        return $this->created_at->format('d M Y H:i:s');
    }
}