<?php

// app/Models/User.php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /*
    |--------------------------------------------------------------------------
    | Fillable — all columns the controller writes to
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'ein',
        'name',
        'middle_name',
        'last_name',
        'email',
        'password',
        'phone',
        'phone_extension',
        'avatar',
        'status',
        'last_login_at',
        'last_login_ip',
        // Personal Information
        'salutation',
        'professional_title',
        'job_title',
        'bio',
        // Contact Information
        'secondary_email',
        'mobile_phone',
        'fax',
        // Professional Information
        'specialization',
        'mmc_reg_no',
        'mmc_reg_expiry',
        'other_reg_no',
        'other_reg_expiry',
        'key_credentials',
        // Preferences
        'preferred_language',
        'timezone',
        'two_factor_enabled',
        // Email Verification
        'verification_code',
        'verification_code_expires_at',
        'is_verified',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at'            => 'datetime',
        'last_login_at'                => 'datetime',
        'verification_code_expires_at' => 'datetime',
        'is_verified'                  => 'boolean',
        // no 'password' => 'hashed' — using setPasswordAttribute() mutator below
    ];

    /*
    |--------------------------------------------------------------------------
    | Mutators
    |--------------------------------------------------------------------------
    */

    /**
     * Always bcrypt the password on set.
     * Handles: User::create(['password'=>'...']) and $user->password = '...'
     */
    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password'] = bcrypt($value);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Departments this user belongs to.
     * Pivot: department_user (user_id, department_id, is_primary)
     */
    public function departments()
    {
        return $this->belongsToMany(
            Department::class,
            'department_user',  // pivot table
            'user_id',          // FK -> users
            'department_id'     // FK -> departments
        )->withPivot('is_primary')->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * {{ $user->full_name }}
     */
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([
            $this->name,
            $this->middle_name,
            $this->last_name,
        ]);
        return implode(' ', $parts);
    }

    /**
     * {{ $user->initials }} — used for avatar fallback
     */
    public function getInitialsAttribute(): string
    {
        return strtoupper(
            substr($this->name,           0, 1) .
            substr($this->last_name ?? '', 0, 1)
        );
    }

    /**
     * {{ $user->profile_completeness }} — percentage of profile filled
     */
    public function getProfileCompletenessAttribute(): int
    {
        $fields = [
            'salutation',
            'professional_title',
            'job_title',
            'bio',
            'phone',
            'mobile_phone',
            'fax',
            'secondary_email',
            'specialization',
            'mmc_reg_no',
            'other_reg_no',
            'key_credentials',
            'avatar',
            'preferred_language',
            'timezone',
        ];

        $filled = 0;
        foreach ($fields as $field) {
            if (!empty($this->$field)) {
                $filled++;
            }
        }

        return min(100, round(($filled / count($fields)) * 100));
    }

    /**
     * {{ $user->department }} — get primary department name
     */
    public function getDepartmentAttribute(): ?string
    {
        $primary = $this->departments()->wherePivot('is_primary', true)->first();
        return $primary?->name;
    }

    /**
     * {{ $user->avatar_url }} — full storage URL or null
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? asset('storage/' . $this->avatar) : null;
    }


    /**
     * ═══════════════════════════════════════════════════════════════════
     * ONLINE/OFFLINE STATUS MANAGEMENT
     * ═══════════════════════════════════════════════════════════════════
     */

    /**
     * Mark user as online
     * Called when user logs in or on each request
     */
    public function markOnline(): void
    {
        $this->update([
            'is_online' => true,
            'presence_status' => 'online',
            'last_activity' => now(),
            'last_seen' => now(),
        ]);

        // Cache online status for 5 minutes
        Cache::put("user_online_{$this->id}", true, now()->addMinutes(5));
    }

    /**
     * Mark user as offline
     * Called when user logs out
     */
    public function markOffline(): void
    {
        $this->update([
            'is_online' => false,
            'presence_status' => 'offline',
            'last_seen' => now(),
        ]);

        // Remove from cache
        Cache::forget("user_online_{$this->id}");
    }

    /**
     * Update last activity timestamp
     * Called on every request via middleware
     */
    public function updateActivity(): void
    {
        $this->update([
            'last_activity' => now(),
        ]);

        // Keep cache alive
        Cache::put("user_online_{$this->id}", true, now()->addMinutes(5));
    }

    /**
     * Check if user is online
     * Returns true if last activity was within timeout period
     */
    public function isOnline(): bool
    {
        // Check cache first (faster)
        if (Cache::has("user_online_{$this->id}")) {
            return true;
        }

        // Fallback to database check (5 minute timeout)
        if (!$this->last_activity) {
            return false;
        }

        return $this->last_activity->greaterThan(now()->subMinutes(5));
    }

    /**
     * Check if user was recently online (within 30 minutes)
     */
    public function wasRecentlyOnline(): bool
    {
        if (!$this->last_seen) {
            return false;
        }

        return $this->last_seen->greaterThan(now()->subMinutes(30));
    }

    /**
     * Get time since user was last online
     */
    public function getLastSeenAttribute(): ?string
    {
        if (!$this->attributes['last_seen'] ?? null) {
            return 'Never';
        }

        return $this->last_seen->diffForHumans();
    }

    /**
     * Set presence status (online, away, busy, offline)
     */
    public function setPresenceStatus(string $status): void
    {
        if (!in_array($status, ['online', 'away', 'busy', 'offline'])) {
            throw new \InvalidArgumentException("Invalid status: {$status}");
        }

        $this->update([
            'presence_status' => $status,
            'last_activity' => now(),
        ]);

        Cache::put("user_presence_{$this->id}", $status, now()->addMinutes(5));
    }

    /**
     * Get presence status
     */
    public function getPresenceStatus(): string
    {
        return $this->presence_status ?? 'offline';
    }

    /**
     * Get presence indicator color for UI
     */
    public function getPresenceColor(): string
    {
        return match ($this->presence_status) {
            'online' => 'success',   // Green
            'away' => 'warning',     // Yellow
            'busy' => 'danger',      // Red
            default => 'secondary',  // Gray
        };
    }

    /**
     * Get presence icon for UI
     */
    public function getPresenceIcon(): string
    {
        return match ($this->presence_status) {
            'online' => 'fa-circle text-success',
            'away' => 'fa-circle text-warning',
            'busy' => 'fa-circle text-danger',
            default => 'fa-circle text-secondary',
        };
    }

    /**
     * Update device information
     * Called from middleware with user agent
     */
    public function updateDeviceInfo(?string $userAgent = null): void
    {
        $userAgent = $userAgent ?? request()->userAgent();
        
        // Detect device type
        $agent = new Agent();
        $agent->setUserAgent($userAgent);
        
        $device = 'unknown';
        if ($agent->isMobile()) {
            $device = 'mobile';
        } elseif ($agent->isTablet()) {
            $device = 'tablet';
        } else {
            $device = 'desktop';
        }

        $this->update([
            'current_device' => $device,
            'current_ip' => request()->ip(),
            'current_user_agent' => $userAgent,
        ]);
    }

    /**
     * Get browser name from user agent
     */
    public function getBrowserName(): string
    {
        if (!$this->current_user_agent) {
            return 'Unknown';
        }

        $agent = new Agent();
        $agent->setUserAgent($this->current_user_agent);
        
        return $agent->browser() ?? 'Unknown';
    }

    /**
     * Get device icon for UI
     */
    public function getDeviceIcon(): string
    {
        return match ($this->current_device) {
            'mobile' => 'fa-mobile-alt',
            'tablet' => 'fa-tablet-alt',
            'desktop' => 'fa-desktop',
            default => 'fa-question-circle',
        };
    }

    /**
     * Get formatted status for display
     * Example: "Online from Chrome on Desktop"
     */
    public function getFormattedStatus(): string
    {
        $parts = [];

        // Presence status
        $statusLabel = match ($this->presence_status) {
            'away' => 'Away',
            'busy' => 'Busy',
            'offline' => 'Offline',
            default => 'Online',
        };
        $parts[] = $statusLabel;

        // Browser if online
        if ($this->isOnline()) {
            $browser = $this->getBrowserName();
            if ($browser) {
                $parts[] = "via {$browser}";
            }

            // Device if online
            if ($this->current_device) {
                $device = ucfirst($this->current_device);
                $parts[] = "on {$device}";
            }
        } else {
            // Last seen if offline
            if ($this->last_seen) {
                $parts[] = "Last seen " . $this->last_seen->diffForHumans();
            }
        }

        return implode(' ', $parts);
    }

    /**
     * Get all online users
     */
    public static function getOnlineUsers($limit = null)
    {
        $query = self::where('is_online', true)
            ->orderBy('last_activity', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get all offline users
     */
    public static function getOfflineUsers($limit = null)
    {
        $query = self::where('is_online', false)
            ->orderBy('last_seen', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get online count
     */
    public static function getOnlineCount(): int
    {
        return self::where('is_online', true)->count();
    }

    /**
     * Get recently active users (within 30 minutes)
     */
    public static function getRecentlyActive($limit = 10)
    {
        return self::where('last_seen', '>', now()->subMinutes(30))
            ->orderBy('last_seen', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Sync online status from cache
     * Run this periodically (via cron or queue) to sync cache with database
     */
    public static function syncOnlineStatus(): void
    {
        // Get all users
        self::all()->each(function ($user) {
            if (Cache::has("user_online_{$user->id}")) {
                // User has activity in cache, mark as online
                $user->markOnline();
            } else {
                // No recent activity, mark as offline
                if ($user->is_online) {
                    $user->markOffline();
                }
            }
        });
    }

    /**
     * Get user's session info
     */
    public function getSessionInfo(): array
    {
        return [
            'is_online' => $this->is_online,
            'presence_status' => $this->presence_status,
            'last_activity' => $this->last_activity?->toIso8601String(),
            'last_seen' => $this->last_seen?->toIso8601String(),
            'device' => $this->current_device,
            'browser' => $this->getBrowserName(),
            'ip' => $this->current_ip,
            'formatted_status' => $this->getFormattedStatus(),
        ];
    }
}

