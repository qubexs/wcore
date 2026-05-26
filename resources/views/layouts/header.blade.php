<!-- resources/views/layouts/header.blade.php -->
<nav class="navbar navbar-expand navbar-light topbar glass-header">
    
    <!-- Left: Logo + App Menu -->
    <div class="navbar-brand-left d-flex align-items-center">
        <a class="navbar-brand macos-logo" href="{{ route('home') }}" title="{{ $settings['site_name'] ?? 'wCore' }}">
            @if(!empty($settings['site_logo']))
                <img src="{{ asset('storage/' . $settings['site_logo']) }}" alt="Logo" style="height:32px;width:auto;">
            @else
                <svg width="20" height="20" viewBox="0 0 32 32" class="logo-icon">
                    <defs>
                        <filter id="grayscale">
                            <feColorMatrix type="matrix" values="0.3333 0.3333 0.3333 0 0 0.3333 0.3333 0.3333 0 0 0.3333 0.3333 0.3333 0 0 0 0 0 1 0"/>
                        </filter>
                    </defs>
                    <image href="{{ asset('img/logome.png') }}" width="32" height="32" filter="url(#grayscale)" class="logo-image"/>
                </svg>
            @endif
        </a>
        
        <ul class="navbar-nav macos-main-menu d-none d-md-flex">
            {{-- Hidden menu items (Finder, File, Edit, View, Go, Window) - can enable later if needed --}}
            {{-- 
            <li class="nav-item active"><a class="nav-link" href="{{ route('home') }}">Finder</a></li>
            <li class="nav-item"><a class="nav-link" href="#">File</a></li>
            <li class="nav-item"><a class="nav-link" href="#">Edit</a></li>
            <li class="nav-item"><a class="nav-link" href="#">View</a></li>
            <li class="nav-item"><a class="nav-link" href="#">Go</a></li>
            <li class="nav-item"><a class="nav-link" href="#">Window</a></li>
            --}}
            <li class="nav-item dropdown">
                <a class="nav-link" href="#" id="helpDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Help</a>
                <div class="dropdown-menu macos-dropdown-menu dropdown-menu-left shadow animated--grow-in" aria-labelledby="helpDropdown" style="min-width: 220px;">
                    <a class="dropdown-item" href="#">Search commands</a>
                    <a class="dropdown-item" href="#">System documentation</a>
                    <a class="dropdown-item" href="#">Keyboard shortcuts</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#">Report bug</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('about') }}">About <span class="site-name">{{ $settings['site_name'] ?? 'wCore' }}</span></a>
                </div>
            </li>
        </ul>
    </div>

    <!-- Center: Time -->
    <div class="navbar-brand-center d-none d-lg-flex align-items-center">
        <div class="macos-time">
            <span class="time-clock" id="liveClock">00:00</span>
            <span class="time-date" id="liveDate">Loading...</span>
        </div>
    </div>

    <!-- Right: Status Icons + User Controls -->
    <ul class="navbar-nav ml-auto align-items-center">
        
        <!-- Wi-Fi -->
        <li class="nav-item macos-status-icon">
            <a class="nav-link" href="#" title="Wi-Fi">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M1 9l2 2c4.97-4.97 13.03-4.97 18 0l2-2C16.93 2.93 7.08 2.93 1 9zm8 8l3 3 3-3c-1.65-1.66-4.34-1.66-6 0zm-4-4l2 2c2.76-2.76 7.24-2.76 10 0l2-2C15.14 9.14 8.87 9.14 5 13z"/>
                </svg>
            </a>
        </li>
        
        <!-- Bluetooth -->
        <li class="nav-item macos-status-icon">
            <a class="nav-link" href="#" title="Bluetooth">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.71 7.71L12 2h-1v7.59L6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 11 14.41V22h1l5.71-5.71-4.3-4.29 4.3-4.29zM13 5.83l1.88 1.88L13 9.59V5.83zm1.88 10.46L13 18.17v-3.76l1.88 1.88z"/>
                </svg>
            </a>
        </li>
        
        <!-- Battery -->
        <li class="nav-item macos-status-icon">
            <a class="nav-link" href="#" title="Battery">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M15.67 4H14V2h-4v2H8.33C7.6 4 7 4.6 7 5.33v15.33C7 21.4 7.6 22 8.33 22h7.33c.74 0 1.34-.6 1.34-1.33V5.33C17 4.6 16.4 4 15.67 4z"/>
                </svg>
            </a>
        </li>
        
        <!-- Search -->
        <li class="nav-item macos-status-icon">
            <a class="nav-link" href="#" title="Search">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                </svg>
            </a>
        </li>

        <!-- Control Center -->
        <li class="nav-item macos-status-icon">
            <a class="nav-link" href="#" title="Control Center">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M6 6c0-1.1.9-2 2-2s2 .9 2 2-.9 2-2 2-2-.9-2-2zm0 8c0-1.1.9-2 2-2s2 .9 2 2-.9 2-2 2-2-.9-2-2zm0 8c0-1.1.9-2 2-2s2 .9 2 2-.9 2-2 2-2-.9-2-2zm8-16c0-1.1.9-2 2-2s2 .9 2 2-.9 2-2 2-2-.9-2-2zm0 8c0-1.1.9-2 2-2s2 .9 2 2-.9 2-2 2-2-.9-2-2zm0 8c0-1.1.9-2 2-2s2 .9 2 2-.9 2-2 2-2-.9-2-2z"/>
                </svg>
            </a>
        </li>
        
        <div class="macos-divider"></div>
        
        <!-- Notifications -->
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bell fa-fw"></i>
                <span class="badge badge-counter glass-badge">3+</span>
            </a>
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
                <h6 class="dropdown-header">Alerts Center</h6>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="mr-3"><div class="icon-circle bg-primary"><i class="fas fa-file-alt text-white"></i></div></div>
                    <div><div class="small text-gray-500">December 12, 2019</div><span class="font-weight-bold">New monthly report ready!</span></div>
                </a>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="mr-3"><div class="icon-circle bg-success"><i class="fas fa-donate text-white"></i></div></div>
                    <div><div class="small text-gray-500">December 7, 2019</div>$290.29 deposited!</div>
                </a>
                <a class="dropdown-item text-center small text-gray-500" href="#">Show All Alerts</a>
            </div>
        </li>
        
        <!-- Messages -->
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-envelope fa-fw"></i>
                <span class="badge badge-counter glass-badge" id="unreadBadge" style="{{ isset($unreadCount) && $unreadCount > 0 ? '' : 'display: none;' }}">
                    {{ isset($unreadCount) && $unreadCount > 0 ? ($unreadCount > 99 ? '99+' : $unreadCount) : '' }}
                </span>
            </a>
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="messagesDropdown">
                <h6 class="dropdown-header">Message Center</h6>
                @if(isset($recentMessages) && $recentMessages->count() > 0)
                    @foreach($recentMessages as $message)
                        <a class="dropdown-item d-flex align-items-center message-item" href="{{ route('messages.show', $message->conversation_id) }}">
                            <div class="dropdown-list-image mr-3">
                                <img class="rounded-circle" src="{{ $message->sender->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($message->sender->name).'&background=random' }}" alt="{{ $message->sender->name }}">
                                <div class="status-indicator {{ $message->sender->is_online ? 'bg-success' : 'bg-secondary' }}"></div>
                            </div>
                            <div class="{{ $message->read_at ? '' : 'font-weight-bold' }}">
                                <div class="text-truncate">{{ Str::limit($message->content, 50) }}</div>
                                <div class="small text-gray-500">{{ $message->sender->name }} · {{ $message->created_at->diffForHumans() }}</div>
                            </div>
                        </a>
                    @endforeach
                @else
                    <a class="dropdown-item text-center small text-gray-500" href="#">No new messages</a>
                @endif
                <a class="dropdown-item text-center small text-gray-500" href="{{ route('messages.index') }}">Read More Messages</a>
            </div>
        </li>
        
        <div class="topbar-divider d-none d-sm-block"></div>

        <!-- User Profile -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle user-profile-trigger" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small user-name">{{ auth()->user()?->name ?? 'Guest' }}</span>
                <figure class="img-profile rounded-circle avatar font-weight-bold" data-initial="{{ auth()->user()?->name[0] ?? 'G' }}">
                    {{ auth()->user()?->name[0] ?? 'G' }}
                </figure>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="{{ route('profile') }}"><i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>Profile</a>
                <a class="dropdown-item" href="javascript:void(0)"><i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>Settings</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal"><i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>Logout</a>
            </div>
        </li>
    </ul>
