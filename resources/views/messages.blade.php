@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card shadow mb-4 glass-card">
                <div class="card-header py-3 d-flex justify-content-between align-items-center bg-transparent border-bottom-glass">
                    <h6 class="m-0 font-weight-bold text-primary">Conversations</h6>
                    <button class="btn btn-sm btn-primary rounded-circle shadow-sm">
                        <i class="fas fa-plus fa-sm"></i>
                    </button>
                </div>
                <div class="list-group list-group-flush scrollable-list" style="max-height: 70vh; overflow-y: auto;">
                    @forelse($conversations as $conversation)
                        <a href="{{ route('messages.show', $conversation->id) }}" 
                           class="list-group-item list-group-item-action border-0 px-3 py-3 {{ $activeId == $conversation->id ? 'bg-glass-active' : 'bg-transparent' }}">
                            <div class="d-flex align-items-center">
                                <div class="mr-3 position-relative">
                                    <div class="rounded-circle avatar-sm d-flex align-items-center justify-content-center bg-info text-white">
                                        {{ strtoupper(substr($conversation->title ?? 'C', 0, 1)) }}
                                    </div>
                                    @if($conversation->type == 'direct')
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

        <div class="col-lg-8">
            <div class="card shadow mb-4 glass-card d-flex flex-column" style="height: 70vh;">
                @if(isset($currentConversation))
                    <div class="card-header py-3 bg-transparent border-bottom-glass">
                        <div class="d-flex align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">{{ $currentConversation->title }}</h6>
                            <span class="badge badge-info ml-2">{{ ucfirst($currentConversation->type) }}</span>
                        </div>
                    </div>

                    <div class="card-body overflow-auto p-4 flex-grow-1" id="chatWindow">
                        </div>

                    <div class="card-footer bg-transparent border-top-glass py-3">
                        <form action="{{ route('messages.send', $currentConversation->id) }}" method="POST">
                            @csrf
                            <div class="input-group">
                                <input type="text" name="message" class="form-control border-0 bg-light-glass" placeholder="Type your message..." required>
                                <div class="input-group-append">
                                    <button class="btn btn-primary shadow-sm" type="submit">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="h-100 d-flex align-items-center justify-content-center text-gray-500">
                        Select a conversation to start messaging.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    /* Styling to match your glass-header.blade.php */
    .glass-card {
        background: rgba(255, 255, 255, 0.8) !important;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .border-bottom-glass { border-bottom: 1px solid rgba(0, 0, 0, 0.05); }
    .border-top-glass { border-top: 1px solid rgba(0, 0, 0, 0.05); }
    
    .bg-glass-active {
        background-color: rgba(78, 115, 223, 0.1) !important;
        border-right: 3px solid #4e73df !important;
    }

    .avatar-sm { width: 40px; height: 40px; }
    
    .status-indicator-sm {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        border: 2px solid #fff;
    }

    @media (prefers-color-scheme: dark) {
        .glass-card {
            background: rgba(30, 41, 59, 0.7) !important;
            border-color: rgba(255, 255, 255, 0.1);
            color: #f1f5f9;
        }
        .text-gray-500 { color: #94a3b8 !important; }
        .bg-light-glass { background: rgba(51, 65, 85, 0.6); color: white; }
    }
</style>
@endsection