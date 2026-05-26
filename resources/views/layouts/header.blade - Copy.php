<!-- resources/views/layouts/header.blade.php -->
<nav class="navbar navbar-expand navbar-light topbar static-top glass-header">
    
    <!-- Left: Current Time -->
    <div class="navbar-brand-time d-none d-md-flex align-items-center mr-4">
        <div class="time-display">
            <span class="time-clock" id="liveClock">00:00:00</span>
            <span class="time-date" id="liveDate">Loading...</span>
        </div>
    </div>

    <!-- Center: Search Bar (Desktop & Mobile) -->
    <div class="navbar-search-wrapper mx-auto">
        <form class="navbar-search-centered">
            <div class="input-group input-group-glass">
                <input type="text" class="form-control border-0" placeholder="Search for..." aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-glass" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Mobile Search Toggle -->
    <button id="searchDropdownMobile" class="btn btn-link d-md-none rounded-circle mr-3" data-toggle="collapse" data-target="#mobileSearch">
        <i class="fas fa-search"></i>
    </button>

    <!-- Sidebar Toggle (Mobile) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <!-- Right: User Controls -->
    <ul class="navbar-nav ml-auto align-items-center">

        <!-- Nav Item - Alerts -->
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bell fa-fw"></i>
                <span class="badge badge-counter glass-badge">3+</span>
            </a>
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
                <h6 class="dropdown-header">Alerts Center</h6>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="mr-3">
                        <div class="icon-circle bg-primary">
                            <i class="fas fa-file-alt text-white"></i>
                        </div>
                    </div>
                    <div>
                        <div class="small text-gray-500">December 12, 2019</div>
                        <span class="font-weight-bold">A new monthly report is ready to download!</span>
                    </div>
                </a>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="mr-3">
                        <div class="icon-circle bg-success">
                            <i class="fas fa-donate text-white"></i>
                        </div>
                    </div>
                    <div>
                        <div class="small text-gray-500">December 7, 2019</div>
                        $290.29 has been deposited into your account!
                    </div>
                </a>
                <a class="dropdown-item text-center small text-gray-500" href="#">Show All Alerts</a>
            </div>
        </li>

        <!-- Nav Item - Messages -->
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button" 
               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-envelope fa-fw"></i>
                <span class="badge badge-counter glass-badge" id="unreadBadge" style="{{ isset($unreadCount) && $unreadCount > 0 ? '' : 'display: none;' }}">
                    {{ isset($unreadCount) && $unreadCount > 0 ? ($unreadCount > 99 ? '99+' : $unreadCount) : '' }}
                </span>
            </a>
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" 
                 aria-labelledby="messagesDropdown" id="messagesDropdownMenu">
                <h6 class="dropdown-header">Message Center</h6>
                
                @if(isset($recentMessages) && $recentMessages->count() > 0)
                    @foreach($recentMessages as $message)
                        <a class="dropdown-item d-flex align-items-center message-item" 
                           href="{{ route('messages.show', $message->conversation_id) }}"
                           data-message-id="{{ $message->id }}"
                           data-conversation-id="{{ $message->conversation_id }}">
                            <div class="dropdown-list-image mr-3">
                                <img class="rounded-circle" 
                                     src="{{ $message->sender->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($message->sender->name).'&background=random' }}" 
                                     alt="{{ $message->sender->name }}">
                                <div class="status-indicator {{ $message->sender->is_online ? 'bg-success' : 'bg-secondary' }}"></div>
                            </div>
                            <div class="{{ $message->read_at ? '' : 'font-weight-bold' }}">
                                <div class="text-truncate">{{ Str::limit($message->content, 50) }}</div>
                                <div class="small text-gray-500">
                                    {{ $message->sender->name }} · {{ $message->created_at->diffForHumans() }}
                                </div>
                                @if(!$message->read_at)
                                    <span class="badge badge-primary badge-sm">New</span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                @else
                    <a class="dropdown-item text-center small text-gray-500" href="#">
                        No new messages
                    </a>
                @endif
                
                <a class="dropdown-item text-center small text-gray-500" href="{{ route('messages.index') }}">
                    Read More Messages
                </a>
            </div>
        </li>

        <div class="topbar-divider d-none d-sm-block"></div>

        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle user-profile-trigger" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small user-name">{{ auth()->user()?->name ?? 'Guest' }}</span>
                <figure class="img-profile rounded-circle avatar font-weight-bold" data-initial="{{ auth()->user()?->name[0] ?? 'G' }}">
                    {{ auth()->user()?->name[0] ?? 'G' }}
                </figure>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="{{ route('profile') }}">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    {{ __('Profile') }}
                </a>
                <a class="dropdown-item" href="javascript:void(0)">
                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                    {{ __('Settings') }}
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    {{ __('Logout') }}
                </a>
            </div>
        </li>

    </ul>
</nav>

<!-- Mobile Search Collapse -->
<div class="collapse d-md-none mobile-search-container" id="mobileSearch">
    <form class="p-3">
        <div class="input-group input-group-glass">
            <input type="text" class="form-control border-0" placeholder="Search for..." aria-label="Search">
            <div class="input-group-append">
                <button class="btn btn-glass" type="button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </form>
</div>

<style>
/* Glass Header Styles */
.glass-header {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.3);
    padding: 0.75rem 1.5rem;
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
}