</nav>

<!-- Mobile menu button - hidden since main menu is hidden -->
<button class="macos-mobile-menu-btn d-md-none" onclick="document.querySelector('.macos-main-menu').classList.toggle('show')" style="display: none;">
    <i class="fas fa-bars"></i>
</button>

<style>
/* macOS Glass Header Styles */
.glass-header {
    background: rgba(255, 255, 255, 0.85) !important;
    backdrop-filter: blur(20px) !important;
    -webkit-backdrop-filter: blur(20px) !important;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1) !important;
    padding: 0 1rem !important;
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    z-index: 9999 !important;
    height: 40px !important;
    min-height: 40px !important;
    display: flex !important;
    align-items: center !important;
}

.navbar-brand-left {
    display: flex;
    align-items: center;
}

/* Logo Styles */
.macos-logo {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    transition: background 0.15s ease;
    margin-right: 0.25rem;
    padding: 4px;
}

.macos-logo:hover {
    background: rgba(0, 0, 0, 0.1);
}

.macos-logo .logo-icon {
    width: 24px;
    height: 24px;
}

.macos-logo .logo-image {
    transition: filter 0.2s ease;
}

.macos-logo:hover .logo-image {
    filter: none;
}

.macos-main-menu {
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 0.25rem;
}

.macos-main-menu .nav-link {
    padding: 4px 10px;
    font-size: 13px;
    font-weight: 500;
    color: #1d1d1f;
    border-radius: 4px;
    transition: background 0.15s ease;
}

