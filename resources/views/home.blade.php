{{-- HOME Dashboard Viewer - resources/views/home.blade.php --}}
@extends('layouts.admin')

@section('main-content')

<div style="padding-top: 3.5rem;">
    <div class="container-fluid">
        
        {{-- Dashboard Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                {{-- Header content commented out as in original --}}
            </div>
            
            <div class="d-flex align-items-center">
                @can('manage settings')
                <a href="{{ route('settings.index') }}#dashboard-tab" class="btn btn-sm btn-outline-primary mr-2">
                    <i class="fas fa-tools"></i> Customize Dashboard
                </a>
                @endcan
                
                <button class="btn btn-sm btn-outline-secondary" onclick="refreshDashboard()" title="Refresh Data">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>

        {{-- Success Messages --}}
        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        @if (session('status'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle"></i> {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        {{-- Dashboard Display Card --}}
        <div class="card shadow-sm border-0" style="border-radius: 10px;">
            <div class="card-body p-4">
                
                {{-- Loading State --}}
                <div id="dashboardLoading" class="text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="sr-only">Loading dashboard...</span>
                    </div>
                    <p class="text-muted">Loading dashboard widgets...</p>
                </div>
                
                {{-- GridStack Container (Read-Only Display) --}}
                <div class="grid-stack" id="homeDashboardGrid" style="min-height: 400px; display: none;">
                    {{-- Widgets will be loaded here from saved layout --}}
                </div>
                
                {{-- Empty State (shown if no widgets configured) --}}
                <div id="emptyDashboard" class="text-center py-5" style="display: none;">
                    <div class="mb-4">
                        <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">No Dashboard Configured</h4>
                        <p class="text-muted">
                            @can('manage settings')
                                Click "Customize Dashboard" above to design your perfect dashboard layout.
                            @else
                                Please contact your administrator to configure your dashboard.
                            @endcan
                        </p>
                    </div>
                    
                    @can('manage settings')
                    <a href="{{ route('settings.index') }}#dashboard-tab" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Create Dashboard
                    </a>
                    @endcan
                </div>
                
            </div>
        </div>
        
        {{-- Dashboard Info Footer --}}
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm" style="border-radius: 10px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body py-3">
                        <div class="row text-center text-white">
                            <div class="col-md-3 mb-2 mb-md-0">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="fas fa-clock mr-2"></i>
                                    <div>
                                        <small class="d-block opacity-75">Last Updated</small>
                                        <strong id="lastRefresh">{{ now()->format('g:i A') }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2 mb-md-0">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="fas fa-th mr-2"></i>
                                    <div>
                                        <small class="d-block opacity-75">Active Widgets</small>
                                        <strong id="widgetCountDisplay">0</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2 mb-md-0">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="fas fa-user-circle mr-2"></i>
                                    <div>
                                        <small class="d-block opacity-75">Profile</small>
                                        <strong>{{ $profileName ?? 'Default' }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="fas fa-sync-alt mr-2"></i>
                                    <div>
                                        <small class="d-block opacity-75">Auto-Refresh</small>
                                        <strong>
                                            <span class="badge badge-light">
                                                <i class="fas fa-check text-success"></i> Every 60s
                                            </span>
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('styles')


<style>
/* Home Dashboard Display Styles (Read-Only) */
.grid-stack {
    background: transparent;
}

.grid-stack-item-content {
    overflow: hidden;
}

/* Widget Card Styling */
.widget-card {
    background: #f0f0f0;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    height: 100%;
    display: flex;
    flex-direction: column;
    transition: transform 0.2s, box-shadow 0.2s;
    border: 2px solid rgba(0, 0, 0, 0.05);
}

.widget-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
    border-color: rgba(78, 115, 223, 0.2);
}

.widget-header {
    padding: 14px 18px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, rgba(78, 115, 223, 0.05), rgba(78, 115, 223, 0.02));
}

.widget-title {
    font-size: 14px;
    font-weight: 600;
    color: #535561;
    display: flex;
    align-items: center;
}

.widget-title i {
    font-size: 16px;
    margin-right: 8px;
}

.widget-badge {
    background: rgba(78, 115, 223, 0.1);
    color: #4e73df;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.widget-content {
    padding: 24px 18px;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
}

.widget-value {
    font-size: 36px;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 10px;
}

.widget-label {
    font-size: 13px;
    color: #858796;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Refreshing State */
.widget-refreshing {
    opacity: 0.7;
    pointer-events: none;
}

.widget-refreshing .widget-value {
    animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

/* Loading State */
#dashboardLoading {
    display: none;
}

/* Print Styles */
@media print {
    .btn, .alert, #dashboardLoading {
        display: none !important;
    }
    
    .widget-card {
        break-inside: avoid;
    }
    
    #homeDashboardGrid {
        display: block !important;
    }
}
</style>
@endpush

@push('scripts')

<script>
(function() {
    'use strict';
    
    let grid = null;
    let refreshInterval = null;
    let isDashboardInitialized = false;
    let initAttempts = 0;
    const MAX_INIT_ATTEMPTS = 3;
    
    // Widget Templates with Laravel Stats
    const widgetTemplates = {
        total_users: {
            title: 'Total Users',
            icon: 'fa-users',
            color: '#007AFF',
            value: '{{ $stats["total_users"] ?? 0 }}'
        },
        active_users: {
            title: 'Active Users',
            icon: 'fa-user-check',
            color: '#34C759',
            value: '{{ $stats["active_users"] ?? 0 }}'
        },
        total_roles: {
            title: 'Total Roles',
            icon: 'fa-shield-alt',
            color: '#5AC8FA',
            value: '{{ $stats["total_roles"] ?? 0 }}'
        },
        total_departments: {
            title: 'Departments',
            icon: 'fa-sitemap',
            color: '#FF9500',
            value: '{{ $stats["total_departments"] ?? 0 }}'
        },
        total_permissions: {
            title: 'Permissions',
            icon: 'fa-lock',
            color: '#FF3B30',
            value: '{{ $stats["total_permissions"] ?? 0 }}'
        },
        pending_requests: {
            title: 'Pending Requests',
            icon: 'fa-bell',
            color: '#FF9500',
            value: '{{ $stats["pending_requests"] ?? 0 }}'
        },
        system_health: {
            title: 'System Health',
            icon: 'fa-heartbeat',
            color: '#34C759',
            value: '{{ $stats["system_health"] ?? "N/A" }}'
        },
        storage_usage: {
            title: 'Storage Usage',
            icon: 'fa-hdd',
            color: '#FF9500',
            value: '{{ $stats["storage_usage"] ?? "N/A" }}'
        },
        last_backup: {
            title: 'Last Backup',
            icon: 'fa-database',
            color: '#5AC8FA',
            value: '{{ $stats["last_backup"] ?? "Never" }}'
        },
        recent_logins: {
            title: 'Recent Activity',
            icon: 'fa-sign-in-alt',
            color: '#5856D6',
            value: 'View Logs'
        },
        activity_log: {
            title: 'Activity Timeline',
            icon: 'fa-history',
            color: '#007AFF',
            value: 'Recent Events'
        },
        user_growth_chart: {
            title: 'User Growth',
            icon: 'fa-chart-line',
            color: '#34C759',
            value: 'Trending'
        },
        role_distribution: {
            title: 'Role Distribution',
            icon: 'fa-chart-pie',
            color: '#007AFF',
            value: 'Stats'
        },
        department_chart: {
            title: 'Department Overview',
            icon: 'fa-chart-bar',
            color: '#5AC8FA',
            value: 'Overview'
        }
    };
    
    // Saved layout from backend
    const savedLayout = @json($dashboardLayout ?? []);
    
    console.log('[HomeDashboard] Script loaded. Layout:', savedLayout.length, 'widgets');
    
    // Initialize Dashboard Display (Read-Only)
    function initDashboard() {
        if (isDashboardInitialized) {
            console.log('[HomeDashboard] Already initialized, skipping');
            return;
        }
        
        initAttempts++;
        console.log('[HomeDashboard] Initialization attempt', initAttempts);
        
        if (typeof GridStack === 'undefined') {
            console.error('[HomeDashboard] GridStack library not loaded');
            if (initAttempts < MAX_INIT_ATTEMPTS) {
                setTimeout(initDashboard, 500);
                return;
            }
            showEmptyState();
            return;
        }
        
        // Check if grid container exists and is visible
        const $container = $('#homeDashboardGrid');
        if ($container.length === 0) {
            console.error('[HomeDashboard] Grid container not found');
            if (initAttempts < MAX_INIT_ATTEMPTS) {
                setTimeout(initDashboard, 500);
                return;
            }
            return;
        }
        
        // Show loading, hide others
        $('#dashboardLoading').show();
        $container.hide();
        $('#emptyDashboard').hide();
        
        console.log('[HomeDashboard] Initializing with', savedLayout.length, 'widgets');
        
        try {
            // Destroy any existing grid first
            if (grid) {
                grid.destroy();
                grid = null;
            }
            
            // Clear the container
            $container.empty();
            
            // Small delay to ensure DOM is ready
            setTimeout(function() {
                try {
                    // Initialize GridStack in STATIC mode
                    grid = GridStack.init({
                        column: 12,
                        cellHeight: 80,
                        margin: 10,
                        float: false,
                        disableResize: true,
                        disableDrag: true,
                        staticGrid: true,
                        animate: true
                    }, '#homeDashboardGrid');
                    
                    if (savedLayout.length === 0) {
                        showEmptyState();
                    } else {
                        loadDashboard();
                        startAutoRefresh();
                    }
                    
                    isDashboardInitialized = true;
                    console.log('[HomeDashboard] Dashboard initialized successfully');
                    
                } catch (error) {
                    console.error('[HomeDashboard] GridStack init error:', error);
                    showEmptyState();
                }
            }, 100);
            
        } catch (error) {
            console.error('[HomeDashboard] Initialization error:', error);
            showEmptyState();
        }
    }
    
    // Load Saved Dashboard Layout
    function loadDashboard() {
        if (!grid) {
            console.error('[HomeDashboard] Grid not initialized');
            showEmptyState();
            return;
        }
        
        $('#dashboardLoading').hide();
        $('#emptyDashboard').hide();
        $('#homeDashboardGrid').show();
        
        // Load widgets
        savedLayout.forEach(function(widget) {
            const template = widgetTemplates[widget.widgetType];
            if (template) {
                const widgetHTML = createWidgetHTML(widget.widgetId, widget.widgetType, template);
                grid.addWidget(widgetHTML, {
                    x: widget.x,
                    y: widget.y,
                    w: widget.w,
                    h: widget.h,
                    autoPosition: false
                });
            } else {
                console.warn('[HomeDashboard] Unknown widget type:', widget.widgetType);
            }
        });
        
        // Force grid to recalculate layout
        grid.compact();
        
        updateWidgetCount();
        console.log('[HomeDashboard] Dashboard loaded with', grid.engine.nodes.length, 'widgets');
    }
    
    // Create Widget HTML
    function createWidgetHTML(widgetId, widgetType, template) {
        return `
            <div class="grid-stack-item" data-widget-type="${widgetType}" data-widget-id="${widgetId}">
                <div class="grid-stack-item-content">
                    <div class="widget-card">
                        <div class="widget-header">
                            <span class="widget-title">
                                <i class="fas ${template.icon}"></i>
                                ${template.title}
                            </span>
                        </div>
                        <div class="widget-content">
                            <div class="widget-value" style="color: ${template.color}">
                                ${template.value}
                            </div>
                            <div class="widget-label">${template.title}</div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    // Show Empty State
    function showEmptyState() {
        $('#dashboardLoading').hide();
        $('#homeDashboardGrid').hide();
        $('#emptyDashboard').show();
        $('#widgetCountDisplay').text('0');
        console.log('[HomeDashboard] Showing empty state');
    }
    
    // Update Widget Count
    function updateWidgetCount() {
        const count = grid ? grid.engine.nodes.length : 0;
        $('#widgetCountDisplay').text(count);
    }
    
    // Refresh Widget Data from Backend
    window.refreshDashboard = function() {
        console.log('[HomeDashboard] Manual refresh triggered');
        refreshWidgetData(true);
    };
    
    function refreshWidgetData(showIndicator = false) {
        if (savedLayout.length === 0) return;
        
        console.log('[HomeDashboard] Refreshing widget data...');
        
        if (showIndicator) {
            $('.widget-card').addClass('widget-refreshing');
        }
        
        $.ajax({
            url: '/dashboard/stats',
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success && response.stats) {
                    updateWidgetValues(response.stats);
                    $('#lastRefresh').text(new Date().toLocaleTimeString([], {hour: 'numeric', minute: '2-digit'}));
                    console.log('[HomeDashboard] Data refreshed successfully');
                    
                    if (showIndicator) {
                        setTimeout(function() {
                            $('.widget-card').removeClass('widget-refreshing');
                        }, 500);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('[HomeDashboard] Refresh failed:', error);
                if (showIndicator) {
                    $('.widget-card').removeClass('widget-refreshing');
                }
            }
        });
    }
    
    // Update Widget Values with Fresh Data
    function updateWidgetValues(stats) {
        $('.grid-stack-item').each(function() {
            const $widget = $(this);
            const widgetType = $widget.data('widget-type');
            const $valueEl = $widget.find('.widget-value');
            
            if (stats[widgetType] !== undefined) {
                // Animate value change
                $valueEl.fadeOut(200, function() {
                    $(this).text(stats[widgetType]).fadeIn(200);
                });
            }
        });
    }
    
    // Start Auto-Refresh (60 seconds)
    function startAutoRefresh() {
        // Clear any existing interval first
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
        
        refreshInterval = setInterval(function() {
            refreshWidgetData(false);
        }, 60000); // 60 seconds
        
        console.log('[HomeDashboard] Auto-refresh enabled (60s interval)');
    }
    
    // Cleanup function for SPA navigation
    function destroyDashboard() {
        console.log('[HomeDashboard] Destroying dashboard...');
        
        if (refreshInterval) {
            clearInterval(refreshInterval);
            refreshInterval = null;
        }
        
        if (grid) {
            try {
                grid.destroy();
            } catch (e) {
                console.warn('[HomeDashboard] Error destroying grid:', e);
            }
            grid = null;
        }
        
        $('#homeDashboardGrid').empty();
        isDashboardInitialized = false;
        initAttempts = 0;
        
        console.log('[HomeDashboard] Dashboard destroyed');
    }
    
    // Expose functions globally for SPA
    window.initDashboard = initDashboard;
    window.destroyDashboard = destroyDashboard;
    
    // Listen for SPA navigation events
    $(document).on('spa:before-navigate', function() {
        console.log('[HomeDashboard] spa:before-navigate event triggered');
        destroyDashboard();
    });
    
    // Also cleanup on page unload
    $(window).on('beforeunload', function() {
        console.log('[HomeDashboard] Page unload, cleaning up');
        destroyDashboard();
    });
    
    // Initialize on DOM Ready with retry logic
    $(document).ready(function() {
        console.log('[HomeDashboard] DOM ready');
        // Small delay to ensure everything is rendered
        setTimeout(initDashboard, 100);
    });
    
    // Also try on window load as fallback
    $(window).on('load', function() {
        if (!isDashboardInitialized) {
            console.log('[HomeDashboard] Window loaded, retrying init...');
            initDashboard();
        }
    });
    
})();
</script>
@endpush