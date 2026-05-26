<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationParticipant extends Model
{
    // The table name is conversation_participants
    protected $table = 'conversation_participants';

    protected $fillable = [
        'conversation_id', 
        'user_id', 
        'role', 
        'last_read_at'
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}