.macos-main-menu .nav-link:hover {
    background: rgba(0, 0, 0, 0.1);
}

.navbar-brand-center {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
}

.macos-time {
    text-align: center;
    color: #1d1d1f;
}

.macos-time .time-clock {
    font-size: 14px;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    letter-spacing: 0.5px;
}

.macos-time .time-date {
    font-size: 12px;
    font-weight: 400;
    opacity: 0.8;
}

.macos-status-icon {
    display: flex;
    align-items: center;
}

.macos-status-icon .nav-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border-radius: 4px;
    color: #1d1d1f;
    transition: background 0.15s ease;
    padding: 0;
}

.macos-status-icon .nav-link:hover {
    background: rgba(0, 0, 0, 0.1);
}

.macos-divider {
    width: 1px;
    height: 16px;
    background: rgba(0, 0, 0, 0.2);
    margin: 0 4px;
}

.macos-mobile-menu-btn {
    position: fixed;
    top: 8px;
    left: 8px;
    z-index: 9999;
    background: rgba(255, 255, 255, 0.8);
    border: none;
    border-radius: 6px;
    padding: 8px 12px;
    font-size: 18px;
    display: none;
}

/* Glass Badge */
.glass-badge {
    background: rgba(239, 68, 68, 0.9) !important;
    backdrop-filter: blur(4px);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

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
    .macos-mobile-menu-btn {
        display: block;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .glass-header {
        background: rgba(30, 30, 30, 0.85) !important;
        border-bottom-color: rgba(255, 255, 255, 0.1);
    }
    
    .macos-logo:hover {
        background: rgba(255, 255, 255, 0.15);
    }
    
    .macos-main-menu .nav-link,
    .macos-time,
    .macos-status-icon .nav-link {
        color: #f5f5f7;
    }
    
    .macos-main-menu .nav-link:hover,
    .macos-status-icon .nav-link:hover {
        background: rgba(255, 255, 255, 0.15);
    }
    
    .macos-divider {
        background: rgba(255, 255, 255, 0.2);
    }
}

/* Body padding for fixed header */
body {
    padding-top: 40px;
}

/* macOS Glass Dropdown Menu */
.macos-dropdown-menu {
    min-width: 16rem;
    width: max-content;
    padding: 0.5rem;
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(25px);
    -webkit-backdrop-filter: blur(25px);
    border-radius: 0.5rem;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3), 0 0 0 0.5px rgba(0, 0, 0, 0.1);
    border: none;
    margin-top: -12px;
}

.macos-dropdown-menu .dropdown-item {
    padding: 0.4rem 0.6rem;
    margin: 0.1rem;
    font-size: 0.9rem;
    letter-spacing: 0.3px;
    border-radius: 0.3rem;
    color: #1d1d1f;
    transition: none;
}

.macos-dropdown-menu .dropdown-item:hover,
.macos-dropdown-menu .dropdown-item:focus {
    background: #007AFF;
    color: white;
    font-weight: 500;
}

.macos-dropdown-menu .dropdown-item .site-name {
    font-weight: 600;
}

.macos-dropdown-menu .dropdown-divider {
    margin: 4px 0;
    background-color: rgba(0, 0, 0, 0.15);
}

/* Dark mode dropdown */
@media (prefers-color-scheme: dark) {
    .macos-dropdown-menu {
        background: rgba(40, 40, 40, 0.9);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5), 0 0 0 0.5px rgba(255, 255, 255, 0.1);
    }
    
    .macos-dropdown-menu .dropdown-item {
        color: #f5f5f7;
    }
    
    .macos-dropdown-menu .dropdown-divider {
        background-color: rgba(255, 255, 255, 0.2);
    }
}
</style>

<script>
// Live Clock Functionality
function updateClock() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    document.getElementById('liveClock').textContent = `${hours}:${minutes}`;
    
    const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
    document.getElementById('liveDate').textContent = now.toLocaleDateString('en-US', options);
}

updateClock();
setInterval(updateClock, 1000);

// Handle message click and update unread count
document.addEventListener('DOMContentLoaded', function() {
    const messageItems = document.querySelectorAll('.message-item');
    const unreadBadge = document.getElementById('unreadBadge');
    
    messageItems.forEach(item => {
        item.addEventListener('click', function(e) {
            let currentCount = parseInt(unreadBadge.textContent) || 0;
            const isUnread = this.querySelector('.font-weight-bold') !== null;
            
            if (isUnread && currentCount > 0) {
                currentCount--;
                if (currentCount === 0) {
                    unreadBadge.style.display = 'none';
                } else {
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

// Auto-refresh unread count every 30 seconds
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
}, 30000);

// Haptic feedback for header elements
document.querySelectorAll('.btn-glass, .nav-link, .dropdown-item').forEach(el => {
    el.addEventListener('click', function() {
        if (window.navigator.vibrate) {
            window.navigator.vibrate(10);
        }
    });
});
</script>
