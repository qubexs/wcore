@extends('layouts.admin')

@section('main-content')
<script src="https://cdn.jsdelivr.net/npm/emoji-mart@latest/dist/browser.js"></script>

<div class="container-fluid" style="margin-top: 100px; position: relative; z-index: 1;">
    <div class="row">
        {{-- Left Column: Conversation List --}}
        <div class="col-lg-4 mb-4">
            <div class="card shadow glass-card">
                <div class="card-header py-3 d-flex justify-content-between align-items-center bg-transparent border-bottom-glass">
                    <h6 class="m-0 font-weight-bold text-primary">Conversations</h6>
                    <button class="btn btn-sm btn-primary rounded-circle shadow-sm" data-toggle="modal" data-target="#newChatModal">
                        <i class="fas fa-plus fa-sm"></i>
                    </button>
                </div>
                <div class="list-group list-group-flush scrollable-list" style="max-height: 70vh; overflow-y: auto;" id="conversationList">
                    @forelse($conversations ?? [] as $conv)
                        <a href="{{ route('messages', $conv->id) }}" 
                           class="list-group-item list-group-item-action border-0 px-3 py-3 {{ ($activeId ?? 0) == $conv->id ? 'bg-glass-active' : 'bg-transparent' }} conversation-item"
                           data-conversation-id="{{ $conv->id }}">
                            <div class="d-flex align-items-center">
                                <div class="mr-3 position-relative">
                                    <div class="rounded-circle avatar-sm d-flex align-items-center justify-content-center bg-info text-white shadow-sm">
                                        {{ strtoupper(substr($conv->title ?? 'C', 0, 1)) }}
                                    </div>
                                    @if(isset($conv->unread_count) && $conv->unread_count > 0)
                                        <span class="badge badge-danger badge-pill position-absolute unread-badge" 
                                              style="top: -5px; right: -10px; font-size: 0.65rem; min-width: 20px;">
                                            {{ $conv->unread_count > 99 ? '99+' : $conv->unread_count }}
                                        </span>
                                    @endif
                                </div>
                                <div class="w-100 overflow-hidden">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h6 class="mb-0 text-truncate {{ isset($conv->unread_count) && $conv->unread_count > 0 ? 'font-weight-bold' : '' }}" 
                                            style="font-size: 0.9rem;">
                                            {{ $conv->title }}
                                        </h6>
                                        <small class="text-muted" style="font-size: 0.7rem;">
                                            {{ $conv->last_message_at ? \Carbon\Carbon::parse($conv->last_message_at)->diffForHumans(null, true) : 'New' }}
                                        </small>
                                    </div>
                                    <div class="small text-muted text-truncate">{{ $conv->description ?? 'No description' }}</div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="p-4 text-center text-muted">No conversations found.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Right Column: Chat Window --}}
        <div class="col-lg-8">
            <div class="card shadow glass-card d-flex flex-column" style="height: 75vh; position: relative;">
                @if(isset($currentConversation))
                    <div class="card-header py-3 bg-transparent border-bottom-glass">
                        <h6 class="m-0 font-weight-bold text-primary">{{ $currentConversation->title }}</h6>
                        @if($currentConversation->description)
                            <small class="text-muted d-block text-truncate">{{ $currentConversation->description }}</small>
                        @endif
                    </div>

                    <div class="card-body overflow-auto p-4 flex-grow-1" id="chatWindow" style="background: rgba(0,0,0,0.01);">
                        @foreach($currentConversation->messages as $msg)
                            <div class="d-flex {{ $msg->sender_id == auth()->id() ? 'justify-content-end' : 'justify-content-start' }} mb-4">
                                <div class="p-3 shadow-sm {{ $msg->sender_id == auth()->id() ? 'bg-primary text-white' : 'bg-white border text-dark' }}" 
                                     style="max-width: 80%; border-radius: 18px; position: relative;">
                                    
                                    {{-- Show "New" badge for unread messages from others --}}
                                    @if($msg->sender_id != auth()->id() && !$msg->read_at)
                                        <span class="badge badge-success position-absolute" 
                                              style="top: -8px; right: -8px; font-size: 0.65rem;">
                                            New
                                        </span>
                                    @endif
                                    
                                    @if($msg->type == 'image' && $msg->file_path)
                                        <div class="mb-2">
                                            <a href="{{ asset('storage/' . $msg->file_path) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $msg->file_path) }}" class="img-fluid rounded shadow-sm" style="max-height: 250px; border: 2px solid rgba(255,255,255,0.2);">
                                            </a>
                                        </div>
                                    @endif

                                    @if($msg->type == 'file' && $msg->file_path && Str::endsWith(strtolower($msg->file_path), '.pdf'))
                                        <div class="rounded overflow-hidden mb-2 shadow-sm" style="height: 180px; border: 1px solid rgba(0,0,0,0.1); background: #eee;">
                                            <iframe src="{{ asset('storage/' . $msg->file_path) }}#page=1&toolbar=0&navpanes=0" width="100%" height="100%" style="border:none;"></iframe>
                                        </div>
                                    @endif

                                    @if($msg->type == 'file' && $msg->file_path)
                                        <div class="d-flex align-items-center p-2 mb-2 rounded {{ $msg->sender_id == auth()->id() ? 'bg-white text-dark' : 'bg-light' }}" style="gap: 10px; border: 1px solid rgba(0,0,0,0.05);">
                                            @php
                                                $ext = strtolower(pathinfo($msg->file_path, PATHINFO_EXTENSION));
                                                $icon = match($ext) {
                                                    'xls', 'xlsx' => 'fa-file-excel text-success',
                                                    'doc', 'docx' => 'fa-file-word text-primary',
                                                    'ppt', 'pptx' => 'fa-file-powerpoint text-danger',
                                                    'pdf' => 'fa-file-pdf text-danger',
                                                    default => 'fa-file-alt text-secondary'
                                                };
                                            @endphp
                                            <i class="fas {{ $icon }} fa-2x"></i>
                                            <div class="overflow-hidden flex-grow-1">
                                                <div class="small text-truncate font-weight-bold">{{ basename($msg->file_path) }}</div>
                                                <div class="mt-1">
                                                    <a href="{{ asset('storage/' . $msg->file_path) }}" target="_blank" class="badge badge-info">View</a>
                                                    <a href="{{ asset('storage/' . $msg->file_path) }}" download class="badge badge-success">Download</a>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="message-text" style="word-wrap: break-word; white-space: pre-wrap;">{{ $msg->content }}</div>
                                    <div class="text-right mt-1" style="font-size: 0.65rem; opacity: 0.8;">
                                        {{ $msg->created_at->format('H:i') }}
                                        @if($msg->sender_id == auth()->id())
                                            <i class="fas fa-check{{ $msg->read_at ? '-double text-info' : '' }}"></i>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Chat Input Footer --}}
                    <div class="card-footer bg-transparent border-top-glass py-3">
                        <form action="{{ route('messages.send', $currentConversation->id) }}" method="POST" enctype="multipart/form-data" id="chatForm">
                            @csrf
                            <div class="input-group align-items-stretch" style="height: 50px;">
                                <div class="input-group-prepend">
                                    <label class="btn btn-sm btn-light mb-0 border-0 d-flex align-items-center px-3" for="fileInput" title="Attach File" style="border-top-left-radius: 12px; border-bottom-left-radius: 12px;">
                                        <i class="fas fa-paperclip fa-sm"></i>
                                    </label>
                                    <input type="file" name="attachment" id="fileInput" class="d-none" onchange="previewFileName()">
                                    
                                    <button type="button" class="btn btn-sm btn-light border-0 d-flex align-items-center px-3" id="emojiBtn">
                                        <span style="font-size: 0.9rem;">😀</span>
                                    </button>
                                </div>

                                <input type="text" name="message" id="messageInput" 
                                       class="form-control border-0 bg-light-glass px-3 h-100" 
                                       placeholder="Type a message..." 
                                       autocomplete="off" 
                                       style="font-size: 1rem; border-radius: 0;">
                                
                                <div class="input-group-append">
                                    <button class="btn btn-primary d-flex align-items-center px-4 shadow-sm" type="submit" style="border-top-right-radius: 12px; border-bottom-right-radius: 12px;">
                                        <i class="fas fa-paper-plane fa-sm"></i>
                                    </button>
                                </div>
                            </div>
                            <small id="fileNamePreview" class="text-primary mt-2 d-block font-weight-bold ml-2" style="font-size: 0.75rem;"></small>
                        </form>
                        <div id="emojiPicker" style="position: absolute; bottom: 70px; left: 20px; display: none; z-index: 9999;"></div>
                    </div>
                @else
                    <div class="d-flex align-items-center justify-content-center h-100 text-center text-muted">
                        <div>
                            <i class="fas fa-comments fa-4x mb-3" style="opacity: 0.2;"></i>
                            <p>Select a chat or click "+" to start a new conversation.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- MODAL: Create New Conversation --}}
