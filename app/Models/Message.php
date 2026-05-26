<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'receiver_id',      // For direct targeting
        'content',
        'file_path',
        'type',
        'metadata',
        'parent_message_id',
        'read_at'           // ← IMPORTANT: Tracks when message was read
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'read_at' => 'datetime',        // ← IMPORTANT: Cast read_at as datetime
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'metadata' => 'array',          // If you store JSON metadata
    ];

    /**
     * Get the conversation that owns the message.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the user who sent the message.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the user who is the receiver of the message (for direct messages).
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Scope to get unread messages for a specific user.
     */
    public function scopeUnreadForUser($query, $userId)
    {
        return $query->where('sender_id', '!=', $userId)
                    ->whereNull('read_at');
    }

    /**
     * Scope to get messages in a conversation.
     */
    public function scopeInConversation($query, $conversationId)
    {
        return $query->where('conversation_id', $conversationId);
    }

    /**
     * Check if message is read.
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Mark message as read.
     */
    public function markAsRead(): bool
    {
        if ($this->read_at === null) {
            return $this->update(['read_at' => now()]);
        }
        return true;
    }
}