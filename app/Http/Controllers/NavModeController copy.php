<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserSetting
 * ─────────────────────────────────────────────────────────────────────────────
 * Generic per-user key/value settings row.
 *
 * Usage:
 *   // Read
 *   $mode = UserSetting::getValue(auth()->id(), 'nav_mode', 'dock');
 *
 *   // Write (upsert)
 *   UserSetting::setValue(auth()->id(), 'nav_mode', 'sidebar');
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $key
 * @property mixed  $value      (auto-cast from JSON)
 */
class UserSetting extends Model
{
    protected $fillable = ['user_id', 'key', 'value'];

    protected $casts = [
        'value' => 'json',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Static helpers ─────────────────────────────────────────────────────

    /**
     * Read a single setting for a user.
     * Returns $default if the row does not exist.
     */
    public static function getValue(int $userId, string $key, mixed $default = null): mixed
    {
        $row = static::where('user_id', $userId)->where('key', $key)->first();

        return $row ? $row->value : $default;
    }

    /**
     * Write (upsert) a single setting for a user.
     */
    public static function setValue(int $userId, string $key, mixed $value): static
    {
        return static::updateOrCreate(
            ['user_id' => $userId, 'key' => $key],
            ['value'   => $value]
        );
    }

    /**
     * Return ALL settings for a user as a plain associative array.
     * Useful for passing to views: view('...', ['settings' => UserSetting::allFor(auth()->id())])
     */
    public static function allFor(int $userId): array
    {
        return static::where('user_id', $userId)
            ->pluck('value', 'key')
            ->toArray();
    }
}