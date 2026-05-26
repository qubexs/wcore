<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    /**
     * Display all conversations
     */
    public function index()
    {
        try {
            $conversations = Conversation::with(['messages' => function($query) {
                $query->latest()->limit(1);
            }])
            ->where(function($query) {
                $query->whereHas('participants', function($q) {
                    $q->where('user_id', Auth::id());
                })
                ->orWhere('created_by', Auth::id());
            })
            ->orderBy('last_message_at', 'desc')
            ->get();
        } catch (\Exception $e) {
            $conversations = Conversation::with(['messages' => function($query) {
                $query->latest()->limit(1);
            }])
            ->where('created_by', Auth::id())
            ->orderBy('last_message_at', 'desc')
            ->get();
        }

        foreach ($conversations as $conversation) {
            $conversation->unread_count = $this->getUnreadCountForConversation($conversation->id);
        }

        $departments = DB::table('departments')->get();
        
        // Pointing to the NEW folder path: messages/index.blade.php
        return view('messages.index', [
            'conversations' => $conversations,
            'departments' => $departments,
            'activeId' => null // Prevents "Undefined variable" error
        ]);
    }

    /**
     * Show a specific conversation
     */
    public function show($conversationId)
    {
        $conversation = Conversation::with(['messages' => function($query) {
            $query->orderBy('created_at', 'asc');
        }])->findOrFail($conversationId);

        $this->markConversationAsRead($conversationId);

        try {
            $conversations = Conversation::with(['messages' => function($query) {
                $query->latest()->limit(1);
            }])
            ->where(function($query) {
                $query->whereHas('participants', function($q) {
                    $q->where('user_id', Auth::id());
                })
                ->orWhere('created_by', Auth::id());
            })
            ->orderBy('last_message_at', 'desc')
            ->get();
        } catch (\Exception $e) {
            $conversations = Conversation::with(['messages' => function($query) {
                $query->latest()->limit(1);
            }])
            ->where('created_by', Auth::id())
            ->orderBy('last_message_at', 'desc')
            ->get();
        }

        foreach ($conversations as $conv) {
            $conv->unread_count = $this->getUnreadCountForConversation($conv->id);
        }

        $departments = DB::table('departments')->get();
        
        // Pointing to the NEW folder path: messages/index.blade.php
        return view('messages.index', [
            'conversations' => $conversations,
            'currentConversation' => $conversation,
            'activeId' => $conversationId, // Fixes line 17 and highlights the chat
            'departments' => $departments
        ]);
    }

    /**
     * Store a new conversation
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department_id' => 'nullable|exists:departments,id',
            'type' => 'required|in:group,direct,channel'
        ]);

        $conversation = Conversation::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'type' => $validated['type'],
            'created_by' => Auth::id(),
            'last_message_at' => now()
        ]);

        try {
            $conversation->participants()->attach(Auth::id());
        } catch (\Exception $e) {
            \Log::info('Conversation created without participants table');
        }

        return redirect()->route('messages.show', $conversation->id)
            ->with('success', 'Conversation created successfully!');
    }

    /**
     * Send a message in a conversation
     */
    public function sendMessage(Request $request, $conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);

        $validated = $request->validate([
            'message' => 'nullable|string',
            'attachment' => 'nullable|file|max:10240'
        ]);

        $filePath = null;
        $messageType = 'text';

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filePath = $file->store('messages/attachments', 'public');
            $extension = strtolower($file->getClientOriginalExtension());
            $messageType = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']) ? 'image' : 'file';
        }

        Message::create([
            'conversation_id' => $conversationId,
            'sender_id' => Auth::id(),
            'content' => $validated['message'] ?? '',
            'file_path' => $filePath,
            'type' => $messageType,
            'read_at' => null
        ]);

        $conversation->update(['last_message_at' => now()]);

        return back()->with('success', 'Message sent!');
    }

    protected function markConversationAsRead($conversationId)
    {
        Message::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    protected function getUnreadCountForConversation($conversationId)
    {
        return Message::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->count();
    }

    public function getUnreadCount()
    {
        try {
            $count = Message::whereHas('conversation', function($query) {
                $query->whereHas('participants', function($q) {
                    $q->where('user_id', Auth::id());
                });
            })
            ->where('sender_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->count();
        } catch (\Exception $e) {
            $count = Message::whereHas('conversation', function($query) {
                $query->where('created_by', Auth::id());
            })
            ->where('sender_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->count();
        }

        return response()->json(['count' => $count]);
    }

    public function getRecentMessages()
    {
        try {
            $messages = Message::with(['sender', 'conversation'])
                ->whereHas('conversation', function($query) {
                    $query->whereHas('participants', function($q) {
                        $q->where('user_id', Auth::id());
                    });
                })
                ->where('sender_id', '!=', Auth::id())
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            $messages = Message::with(['sender', 'conversation'])
                ->whereHas('conversation', function($query) {
                    $query->where('created_by', Auth::id());
                })
                ->where('sender_id', '!=', Auth::id())
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        return response()->json($messages);
    }

    public function markAsRead(Request $request, $messageId)
    {
        $message = Message::findOrFail($messageId);
        if ($message->sender_id != Auth::id()) {
            $message->update(['read_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'unreadCount' => $this->getUnreadCount()->getData()->count
        ]);
    }
}