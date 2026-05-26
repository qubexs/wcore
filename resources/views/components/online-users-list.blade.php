{{-- ═══════════════════════════════════════════════════════════════════════════
     COMPONENT 3: Online Users List
     Usage: <x-online-users-list :limit="10" />
     ═════════════════════════════════════════════════════════════════════════ --}}

{{-- File: resources/views/components/online-users-list.blade.php --}}

@props(['limit' => 10])

<div class="online-users-list">
    <div class="card shadow-sm">
        <div class="card-header py-3 bg-light d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-circle text-success mr-2"></i>Online Users
            </h6>
            <span class="badge badge-success" id="onlineCount">0</span>
        </div>
        
        <div class="card-body p-0">
            <div id="onlineUsersList" class="list-group list-group-flush">
                <div class="list-group-item p-3 text-center text-muted">
                    <small>Loading...</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const LIMIT = {{ $limit }};
    
    function loadOnlineUsers() {
        if (window.OnlineStatus) {
            window.OnlineStatus.getOnlineUsers(LIMIT)
                .then(users => {
                    const list = document.getElementById('onlineUsersList');
                    const count = document.getElementById('onlineCount');
                    
                    if (users.length === 0) {
                        list.innerHTML = `
                            <div class="list-group-item p-3 text-center text-muted">
                                <small>No users online</small>
                            </div>
                        `;
                    } else {
                        list.innerHTML = users.map(user => `
                            <div class="list-group-item p-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm mr-3">
                                        <img src="${user.avatar || 'https://via.placeholder.com/40'}" 
                                             alt="${user.name}" 
                                             class="rounded-circle">
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">${user.name}</h6>
                                        <small class="text-muted">${user.formatted_status}</small>
                                    </div>
                                    <div>
                                        <i class="fas ${user.device_icon || 'fa-circle'}" 
                                           style="color: ${getStatusColor(user.presence_status)}"></i>
                                    </div>
                                </div>
                            </div>
                        `).join('');
                    }
                    
                    count.textContent = users.length;
                })
                .catch(error => {
                    console.error('Error loading online users:', error);
                    document.getElementById('onlineUsersList').innerHTML = `
                        <div class="list-group-item p-3 text-center text-danger">
                            <small>Error loading users</small>
                        </div>
                    `;
                });
        }
    }
    
    function getStatusColor(status) {
        return {
            'online': '#00b894',
            'away': '#fdcb6e',
            'busy': '#e17055',
            'offline': '#b2bec3'
        }[status] || '#b2bec3';
    }
    
    // Load on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadOnlineUsers);
    } else {
        loadOnlineUsers();
    }
    
    // Refresh every 30 seconds
    setInterval(loadOnlineUsers, 30000);
})();
</script>