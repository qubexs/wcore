@extends('layouts.admin') {{-- Changed to match your admin.blade.php --}}

@section('main-content') {{-- Changed from 'content' to 'main-content' --}}
<div class="container-fluid py-4">
    <div class="row">
        {{-- Left Column: Conversation List --}}
        <div class="col-lg-4 mb-4">
            <div class="card shadow mb-4 glass-card">
                <div class="card-header py-3 d-flex justify-content-between align-items-center bg-transparent border-bottom-glass">
                    <h6 class="m-0 font-weight-bold text-primary">Conversations</h6>
                    <button class="btn btn-sm btn-primary rounded-circle shadow-sm">
                        <i class="fas fa-plus fa-sm"></i>
                    </button>
                </div>
                <div class="list-group list-group-flush scrollable-list" style="max-height: 70vh; overflow-y: auto;">
                    @forelse($conversations ?? [] as $conversation)
                        <a href="{{ route('messages.show', $conversation->id) }}" 
                           class="list-group-item list-group-item-action border-0 px-3 py-3 {{ ($activeId ?? 0) == $conversation->id ? 'bg-glass-active' : 'bg-transparent' }}">
                            <div class="d-flex align-items-center">
                                <div class="mr-3 position-relative">
                                    <div class="rounded-circle avatar-sm d-flex align-items-center justify-content-center bg-info text-white">
                                        {{ strtoupper(substr($conversation->title ?? 'C', 0, 1)) }}
                                    </div>
                                    @if(($conversation->type ?? '') == 'direct')
                                        <div class="status-indicator-sm bg-success"></div>
                                    @endif
                                </div>
                                <div class="w-100 overflow-hidden">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h6 class="mb-0 text-truncate font-weight-600">{{ $conversation->title ?? 'Group Chat' }}</h6>
                                        <small class="text-gray-500">{{ $conversation->last_message_at?->diffForHumans(null, true) }}</small>
                                    </div>
                                    <div class="small text-gray-500 text-truncate">
                                        {{ $conversation->description }}
                                    </div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="p-4 text-center text-gray-500">No conversations found.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Right Column: Chat Window --}}
        <div class="col-lg-8">
            <div class="card shadow mb-4 glass-card d-flex flex-column" style="height: 70vh;">
                @if(isset($currentConversation))
                    {{-- Chat Header --}}
                    <div class="card-header py-3 bg-transparent border-bottom-glass">
                        <div class="d-flex align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">{{ $currentConversation->title }}</h6>
                            <span class="badge badge-info ml-2">{{ ucfirst($currentConversation->type) }}</span>
                        </div>
                    </div>

                    {{-- Chat Messages Body --}}
                    <div class="card-body overflow-auto p-4 flex-grow-1" id="chatWindow" style="background: rgba(0,0,0,0.02);">
                        @forelse($currentConversation->messages as $msg)
                            <div class="d-flex {{ $msg->sender_id == auth()->id() ? 'justify-content-end' : 'justify-content-start' }} mb-4">
                                <div class="p-3 rounded shadow-sm {{ $msg->sender_id == auth()->id() ? 'bg-primary text-white' : 'bg-white text-dark border' }}" 
                                     style="max-width: 75%; border-radius: 15px;">
                                    
                                    @if($msg->sender_id != auth()->id())
                                        <div class="small font-weight-bold mb-1">{{ $msg->sender->name ?? 'User' }}</div>
                                    @endif
                                    
                                    <div class="message-text">{{ $msg->content }}</div>
                                    
                                    <div class="text-right mt-1" style="font-size: 0.7rem; opacity: 0.8;">
                                        {{ $msg->created_at->format('H:i') }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="h-100 d-flex align-items-center justify-content-center text-gray-500">
                                Start the conversation!
                            </div>
                        @endforelse
                    </div>

                    {{-- Chat Input Footer --}}
                    <div class="card-footer bg-transparent border-top-glass py-3">
                        <form action="{{ route('messages.send', $currentConversation->id) }}" method="POST">
                            @csrf
                            <div class="input-group">
                                <input type="text" name="message" class="form-control border-0 bg-light-glass" placeholder="Type your message..." required autocomplete="off">
                                <div class="input-group-append">
                                    <button class="btn btn-primary shadow-sm" type="submit">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                @else
                    {{-- Empty State --}}
                    <div class="h-100 d-flex flex-column align-items-center justify-content-center text-gray-500">
                        <i class="fas fa-comments fa-4x mb-3" style="opacity: 0.2;"></i>
                        <p>Select a conversation to start messaging.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.7) !important;
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 20px;
    }
    .border-bottom-glass { border-bottom: 1px solid rgba(0, 0, 0, 0.05); }
    .border-top-glass { border-top: 1px solid rgba(0, 0, 0, 0.05); }
    .bg-glass-active {
        background-color: rgba(78, 115, 223, 0.1) !important;
        border-right: 4px solid #4e73df !important;
    }
    .avatar-sm { width: 40px; height: 40px; }
    .bg-light-glass { background: rgba(0, 0, 0, 0.05); border-radius: 10px; }
    #chatWindow::-webkit-scrollbar { width: 5px; }
    #chatWindow::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }

    /* Push the content down so it doesn't touch the header */
.container-fluid.py-4 {
    margin-top: 80px; /* Adjust this number (70px - 100px) based on your header height */
}

/* Ensure the glass card has some breathing room */
.glass-card {
    margin-top: 10px;
    background: rgba(255, 255, 255, 0.7) !important;
    backdrop-filter: blur(15px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 20px;
}
</style>

<script>
    window.onload = function() {
        var chatWindow = document.getElementById("chatWindow");
        if (chatWindow) {
            chatWindow.scrollTop = chatWindow.scrollHeight;
        }
    };
</script>
@endsection