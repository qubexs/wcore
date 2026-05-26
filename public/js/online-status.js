/**
 * JAVASCRIPT - Real-time User Online Status Tracking
 * 
 * File: public/js/online-status.js
 * 
 * Add to your main layout:
 * <script src="{{ asset('js/online-status.js') }}"></script>
 * 
 * Include in Blade:
 * @push('scripts')
 *   <script src="{{ asset('js/online-status.js') }}"></script>
 * @endpush
 */

(function() {
    'use strict';

    const OnlineStatus = {
        // Configuration
        config: {
            pingInterval: 30000,        // Ping every 30 seconds
            inactivityTimeout: 300000,  // Mark away after 5 minutes
            busyTimeout: 900000,        // Mark offline after 15 minutes
            apiBase: '/api/user',
        },

        // State
        state: {
            isOnline: true,
            currentStatus: 'online',
            inactivityTimer: null,
            pingTimer: null,
            lastActivity: Date.now(),
        },

        /**
         * Initialize online status tracking
         */
        init: function() {
            console.log('[OnlineStatus] Initializing...');

            if (!document.querySelector('meta[name="csrf-token"]')) {
                console.warn('[OnlineStatus] CSRF token not found');
                return;
            }

            // Start ping timer
            this.startPing();

            // Track user activity
            this.trackActivity();

            // Handle page visibility changes
            this.handleVisibilityChange();

            // Handle before unload (logout)
            this.handleBeforeUnload();

            console.log('[OnlineStatus] Initialized');
        },

        /**
         * Start periodic ping to keep user online
         */
        startPing: function() {
            this.state.pingTimer = setInterval(() => {
                this.ping();
            }, this.config.pingInterval);
        },

        /**
         * Stop periodic ping
         */
        stopPing: function() {
            if (this.state.pingTimer) {
                clearInterval(this.state.pingTimer);
                this.state.pingTimer = null;
            }
        },

        /**
         * Send ping to server
         */
        ping: function() {
            fetch(`${this.config.apiBase}/ping`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('[OnlineStatus] Ping successful');
                }
            })
            .catch(error => console.error('[OnlineStatus] Ping error:', error));
        },

        /**
         * Track user activity on page
         */
        trackActivity: function() {
            const events = ['mousedown', 'keydown', 'scroll', 'touchstart', 'click'];

            events.forEach(event => {
                document.addEventListener(event, () => {
                    this.state.lastActivity = Date.now();

                    // Reset inactivity timer
                    this.resetInactivityTimer();

                    // Mark as online if away/busy
                    if (this.state.currentStatus !== 'online') {
                        this.setStatus('online');
                    }
                }, true);
            });

            console.log('[OnlineStatus] Activity tracking enabled');
        },

        /**
         * Reset inactivity timer
         */
        resetInactivityTimer: function() {
            if (this.state.inactivityTimer) {
                clearTimeout(this.state.inactivityTimer);
            }

            // Mark as away after 5 minutes of inactivity
            this.state.inactivityTimer = setTimeout(() => {
                if (this.state.currentStatus === 'online') {
                    console.log('[OnlineStatus] Marking user as away (5 min inactivity)');
                    this.setStatus('away');
                }
            }, this.config.inactivityTimeout);

            // Mark as offline after 15 minutes of inactivity
            setTimeout(() => {
                if (this.state.currentStatus !== 'offline') {
                    console.log('[OnlineStatus] Marking user as offline (15 min inactivity)');
                    this.setStatus('offline');
                }
            }, this.config.busyTimeout);
        },

        /**
         * Set user status (online, away, busy, offline)
         */
        setStatus: function(status) {
            if (!['online', 'away', 'busy', 'offline'].includes(status)) {
                console.error('[OnlineStatus] Invalid status:', status);
                return;
            }

            if (this.state.currentStatus === status) {
                return;
            }

            fetch(`${this.config.apiBase}/presence`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ status: status }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.state.currentStatus = status;
                    this.updateUI(data.data);
                    console.log('[OnlineStatus] Status updated to:', status);

                    // Broadcast status change
                    this.broadcastStatusChange(status);
                }
            })
            .catch(error => console.error('[OnlineStatus] Error setting status:', error));
        },

        /**
         * Get current status
         */
        getStatus: function() {
            fetch(`${this.config.apiBase}/status`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.updateUI(data.data);
                }
            })
            .catch(error => console.error('[OnlineStatus] Error getting status:', error));
        },

        /**
         * Update UI status indicators
         */
        updateUI: function(data) {
            // Update status badge
            const statusBadges = document.querySelectorAll('[data-online-status]');
            statusBadges.forEach(badge => {
                badge.innerHTML = `
                    <i class="fas ${data.presence_icon} mr-2"></i>
                    ${data.presence_status}
                `;
                badge.className = `badge badge-${data.presence_color}`;
            });

            // Update status in navbar
            const navStatus = document.querySelector('[data-navbar-status]');
            if (navStatus) {
                navStatus.innerHTML = `
                    <i class="fas ${data.presence_icon}"></i>
                `;
            }

            // Update formatted status
            const formattedStatus = document.querySelector('[data-formatted-status]');
            if (formattedStatus) {
                formattedStatus.textContent = data.formatted_status;
            }
        },

        /**
         * Handle page visibility change (tab focus)
         */
        handleVisibilityChange: function() {
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    console.log('[OnlineStatus] Page hidden, pausing ping');
                    this.stopPing();
                } else {
                    console.log('[OnlineStatus] Page visible, resuming ping');
                    this.startPing();
                    this.ping(); // Immediate ping when visible
                }
            });
        },

        /**
         * Handle before unload (logout)
         */
        handleBeforeUnload: function() {
            window.addEventListener('beforeunload', () => {
                // Try to mark as offline (may not complete)
                fetch(`${this.config.apiBase}/offline`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    keepalive: true, // Important for beforeunload
                })
                .catch(error => console.warn('[OnlineStatus] Offline error:', error));
            });
        },

        /**
         * Broadcast status change to other pages/tabs
         */
        broadcastStatusChange: function(status) {
            if ('BroadcastChannel' in window) {
                const channel = new BroadcastChannel('user_status');
                channel.postMessage({
                    event: 'status_changed',
                    status: status,
                    timestamp: new Date(),
                });
                channel.close();
            }
        },

        /**
         * Get online users
         */
        getOnlineUsers: function(limit = 10) {
            return fetch(`/api/users/online?limit=${limit}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        return data.data;
                    }
                    throw new Error('Failed to fetch online users');
                });
        },

        /**
         * Get offline users
         */
        getOfflineUsers: function(limit = 10) {
            return fetch(`/api/users/offline?limit=${limit}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        return data.data;
                    }
                    throw new Error('Failed to fetch offline users');
                });
        },

        /**
         * Get online/offline statistics
         */
        getStats: function() {
            return fetch('/api/users/stats')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        return data.stats;
                    }
                    throw new Error('Failed to fetch stats');
                });
        },

        /**
         * Check if specific user is online
         */
        checkUserStatus: function(userId) {
            return fetch(`/api/users/${userId}/status`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        return data.data;
                    }
                    throw new Error('Failed to check user status');
                });
        },
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => OnlineStatus.init());
    } else {
        OnlineStatus.init();
    }

    // Expose globally for manual access
    window.OnlineStatus = OnlineStatus;

})();