<div class="modal fade" id="newChatModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <form action="{{ route('messages.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white" style="border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title font-weight-bold">New Conversation</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark">Title</label>
                        <input type="text" name="title" class="form-control" placeholder="Project Alpha Chat..." required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="What is this chat about?"></textarea>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark">Department</label>
                        <select name="department_id" class="form-control text-sm">
                            <option value="">-- General / No Department --</option>
                            @isset($departments)
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            @endisset
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold text-dark">Type</label>
                        <select name="type" class="form-control">
                            <option value="group">Group Chat</option>
                            <option value="direct">Direct Message</option>
                            <option value="channel">Channel</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">Create Now</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: 20px;
    }
    .bg-glass-active {
        background-color: rgba(78, 115, 223, 0.1) !important;
        border-right: 4px solid #4e73df !important;
    }
    .bg-light-glass { 
        background: rgba(0, 0, 0, 0.06); 
        border-radius: 0; 
    }
    .avatar-sm { 
        width: 40px; 
        height: 40px; 
    }
    iframe { 
        pointer-events: none; 
    }
    
    /* Unread badge animation */
    .unread-badge {
        animation: pulse 2s infinite;
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
    }
    
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
        }
    }
    
    /* Conversation item hover */
    .conversation-item {
        transition: all 0.2s ease;
    }
    
    .conversation-item:hover {
        transform: translateX(5px);
        background-color: rgba(78, 115, 223, 0.05) !important;
    }
    
    /* Input Group Height and Alignment Fixes */
    .input-group-prepend .btn, .input-group-append .btn {
        border-radius: 0;
    }
    .input-group-prepend :first-child {
        border-top-left-radius: 12px !important;
        border-bottom-left-radius: 12px !important;
    }
    .input-group-append :last-child {
        border-top-right-radius: 12px !important;
        border-bottom-right-radius: 12px !important;
    }
    .input-group .form-control:focus {
        box-shadow: none;
        background: rgba(0, 0, 0, 0.04);
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const messageInput = document.getElementById('messageInput');
        const pickerContainer = document.getElementById('emojiPicker');
        const emojiBtn = document.getElementById('emojiBtn');

        if (emojiBtn) {
            const pickerOptions = { 
                onEmojiSelect: (emoji) => {
                    messageInput.value += emoji.native;
                    pickerContainer.style.display = 'none';
                    messageInput.focus();
                }
            };
            const picker = new EmojiMart.Picker(pickerOptions);
            pickerContainer.appendChild(picker);

            emojiBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                pickerContainer.style.display = pickerContainer.style.display === 'none' ? 'block' : 'none';
            });

            document.addEventListener('click', (e) => {
                if (!pickerContainer.contains(e.target) && e.target !== emojiBtn) {
                    pickerContainer.style.display = 'none';
                }
            });
        }

        const chatWindow = document.getElementById("chatWindow");
        if (chatWindow) {
            chatWindow.scrollTop = chatWindow.scrollHeight;
        }

        const modal = document.getElementById('newChatModal');
        if (modal) {
            document.body.appendChild(modal);
        }
    });

    function previewFileName() {
        const input = document.getElementById('fileInput');
        const preview = document.getElementById('fileNamePreview');
        if (input.files.length > 0) {
            preview.innerText = "📁 " + input.files[0].name;
        }
    }
</script>
@endsection