/* Time Display */
.navbar-brand-time {
    min-width: 140px;
}

.time-display {
    display: flex;
    flex-direction: column;
    line-height: 1.2;
}

.time-clock {
    font-size: 1.25rem;
    font-weight: 700;
    color: #2d3748;
    font-family: 'Inter', monospace;
    letter-spacing: 0.5px;
}

.time-date {
    font-size: 0.75rem;
    color: #718096;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Centered Search */
.navbar-search-wrapper {
    flex: 1;
    display: flex;
    justify-content: center;
    max-width: 600px;
}

.navbar-search-centered {
    width: 100%;
    max-width: 500px;
}

.input-group-glass {
    background: rgba(255, 255, 255, 0.6);
    border-radius: 50px;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.4);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.input-group-glass:focus-within {
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
}

.input-group-glass .form-control {
    background: transparent;
    padding: 0.75rem 1.5rem;
    font-size: 0.9rem;
}

.input-group-glass .form-control:focus {
    box-shadow: none;
}

.btn-glass {
    background: rgba(99, 102, 241, 0.1);
    border: none;
    color: #6366f1;
    padding: 0.75rem 1.25rem;
    transition: all 0.3s ease;
}

.btn-glass:hover {
    background: rgba(99, 102, 241, 0.2);
    color: #4f46e5;
}

/* Glass Badge */
.glass-badge {
    background: rgba(239, 68, 68, 0.9) !important;
    backdrop-filter: blur(4px);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

/* Badge animation when count updates */
@keyframes badgePulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

.badge-pulse {
    animation: badgePulse 0.3s ease;
}

/* User Profile */
.user-profile-trigger {
    display: flex;
    align-items: center;
    padding: 0.5rem;
    border-radius: 50px;
    transition: all 0.3s ease;
}

.user-profile-trigger:hover {
    background: rgba(255, 255, 255, 0.5);
}

.user-name {
    font-weight: 600 !important;
}

.avatar {
    width: 2.5rem;
    height: 2.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    margin: 0;
    box-shadow: 0 2px 10px rgba(102, 126, 234, 0.4);
}

/* Message item hover effect */
.message-item {
    transition: all 0.2s ease;
}

.message-item:hover {
    background-color: rgba(78, 115, 223, 0.05);
    transform: translateX(5px);
}

/* Mobile Adjustments */
@media (max-width: 768px) {
    .glass-header {
        padding: 0.5rem 1rem;
    }
    
    .navbar-search-wrapper {
        display: none;
    }
    
    .mobile-search-container {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(20px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.3);
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .glass-header {
        background: rgba(30, 41, 59, 0.7);
        border-bottom-color: rgba(255, 255, 255, 0.1);
    }
    
    .time-clock {
        color: #f1f5f9;
    }
    
    .time-date {
        color: #94a3b8;
    }
    
    .input-group-glass {
        background: rgba(51, 65, 85, 0.6);
        border-color: rgba(255, 255, 255, 0.1);
    }
    
    .input-group-glass .form-control {
        color: #f1f5f9;
    }
    
    .input-group-glass .form-control::placeholder {
        color: #94a3b8;
    }
}
</style>

<script>
// Live Clock Functionality
function updateClock() {
    const now = new Date();
    
    // Time
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    document.getElementById('liveClock').textContent = `${hours}:${minutes}:${seconds}`;
    
    // Date
    const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
    document.getElementById('liveDate').textContent = now.toLocaleDateString('en-US', options);
}

// Update immediately and every second
updateClock();
setInterval(updateClock, 1000);

// Handle message click and update unread count
document.addEventListener('DOMContentLoaded', function() {
    const messageItems = document.querySelectorAll('.message-item');
    const unreadBadge = document.getElementById('unreadBadge');
    
    messageItems.forEach(item => {
        item.addEventListener('click', function(e) {
            // Get current unread count
            let currentCount = parseInt(unreadBadge.textContent) || 0;
            
            // Check if this message is unread (has bold text)
            const isUnread = this.querySelector('.font-weight-bold') !== null;
            
            if (isUnread && currentCount > 0) {
                // Decrement count
                currentCount--;
                
                if (currentCount === 0) {
                    // Hide badge if no more unread messages
                    unreadBadge.style.display = 'none';
                } else {
                    // Update badge count with animation
                    unreadBadge.textContent = currentCount > 99 ? '99+' : currentCount;
                    unreadBadge.classList.add('badge-pulse');
                    setTimeout(() => {
                        unreadBadge.classList.remove('badge-pulse');
                    }, 300);
                }
            }
        });
    });
});

// Optional: Auto-refresh unread count every 30 seconds
setInterval(function() {
    fetch('{{ route("messages.unread-count") }}', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const unreadBadge = document.getElementById('unreadBadge');
        if (data.count > 0) {
            unreadBadge.textContent = data.count > 99 ? '99+' : data.count;
            unreadBadge.style.display = '';
        } else {
            unreadBadge.style.display = 'none';
        }
    })
    .catch(error => console.error('Error fetching unread count:', error));
}, 30000); // Every 30 seconds

// Haptic feedback for header elements
document.querySelectorAll('.btn-glass, .nav-link, .dropdown-item').forEach(el => {
    el.addEventListener('click', function() {
        if (window.navigator.vibrate) {
            window.navigator.vibrate(10);
        }
    });
});
</script>