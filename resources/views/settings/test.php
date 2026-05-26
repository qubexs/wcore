@extends('layouts.admin')

{{-- resources/views/settings/index.blade.php --}}

@section('main-content')
<div class="container-fluid mt-4">

    <style>

.modal {
    z-index: 99999 !important;
}

.modal-backdrop {
    z-index: 99990 !important;
}

body.modal-open {
    overflow: hidden !important;
}

    </style>

    
<div class="mb-4 macos-header">
    <div class="d-flex align-items-center gap-3">
        <div class="macos-icon">
            <i class="fas fa-cog"></i>
        </div>
        <div>
            <h1 class="macos-title">Settings</h1>
            <div class="macos-subtitle">Site Management</div>
        </div>
    </div>
</div>



    {{-- ── Flash Messages ────────────────────────────────────── --}}
    @if(session('success'))
        <div class="alert alert-ios alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-ios alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif


    {{--v<link href="{{ asset('css/uiglass.min.css') }}" rel="stylesheet"> --}}
    
    {{-- ══════════════════════════════════════════════════════════
         GLASS TABS  — class="glass-tab" + data-tab="id" matches JS
         ══════════════════════════════════════════════════════════ --}}
    <div class="glass-tabs" id="settingsTab" role="tablist">

        <button class="glass-tab active" data-tab="general" role="tab" type="button">
            <i class="fas fa-cog me-1"></i> General
        </button>

        <button class="glass-tab" data-tab="dashboard" role="tab" type="button">
            <i class="fas fa-tachometer-alt me-1"></i> Dashboard
        </button>

        <button class="glass-tab" data-tab="database" role="tab" type="button">
            <i class="fas fa-database me-1"></i> Database
        </button>

        <button class="glass-tab" data-tab="backup" role="tab" type="button">
            <i class="fas fa-archive me-1"></i> Website Backup
        </button>

        <button class="glass-tab" data-tab="update" role="tab" type="button">
            <i class="fas fa-sync me-1"></i> Update
        </button>

    </div>

    {{-- ══════════════════════════════════════════════════════════
         TAB CONTENT PANES
         ══════════════════════════════════════════════════════════ --}}
    <div class="tab-content mt-3" id="settingsTabContent">

        {{-- ════════════════════════════════════════════
             1. GENERAL TAB
             ════════════════════════════════════════════ --}}
        <div class="tab-pane fade show active" id="general" role="tabpanel">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-cog me-2"></i> General Settings
                    </h5>
                </div>
                <div class="card-body">

                    <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- Website Name --}}
                        <div class="row mb-3 align-items-center">
                            <label class="col-md-3 col-form-label fw-bold">Website Name</label>
                            <div class="col-md-9">
                                <input type="text"
                                       name="settings[site_name]"
                                       class="form-control @error('settings.site_name') is-invalid @enderror"
                                       value="{{ $settings['site_name'] ?? '' }}"
                                       placeholder="My Awesome Site">
                            </div>
                        </div>

                        {{-- Website Email --}}
                        <div class="row mb-3 align-items-center">
                            <label class="col-md-3 col-form-label fw-bold">Website Email</label>
                            <div class="col-md-9">
                                <input type="email"
                                       name="settings[site_email]"
                                       class="form-control @error('settings.site_email') is-invalid @enderror"
                                       value="{{ $settings['site_email'] ?? '' }}"
                                       placeholder="hello@example.com">
                            </div>
                        </div>

                        {{-- Support Email --}}
                        <div class="row mb-3 align-items-center">
                            <label class="col-md-3 col-form-label fw-bold">Support Email</label>
                            <div class="col-md-9">
                                <input type="email"
                                       name="settings[support_email]"
                                       class="form-control @error('settings.support_email') is-invalid @enderror"
                                       value="{{ $settings['support_email'] ?? '' }}"
                                       placeholder="support@example.com">
                            </div>
                        </div>

                        {{-- Phone --}}
                        <div class="row mb-3 align-items-center">
                            <label class="col-md-3 col-form-label fw-bold">Phone Number</label>
                            <div class="col-md-9">
                                <input type="text"
                                       name="settings[site_phone]"
                                       class="form-control"
                                       value="{{ $settings['site_phone'] ?? '' }}"
                                       placeholder="+60 12 345 6789">
                            </div>
                        </div>

                        {{-- Address --}}
                        <div class="row mb-3 align-items-center">
                            <label class="col-md-3 col-form-label fw-bold">Address</label>
                            <div class="col-md-9">
                                <textarea name="settings[site_address]"
                                          class="form-control"
                                          rows="2"
                                          placeholder="No. 1, Jalan Example…">{{ $settings['site_address'] ?? '' }}</textarea>
                            </div>
                        </div>

                        {{-- Logo --}}
                        <div class="row mb-3 align-items-center">
                            <label class="col-md-3 col-form-label fw-bold">Website Logo</label>
                            <div class="col-md-9">
                                <input type="file" name="settings[site_logo]"
                                       class="form-control" accept="image/*">
                                @if(!empty($settings['site_logo']))
                                    <div class="mt-2 d-flex align-items-center gap-2">
                                        <img src="{{ asset('storage/' . $settings['site_logo']) }}"
                                             height="50" class="rounded border" alt="Logo">
                                        <small class="text-muted">Current logo</small>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Favicon --}}
                        <div class="row mb-3 align-items-center">
                            <label class="col-md-3 col-form-label fw-bold">Favicon</label>
                            <div class="col-md-9">
                                <input type="file" name="settings[site_favicon]"
                                       class="form-control" accept="image/*">
                                @if(!empty($settings['site_favicon']))
                                    <div class="mt-2 d-flex align-items-center gap-2">
                                        <img src="{{ asset('storage/' . $settings['site_favicon']) }}"
                                             height="32" class="rounded border" alt="Favicon">
                                        <small class="text-muted">Current favicon</small>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Meta Title --}}
                        <div class="row mb-3 align-items-center">
                            <label class="col-md-3 col-form-label fw-bold">Meta Title</label>
                            <div class="col-md-9">
                                <input type="text"
                                       name="settings[meta_title]"
                                       class="form-control"
                                       value="{{ $settings['meta_title'] ?? '' }}"
                                       placeholder="My Site — Best in Town">
                            </div>
                        </div>

                        {{-- Meta Description --}}
                        <div class="row mb-3 align-items-center">
                            <label class="col-md-3 col-form-label fw-bold">Meta Description</label>
                            <div class="col-md-9">
                                <textarea name="settings[meta_description]"
                                          class="form-control" rows="3"
                                          placeholder="Short description shown in search engines…">{{ $settings['meta_description'] ?? '' }}</textarea>
                            </div>
                        </div>

                        {{-- Enable Registration --}}
                        <div class="row mb-3 align-items-center">
                            <label class="col-md-3 col-form-label fw-bold">Enable Registration</label>
                            <div class="col-md-9">
                                {{-- Hidden sends 0 when checkbox is unchecked --}}
                                <input type="hidden" name="settings[enable_registration]" value="0">
                                <div class="form-check form-switch">
                                    <input type="checkbox"
                                           name="settings[enable_registration]"
                                           value="1"
                                           class="form-check-input"
                                           id="chk_enable_reg"
                                           {{ ($settings['enable_registration'] ?? 0) == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label text-muted" for="chk_enable_reg">
                                        Allow new users to register
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Maintenance Mode --}}
                        <div class="row mb-4 align-items-center">
                            <label class="col-md-3 col-form-label fw-bold">Maintenance Mode</label>
                            <div class="col-md-9">
                                {{-- Hidden sends 0 when checkbox is unchecked --}}
                                <input type="hidden" name="settings[maintenance_mode]" value="0">
                                <div class="form-check form-switch">
                                    <input type="checkbox"
                                           name="settings[maintenance_mode]"
                                           value="1"
                                           class="form-check-input"
                                           id="chk_maintenance"
                                           {{ ($settings['maintenance_mode'] ?? 0) == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label text-muted" for="chk_maintenance">
                                        Put site into maintenance mode
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr style="border-color:rgba(255,255,255,0.1);">

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> Save Settings
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>{{-- /general --}}

{{-- ════════════════════════════════════════════
     DASHBOARD TAB — Customizable Dashboard Builder
════════════════════════════════════════════ --}}
<div class="tab-pane fade" id="dashboard" role="tabpanel">
    
    {{-- Dashboard Builder Header --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-success text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard Builder
                </h5>
                <div class="btn-group">
                    <button type="button" class="btn btn-light btn-sm" id="dashboardTemplateBtn">
                        <i class="fas fa-th-large"></i> Templates
                    </button>
                    <button type="button" class="btn btn-light btn-sm" id="dashboardSaveBtn">
                        <i class="fas fa-save"></i> Save Layout
                    </button>
                    <button type="button" class="btn btn-light btn-sm" id="dashboardResetBtn">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="alert alert-info m-3 mb-0">
                <i class="fas fa-info-circle"></i>
                <strong>Drag & Drop to Customize:</strong> Drag widgets from the library below to build your dashboard. 
                Resize and rearrange as needed. Your layout will be saved automatically.
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Left Sidebar: Widget Library --}}
        <div class="col-md-3">
            <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-cubes"></i> Widget Library
                    </h6>
                </div>
                <div class="card-body p-2" style="max-height: 70vh; overflow-y: auto;">
                    
                    {{-- Widget Categories --}}
                    <div class="accordion accordion-flush" id="widgetAccordion">
                        
                        {{-- Statistics Widgets --}}
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#statsWidgets">
                                    <i class="fas fa-chart-line me-2"></i> Statistics
                                </button>
                            </h2>
                            <div id="statsWidgets" class="accordion-collapse collapse show" data-bs-parent="#widgetAccordion">
                                <div class="accordion-body p-2">
                                    <div class="widget-library-item" draggable="true" data-widget-type="total_users">
                                        <i class="fas fa-users text-primary"></i>
                                        <span>Total Users</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="active_users">
                                        <i class="fas fa-user-check text-success"></i>
                                        <span>Active Users</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="total_roles">
                                        <i class="fas fa-shield-alt text-info"></i>
                                        <span>Total Roles</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="total_departments">
                                        <i class="fas fa-sitemap text-warning"></i>
                                        <span>Departments</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="total_permissions">
                                        <i class="fas fa-lock text-danger"></i>
                                        <span>Permissions</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Activity Widgets --}}
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#activityWidgets">
                                    <i class="fas fa-clock me-2"></i> Activity
                                </button>
                            </h2>
                            <div id="activityWidgets" class="accordion-collapse collapse" data-bs-parent="#widgetAccordion">
                                <div class="accordion-body p-2">
                                    <div class="widget-library-item" draggable="true" data-widget-type="recent_logins">
                                        <i class="fas fa-sign-in-alt text-primary"></i>
                                        <span>Recent Logins</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="activity_log">
                                        <i class="fas fa-history text-info"></i>
                                        <span>Activity Log</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="pending_requests">
                                        <i class="fas fa-bell text-warning"></i>
                                        <span>Pending Requests</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Chart Widgets --}}
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#chartWidgets">
                                    <i class="fas fa-chart-bar me-2"></i> Charts
                                </button>
                            </h2>
                            <div id="chartWidgets" class="accordion-collapse collapse" data-bs-parent="#widgetAccordion">
                                <div class="accordion-body p-2">
                                    <div class="widget-library-item" draggable="true" data-widget-type="user_growth_chart">
                                        <i class="fas fa-chart-line text-success"></i>
                                        <span>User Growth</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="role_distribution">
                                        <i class="fas fa-chart-pie text-primary"></i>
                                        <span>Role Distribution</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="department_chart">
                                        <i class="fas fa-chart-bar text-info"></i>
                                        <span>Dept. Overview</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- System Widgets --}}
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#systemWidgets">
                                    <i class="fas fa-server me-2"></i> System
                                </button>
                            </h2>
                            <div id="systemWidgets" class="accordion-collapse collapse" data-bs-parent="#widgetAccordion">
                                <div class="accordion-body p-2">
                                    <div class="widget-library-item" draggable="true" data-widget-type="system_health">
                                        <i class="fas fa-heartbeat text-danger"></i>
                                        <span>System Health</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="storage_usage">
                                        <i class="fas fa-hdd text-warning"></i>
                                        <span>Storage Usage</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="last_backup">
                                        <i class="fas fa-database text-info"></i>
                                        <span>Last Backup</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ════════════════════════════════════════════════════════════════════════
                             SERVER HEALTH - Super Admin Only
                             ════════════════════════════════════════════════════════════════════════ --}}
                        @can('view server stats')
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#serverHealthWidgets">
                                    <i class="fas fa-heartbeat text-danger me-2"></i> Server Health
                                </button>
                            </h2>
                            <div id="serverHealthWidgets" class="accordion-collapse collapse" data-bs-parent="#widgetAccordion">
                                <div class="accordion-body p-2">
                                    <div class="widget-library-item" draggable="true" data-widget-type="server_health">
                                        <i class="fas fa-heartbeat text-danger"></i>
                                        <span>Overall Server Health</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="cpu_usage">
                                        <i class="fas fa-microchip text-info"></i>
                                        <span>CPU Usage</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="memory_usage">
                                        <i class="fas fa-memory text-success"></i>
                                        <span>Memory Usage</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="disk_usage">
                                        <i class="fas fa-hdd text-warning"></i>
                                        <span>Disk Usage</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="uptime">
                                        <i class="fas fa-clock text-primary"></i>
                                        <span>Server Uptime</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endcan

                        {{-- ════════════════════════════════════════════════════════════════════════
                             DATABASE - Super Admin Only
                             ════════════════════════════════════════════════════════════════════════ --}}
                        @can('view server stats')
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#databaseWidgets">
                                    <i class="fas fa-database text-primary me-2"></i> Database
                                </button>
                            </h2>
                            <div id="databaseWidgets" class="accordion-collapse collapse" data-bs-parent="#widgetAccordion">
                                <div class="accordion-body p-2">
                                    <div class="widget-library-item" draggable="true" data-widget-type="database_health">
                                        <i class="fas fa-database text-primary"></i>
                                        <span>Database Health</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="database_size">
                                        <i class="fas fa-database text-info"></i>
                                        <span>Database Size</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="backup_status">
                                        <i class="fas fa-shield-alt text-success"></i>
                                        <span>Backup Status</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="slow_queries">
                                        <i class="fas fa-exclamation-triangle text-warning"></i>
                                        <span>Slow Queries</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="db_connections">
                                        <i class="fas fa-link text-danger"></i>
                                        <span>DB Connections</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endcan

                        {{-- ════════════════════════════════════════════════════════════════════════
                             SERVER LOGS - Super Admin Only
                             ════════════════════════════════════════════════════════════════════════ --}}
                        @can('view server stats')
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#serverLogsWidgets">
                                    <i class="fas fa-history text-info me-2"></i> Server Logs
                                </button>
                            </h2>
                            <div id="serverLogsWidgets" class="accordion-collapse collapse" data-bs-parent="#widgetAccordion">
                                <div class="accordion-body p-2">
                                    <div class="widget-library-item" draggable="true" data-widget-type="error_logs">
                                        <i class="fas fa-exclamation-circle text-danger"></i>
                                        <span>Error Logs</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="activity_timeline">
                                        <i class="fas fa-history text-info"></i>
                                        <span>Activity Timeline</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="login_attempts">
                                        <i class="fas fa-sign-in-alt text-primary"></i>
                                        <span>Login Attempts</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="system_logs">
                                        <i class="fas fa-file-alt text-secondary"></i>
                                        <span>System Logs</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="audit_log">
                                        <i class="fas fa-clipboard-list text-warning"></i>
                                        <span>Audit Log</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endcan

                        {{-- ════════════════════════════════════════════════════════════════════════
                             PERFORMANCE - Super Admin Only
                             ════════════════════════════════════════════════════════════════════════ --}}
                        @can('view server stats')
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#performanceWidgets">
                                    <i class="fas fa-tachometer-alt text-success me-2"></i> Performance
                                </button>
                            </h2>
                            <div id="performanceWidgets" class="accordion-collapse collapse" data-bs-parent="#widgetAccordion">
                                <div class="accordion-body p-2">
                                    <div class="widget-library-item" draggable="true" data-widget-type="response_time">
                                        <i class="fas fa-stopwatch text-info"></i>
                                        <span>Response Time</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="request_rate">
                                        <i class="fas fa-arrow-right text-primary"></i>
                                        <span>Request Rate</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="error_rate">
                                        <i class="fas fa-exclamation text-danger"></i>
                                        <span>Error Rate</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="cache_status">
                                        <i class="fas fa-bolt text-warning"></i>
                                        <span>Cache Status</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endcan

                        {{-- ════════════════════════════════════════════════════════════════════════
                             SECURITY - Super Admin Only
                             ════════════════════════════════════════════════════════════════════════ --}}
                        @can('view server stats')
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#securityWidgets">
                                    <i class="fas fa-shield-alt text-success me-2"></i> Security
                                </button>
                            </h2>
                            <div id="securityWidgets" class="accordion-collapse collapse" data-bs-parent="#widgetAccordion">
                                <div class="accordion-body p-2">
                                    <div class="widget-library-item" draggable="true" data-widget-type="security_threats">
                                        <i class="fas fa-virus text-danger"></i>
                                        <span>Security Threats</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="failed_logins">
                                        <i class="fas fa-ban text-danger"></i>
                                        <span>Failed Logins</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="ip_blocks">
                                        <i class="fas fa-ban text-warning"></i>
                                        <span>IP Blocks</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="ssl_certificate">
                                        <i class="fas fa-lock text-success"></i>
                                        <span>SSL Certificate</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endcan

                        {{-- ════════════════════════════════════════════════════════════════════════
                             ADMIN - Admin Only
                             ════════════════════════════════════════════════════════════════════════ --}}
                        @can('manage users')
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#adminWidgets">
                                    <i class="fas fa-user-tie text-primary me-2"></i> Admin
                                </button>
                            </h2>
                            <div id="adminWidgets" class="accordion-collapse collapse" data-bs-parent="#widgetAccordion">
                                <div class="accordion-body p-2">
                                    <div class="widget-library-item" draggable="true" data-widget-type="user_management">
                                        <i class="fas fa-users-cog text-primary"></i>
                                        <span>User Management</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="pending_approvals">
                                        <i class="fas fa-clipboard-check text-warning"></i>
                                        <span>Pending Approvals</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="department_health">
                                        <i class="fas fa-sitemap text-info"></i>
                                        <span>Department Health</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="registration_status">
                                        <i class="fas fa-id-card text-success"></i>
                                        <span>Registration Status</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endcan

                        {{-- ════════════════════════════════════════════════════════════════════════
                             PERSONAL - All Users
                             ════════════════════════════════════════════════════════════════════════ --}}
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#personalWidgets">
                                    <i class="fas fa-user text-success me-2"></i> Personal
                                </button>
                            </h2>
                            <div id="personalWidgets" class="accordion-collapse collapse" data-bs-parent="#widgetAccordion">
                                <div class="accordion-body p-2">
                                    <div class="widget-library-item" draggable="true" data-widget-type="my_profile">
                                        <i class="fas fa-user-circle text-primary"></i>
                                        <span>My Profile</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="my_activity">
                                        <i class="fas fa-history text-info"></i>
                                        <span>My Activity</span>
                                    </div>
                                    <div class="widget-library-item" draggable="true" data-widget-type="my_registrations">
                                        <i class="fas fa-certificate text-success"></i>
                                        <span>My Registrations</span>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
                
                {{-- Quick Templates --}}
                <div class="card-footer p-2">
                    <small class="text-muted d-block mb-2">
                        <i class="fas fa-magic"></i> Quick Layouts:
                    </small>
                    <div class="btn-group-vertical w-100">
                        <button class="btn btn-sm btn-outline-primary template-btn" data-template="default">
                            <i class="fas fa-th"></i> Default (4 Cards)
                        </button>
                        <button class="btn btn-sm btn-outline-primary template-btn" data-template="single">
                            <i class="fas fa-square"></i> Single Column
                        </button>
                        <button class="btn btn-sm btn-outline-primary template-btn" data-template="dual">
                            <i class="fas fa-th-large"></i> Two Columns
                        </button>
                        <button class="btn btn-sm btn-outline-primary template-btn" data-template="triple">
                            <i class="fas fa-th"></i> Three Columns
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Dashboard Preview/Builder --}}
        <div class="col-md-9">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-muted">
                            <i class="fas fa-desktop"></i> Dashboard Preview
                        </h6>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="enableGridLines" checked>
                            <label class="form-check-label" for="enableGridLines">
                                Show Grid
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-body p-3" style="min-height: 600px; background: #f8f9fa;">
                    
                    {{-- GridStack Container --}}
                    <div class="grid-stack" id="dashboardGrid">
                        {{-- Widgets will be added here dynamically --}}
                        {{-- Initial default widgets loaded from database or template --}}
                    </div>

                </div>
                <div class="card-footer">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <small class="text-muted">Widgets:</small>
                            <strong id="widgetCount">0</strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Layout:</small>
                            <strong id="layoutType">Custom</strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Last Saved:</small>
                            <strong id="lastSaved">Never</strong>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-sm btn-success" id="saveLayoutBtn">
                                <i class="fas fa-save"></i> Save Now
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- User Profiles Section --}}
    <div class="card shadow-sm border-0 mt-3">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0">
                <i class="fas fa-user-cog"></i> My Dashboard Profiles
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12 mb-3">
                    <button class="btn btn-primary btn-sm" id="createProfileBtn">
                        <i class="fas fa-plus"></i> Create New Profile
                    </button>
                </div>
            </div>
            <div class="row" id="profilesList">
                {{-- User's saved dashboard profiles will appear here --}}
                <div class="col-md-4 mb-3">
                    <div class="card profile-card">
                        <div class="card-body">
                            <h6>Default Dashboard</h6>
                            <p class="small text-muted mb-2">4 widgets • Last modified: Today</p>
                            <div class="btn-group btn-group-sm w-100">
                                <button class="btn btn-outline-primary">
                                    <i class="fas fa-eye"></i> Load
                                </button>
                                <button class="btn btn-outline-secondary">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>{{-- /dashboard tab --}}

{{-- Dashboard Builder Styles --}}
<style>
/* Widget Library */
.widget-library-item {
    padding: 10px;
    margin-bottom: 8px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    cursor: grab;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
}

.widget-library-item:hover {
    background: #e9ecef;
    border-color: #0d6efd;
    transform: translateX(2px);
}

.widget-library-item:active {
    cursor: grabbing;
}

.widget-library-item i {
    font-size: 1.2rem;
}

/* GridStack Customization */
.grid-stack {
    background: repeating-linear-gradient(
        0deg,
        transparent,
        transparent 79px,
        #e0e0e0 79px,
        #e0e0e0 80px
    ),
    repeating-linear-gradient(
        90deg,
        transparent,
        transparent 79px,
        #e0e0e0 79px,
        #e0e0e0 80px
    );
}

.grid-stack.no-grid {
    background: none;
}

.grid-stack-item-content {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: all 0.3s;
}

.grid-stack-item-content:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* Widget Cards */
.widget-card {
    height: 100%;
    display: flex;
    flex-direction: column;
    padding: 15px;
}

.widget-card .widget-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.widget-card .widget-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: #666;
}

.widget-card .widget-actions {
    display: flex;
    gap: 5px;
}

.widget-card .widget-action-btn {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: none;
    background: #f8f9fa;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    transition: all 0.2s;
}

.widget-card .widget-action-btn:hover {
    background: #e9ecef;
}

.widget-card .widget-content {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
}

.widget-card .widget-value {
    font-size: 2.5rem;
    font-weight: 700;
    line-height: 1;
}

.widget-card .widget-label {
    font-size: 0.85rem;
    color: #999;
    margin-top: 5px;
}

/* Profile Cards */
.profile-card {
    border: 2px solid #dee2e6;
    transition: all 0.2s;
}

.profile-card:hover {
    border-color: #0d6efd;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Accordion Customization */
.accordion-button {
    padding: 8px 12px;
    font-size: 0.9rem;
}

.accordion-button:not(.collapsed) {
    background-color: #e7f3ff;
    color: #0d6efd;
}

/* Template Buttons */
.template-btn {
    justify-content: flex-start;
    text-align: left;
    margin-bottom: 4px;
}

.template-btn i {
    width: 20px;
}
</style>

{{-- Dashboard Builder Scripts --}}

       {{-- ════════════════════════════════════════════
     3. DATABASE TAB — 3 stacked cards
════════════════════════════════════════════ --}}
<div class="tab-pane fade" id="database" role="tabpanel">

    {{-- Card 1 — Create Backup --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                <i class="fas fa-database me-2"></i> Create Database Backup
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('database.backup.run') }}">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-play me-1"></i> Create Database Backup
                </button>
            </form>
        </div>
    </div>

    {{-- Card 2 — Restore Database --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">
                <i class="fas fa-undo me-2"></i> Restore Database
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('database.restore') }}" enctype="multipart/form-data">
                @csrf

                {{-- Drag & Drop Zone --}}
                <div class="drop-zone mb-3">
                    <span class="drop-zone__prompt">Drag & drop your .sql file here or click to select</span>
                    <input type="file" name="backup_file" class="drop-zone__input" accept=".sql" required>
                </div>

                @error('backup_file')
                    <div class="text-danger mb-2">{{ $message }}</div>
                @enderror

                <button type="submit" class="btn btn-danger"
                        onclick="return confirm('This will OVERWRITE the current database. Are you sure?')">
                    <i class="fas fa-undo me-1"></i> Restore Database
                </button>
            </form>
        </div>
    </div>

    {{-- Card 3 — Backup History --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                <i class="fas fa-history me-2"></i> Backup History
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th><th>File</th><th>Size</th><th>Date</th><th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($backups ?? [] as $backup)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="text-break"><small>{{ $backup['name'] }}</small></td>
                                <td><span class="badge bg-secondary">{{ $backup['size'] }}</span></td>
                                <td><small class="text-muted">{{ $backup['time'] }}</small></td>
                                <td class="text-center">
                                    <a href="{{ route('database.backup.download', $backup['name']) }}"
                                       class="btn btn-sm btn-success">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">
                                    <i class="fas fa-inbox me-1"></i> No backups found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>{{-- /database --}}

        {{-- ════════════════════════════════════════════
             4. WEBSITE BACKUP TAB  (3 stacked cards)
             ════════════════════════════════════════════ --}}
        <div class="tab-pane fade" id="backup" role="tabpanel">
            <div class="d-flex flex-column gap-4">

                {{-- ╔══════════════════════════════════════════╗
                     ║  CARD 1 — CREATE BACKUP                 ║
                     ╚══════════════════════════════════════════╝ --}}
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <span class="badge rounded-pill bg-light text-success me-2" style="width:24px;height:24px;line-height:24px;font-size:.7rem;">1</span>
                            <i class="fas fa-archive me-1"></i> Create Backup
                        </h5>
                    </div>
                    <div class="card-body">

                        <form method="POST" action="{{ route('website.backup.run') }}">
                            @csrf

                            {{-- Folder Selection --}}
                            <h6 class="fw-semibold mb-2 small text-uppercase text-muted ls-1">
                                <i class="fas fa-folder-open me-1"></i> Select Folders
                            </h6>
                            <div class="mb-3 p-3 rounded" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);">

                                <div class="mb-2 pb-2" style="border-bottom:1px solid rgba(255,255,255,0.08);">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" id="selectAllFolders" checked
                                               onchange="document.querySelectorAll('.folder-check').forEach(cb => cb.checked = this.checked)">
                                        <label class="form-check-label fw-semibold small" for="selectAllFolders">
                                            Select / Deselect All
                                        </label>
                                    </div>
                                </div>

                                <div class="row row-cols-2 row-cols-md-4 g-2 mt-1">
                                    @foreach([
                                        'app'           => 'fas fa-code',
                                        'bootstrap'     => 'fas fa-rocket',
                                        'config'        => 'fas fa-sliders-h',
                                        'database'      => 'fas fa-database',
                                        'lang'          => 'fas fa-language',
                                        'module-sample' => 'fas fa-puzzle-piece',
                                        'modules'       => 'fas fa-cubes',
                                        'public'        => 'fas fa-globe',
                                        'resources'     => 'fas fa-paint-brush',
                                        'routes'        => 'fas fa-route',
                                        //'storage'       => 'fas fa-hdd',
                                        'tests'         => 'fas fa-vial',
                                    ] as $folder => $icon)
                                        <div class="col">
                                            <div class="form-check p-2 rounded d-flex align-items-center gap-2"
                                                 style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);">
                                                <input class="form-check-input folder-check mt-0" type="checkbox"
                                                       name="folders[]" value="{{ $folder }}"
                                                       id="folder_{{ $folder }}" checked>
                                                <label class="form-check-label d-flex align-items-center gap-2 w-100 mb-0"
                                                       for="folder_{{ $folder }}" style="cursor:pointer;">
                                                    <i class="fas {{ $icon }} fa-fw small" style="color:#34c7c0;"></i>
                                                    <span class="text-break small fw-semibold">{{ $folder }}</span>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Note --}}
                            <h6 class="fw-semibold mb-2 small text-uppercase text-muted">
                                <i class="fas fa-sticky-note me-1"></i> Backup Note
                                <span class="text-muted fw-normal">(optional)</span>
                            </h6>
                            <textarea name="note" rows="2" class="form-control mb-3"
                                      placeholder="e.g. Before v2.1 update, pre-launch snapshot…"
                                      maxlength="300">{{ old('note') }}</textarea>

                            <button type="submit" class="btn btn-success px-4">
                                <i class="fas fa-file-archive me-1"></i> Create Website Backup
                            </button>
                        </form>

                    </div>
                </div>{{-- /card 1 --}}


                {{-- ╔══════════════════════════════════════════╗
                     ║  CARD 2 — RESTORE FROM ZIP              ║
                     ╚══════════════════════════════════════════╝ --}}
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <span class="badge rounded-pill bg-light text-primary me-2" style="width:24px;height:24px;line-height:24px;font-size:.7rem;">2</span>
                            <i class="fas fa-upload me-1"></i> Restore from External ZIP
                        </h5>
                    </div>
                    <div class="card-body">

                        <p class="text-muted small mb-3">
                            Drop a third-party ZIP backup below. It will be extracted to
                            <code>storage/app/sitebackup/</code>, compared against your live files,
                            then you choose what to overwrite.
                        </p>

                        {{-- STEP 1 — Drop zone --}}
                        <div id="restoreStep1">
                            <div id="restoreDropZone"
                                 class="rounded text-center py-4 px-3 mb-3"
                                 style="border:2px dashed rgba(0,122,255,0.4);background:rgba(0,122,255,0.04);cursor:pointer;transition:border-color .2s;">
                                <i class="fas fa-cloud-upload-alt fa-2x mb-2" style="color:#007AFF;opacity:.7;"></i>
                                <p class="mb-1 fw-semibold small">Drag &amp; drop your ZIP here</p>
                                <p class="text-muted small mb-3">or click to browse</p>
                                <input type="file" id="restoreFileInput" accept=".zip" class="d-none">
                                <button type="button" class="btn btn-outline-primary btn-sm"
                                        onclick="document.getElementById('restoreFileInput').click()">
                                    <i class="fas fa-folder-open me-1"></i> Browse File
                                </button>
                            </div>

                            <div id="restoreFileInfo" class="d-none rounded p-3 mb-3 d-flex align-items-center gap-3"
                                 style="background:rgba(0,122,255,0.08);border:1px solid rgba(0,122,255,0.25);">
                                <i class="fas fa-file-archive fa-2x" style="color:#007AFF;"></i>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold small" id="restoreFileName">—</div>
                                    <div class="text-muted small" id="restoreFileSize">—</div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="wbResetDropZone()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            <button id="restoreUploadBtn" type="button"
                                    class="btn btn-primary d-none"
                                    onclick="wbUploadAndCompare()">
                                <i class="fas fa-sync me-1"></i> Upload &amp; Compare
                            </button>
                        </div>

                        {{-- STEP 2 — Progress --}}
                        <div id="restoreStep2" class="d-none">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <i class="fas fa-spinner fa-spin" style="color:#007AFF;"></i>
                                <span class="fw-semibold small" id="restoreProgressLabel">Uploading…</span>
                                <span class="ms-auto small text-muted" id="restoreProgressPct">0%</span>
                            </div>
                            <div class="progress mb-3" style="height:6px;">
                                <div id="restoreProgressBar"
                                     class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                     style="width:0%;"></div>
                            </div>
                            <div id="restoreLog" class="p-2 rounded small"
                                 style="background:rgba(0,0,0,0.2);font-family:monospace;max-height:100px;overflow-y:auto;"></div>
                        </div>

                        {{-- STEP 3 — Comparison --}}
                        <div id="restoreStep3" class="d-none">

                            <h6 class="fw-semibold small text-uppercase text-muted mb-2">
                                <i class="fas fa-code-branch me-1" style="color:#FF9500;"></i> File Comparison
                            </h6>

                            <div class="row g-2 mb-3">
                                <div class="col-4">
                                    <div class="text-center p-2 rounded" style="background:rgba(52,199,89,0.1);border:1px solid rgba(52,199,89,0.2);">
                                        <div class="fw-bold text-success" id="cntNew">—</div>
                                        <div class="small text-muted">New</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center p-2 rounded" style="background:rgba(255,149,0,0.1);border:1px solid rgba(255,149,0,0.2);">
                                        <div class="fw-bold text-warning" id="cntModified">—</div>
                                        <div class="small text-muted">Modified</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center p-2 rounded" style="background:rgba(142,142,147,0.1);border:1px solid rgba(142,142,147,0.2);">
                                        <div class="fw-bold" id="cntSame">—</div>
                                        <div class="small text-muted">Unchanged</div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive mb-3" style="max-height:220px;overflow-y:auto;">
                                <table class="table table-sm table-hover mb-0 small">
                                    <thead style="position:sticky;top:0;">
                                        <tr>
                                            <th>Status</th><th>File</th>
                                            <th>Backup</th><th>Live</th><th>Modified</th>
                                        </tr>
                                    </thead>
                                    <tbody id="restoreDiffTable"></tbody>
                                </table>
                            </div>

                            {{-- Restore mode --}}
                            <h6 class="fw-semibold small text-uppercase text-muted mb-2">
                                <i class="fas fa-cogs me-1"></i> Restore Mode
                            </h6>
                            <div class="d-flex flex-column gap-2 mb-3">
                                <div class="form-check p-3 rounded"
                                     style="background:rgba(255,59,48,0.07);border:1px solid rgba(255,59,48,0.2);">
                                    <input class="form-check-input" type="radio" name="restoreMode"
                                           id="modeFull" value="full" checked>
                                    <label class="form-check-label" for="modeFull">
                                        <strong class="text-danger">Full Restore</strong>
                                        <span class="text-muted small d-block">
                                            Overwrites <em>all</em> files including unchanged ones.
                                        </span>
                                    </label>
                                </div>
                                <div class="form-check p-3 rounded"
                                     style="background:rgba(52,199,89,0.07);border:1px solid rgba(52,199,89,0.2);">
                                    <input class="form-check-input" type="radio" name="restoreMode"
                                           id="modeNewer" value="newer">
                                    <label class="form-check-label" for="modeNewer">
                                        <strong class="text-success">Newer / Changed Files Only</strong>
                                        <span class="text-muted small d-block">
                                            Skips files where size <em>and</em> modified date are identical.
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-danger" onclick="wbExecuteRestore()">
                                    <i class="fas fa-undo me-1"></i> Execute Restore
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="wbResetAll()">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </button>
                            </div>
                        </div>

                        {{-- STEP 4 — Done --}}
                        <div id="restoreStep4" class="d-none text-center py-3">
                            <i class="fas fa-check-circle fa-3x mb-2" style="color:#34C759;"></i>
                            <h6 class="fw-bold mb-1">Restore Complete</h6>
                            <p class="text-muted small mb-3" id="restoreDoneMsg"></p>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="wbResetAll()">
                                <i class="fas fa-redo me-1"></i> Restore Another
                            </button>
                        </div>

                    </div>
                </div>{{-- /card 2 --}}


                {{-- ╔══════════════════════════════════════════╗
                     ║  CARD 3 — BACKUP LIST                   ║
                     ╚══════════════════════════════════════════╝ --}}
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <span class="badge rounded-pill bg-light text-warning me-2" style="width:24px;height:24px;line-height:24px;font-size:.7rem;">3</span>
                            <i class="fas fa-list me-1"></i> Backup List
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-3">#</th>
                                        <th>File</th>
                                        <th>Size</th>
                                        <th>Date</th>
                                        <th>Note</th>
                                        <th class="text-center pe-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($websiteBackups ?? [] as $backup)
                                        <tr>
                                            <td class="ps-3 small text-muted">{{ $loop->iteration }}</td>
                                            <td class="small">
                                                <i class="fas fa-file-archive me-1 text-success"></i>
                                                {{ $backup['name'] }}
                                            </td>
                                            <td class="small text-muted">{{ $backup['size'] }}</td>
                                            <td class="small text-muted">{{ $backup['time'] }}</td>
                                            <td class="small text-muted fst-italic" style="max-width:160px;">
                                                @if(!empty($backup['note']))
                                                    <span title="{{ $backup['note'] }}">
                                                        {{ Str::limit($backup['note'], 35) }}
                                                    </span>
                                                @else
                                                    <span class="opacity-50">—</span>
                                                @endif
                                            </td>
                                            <td class="text-center pe-3">
                                                <div class="d-flex justify-content-center gap-1">

                                                    {{-- Info --}}
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-info"
                                                            title="Info"
                                                            onclick='wbShowInfo(@json($backup))'>
                                                        <i class="fas fa-info-circle"></i>
                                                    </button>

                                                    {{-- Download --}}
                                                    <a href="{{ route('website.backup.download', $backup['name']) }}"
                                                       class="btn btn-sm btn-outline-success" title="Download">
                                                        <i class="fas fa-download"></i>
                                                    </a>

                                                    {{-- Restore --}}
                                                    <form method="POST"
                                                          action="{{ route('website.backup.restore.local') }}"
                                                          class="d-inline"
                                                          onsubmit="return confirm('Restore this backup? This will overwrite live files.')">
                                                        @csrf
                                                        <input type="hidden" name="backup_name" value="{{ $backup['name'] }}">
                                                        <input type="hidden" name="mode" value="newer">
                                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Restore">
                                                            <i class="fas fa-undo"></i>
                                                        </button>
                                                    </form>

                                                    {{-- Delete --}}
                                                    <form method="POST"
                                                          action="{{ route('website.backup.delete', $backup['name']) }}"
                                                          class="d-inline"
                                                          onsubmit="return confirm('Delete this backup permanently?')">
                                                        @csrf
                                                        
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>

                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x d-block mb-2 opacity-50"></i>
                                                No website backups found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>{{-- /card 3 --}}

            </div>{{-- /d-flex flex-column --}}
        </div>{{-- /backup tab-pane --}}


        {{-- ── Info Modal ──────────────────────────────────────── --}}
        <div class="modal fade" id="backupInfoModal" tabindex="-1" data-bs-backdrop="true" data-bs-keyboard="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="background:#1c1c2e;border:1px solid rgba(255,255,255,0.12);">
                    <div class="modal-header" style="border-bottom:1px solid rgba(255,255,255,0.1);">
                        <h6 class="modal-title fw-bold">
                            <i class="fas fa-info-circle me-2" style="color:#007AFF;"></i> Backup Info
                        </h6>
                        <button type="button" class="close ml-auto" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <dl class="row mb-0 small">
                            <dt class="col-4 text-muted">File</dt>
                            <dd class="col-8 text-break fw-semibold" id="infoName">—</dd>
                            <dt class="col-4 text-muted">Size</dt>
                            <dd class="col-8" id="infoSize">—</dd>
                            <dt class="col-4 text-muted">Created</dt>
                            <dd class="col-8" id="infoTime">—</dd>
                            <dt class="col-4 text-muted">Folders</dt>
                            <dd class="col-8" id="infoFolders">—</dd>
                            <dt class="col-4 text-muted">Note</dt>
                            <dd class="col-8 fst-italic" id="infoNote">—</dd>
                        </dl>
                    </div>
                    <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,0.1);">
                        <button type="button" class="btn btn-sm btn-secondary" onclick="$('#backupInfoModal').modal('hide')">Close</button>
                    </div>
                </div>
            </div>
        </div>


        {{-- ── Restore + Info JS ───────────────────────────────── --}}
        <script>
        (function () {

            /* helpers */
            function show(id) { document.getElementById(id).classList.remove('d-none'); }
            function hide(id) { document.getElementById(id).classList.add('d-none'); }
            function log(msg) {
                var el = document.getElementById('restoreLog');
                el.innerHTML += '<div>› ' + msg + '</div>';
                el.scrollTop = el.scrollHeight;
            }
            function progress(pct, label) {
                document.getElementById('restoreProgressBar').style.width = pct + '%';
                document.getElementById('restoreProgressPct').textContent  = pct + '%';
                document.getElementById('restoreProgressLabel').textContent = label;
            }

            /* drop zone */
            var dropZone   = document.getElementById('restoreDropZone');
            var fileInput  = document.getElementById('restoreFileInput');
            var chosenFile = null;
            var stagingPath = null;

            dropZone.addEventListener('dragover',  function(e){ e.preventDefault(); dropZone.style.borderColor='#007AFF'; });
            dropZone.addEventListener('dragleave', function(){ dropZone.style.borderColor='rgba(0,122,255,0.4)'; });
            dropZone.addEventListener('drop', function(e){
                e.preventDefault();
                dropZone.style.borderColor = 'rgba(0,122,255,0.4)';
                if (e.dataTransfer.files.length) wbSetFile(e.dataTransfer.files[0]);
            });
            dropZone.addEventListener('click', function(){ fileInput.click(); });
            fileInput.addEventListener('change', function(){ if (this.files[0]) wbSetFile(this.files[0]); });

            window.wbSetFile = function(file) {
                if (!file.name.endsWith('.zip')) { alert('Please select a .zip file.'); return; }
                chosenFile = file;
                document.getElementById('restoreFileName').textContent = file.name;
                document.getElementById('restoreFileSize').textContent = (file.size / 1048576).toFixed(2) + ' MB';
                show('restoreFileInfo');
                show('restoreUploadBtn');
            };

            window.wbResetDropZone = function() {
                chosenFile = null; fileInput.value = '';
                hide('restoreFileInfo'); hide('restoreUploadBtn');
                document.getElementById('restoreFileName').textContent = '—';
                document.getElementById('restoreFileSize').textContent = '—';
            };

            window.wbResetAll = function() {
                wbResetDropZone();
                hide('restoreStep2'); hide('restoreStep3'); hide('restoreStep4');
                show('restoreStep1');
                document.getElementById('restoreLog').innerHTML = '';
                stagingPath = null;
            };

            /* upload & compare */
            window.wbUploadAndCompare = function() {
                if (!chosenFile) return;
                hide('restoreStep1'); show('restoreStep2');
                progress(0, 'Uploading ZIP…');
                log('Uploading: ' + chosenFile.name);

                var fd = new FormData();
                fd.append('_token', document.querySelector('meta[name=csrf-token]').content);
                fd.append('zip_file', chosenFile);

                var xhr = new XMLHttpRequest();
                xhr.open('POST', '{{ route("website.backup.restore.upload") }}');

                xhr.upload.onprogress = function(e) {
                    if (e.lengthComputable) {
                        var pct = Math.round(e.loaded / e.total * 50);
                        progress(pct, 'Uploading…');
                    }
                };

                xhr.onload = function() {
                    if (xhr.status !== 200) { alert('Upload failed.'); wbResetAll(); return; }
                    var res = JSON.parse(xhr.responseText);
                    if (!res.success) { alert(res.message || 'Upload failed.'); wbResetAll(); return; }
                    stagingPath = res.staging_path;
                    log('Extracting to storage/app/sitebackup/…');
                    progress(65, 'Extracting…');
                    setTimeout(function(){
                        progress(90, 'Comparing files…');
                        log('Comparing with live site…');
                        setTimeout(function(){
                            progress(100, 'Done.');
                            log('Found ' + res.diff.total + ' files.');
                            wbRenderDiff(res.diff);
                        }, 500);
                    }, 700);
                };

                xhr.onerror = function(){ alert('Network error.'); wbResetAll(); };
                xhr.send(fd);
            };

            /* render diff */
            window.wbRenderDiff = function(diff) {
                hide('restoreStep2'); show('restoreStep3');
                document.getElementById('cntNew').textContent      = diff.new_count      || 0;
                document.getElementById('cntModified').textContent = diff.modified_count || 0;
                document.getElementById('cntSame').textContent     = diff.same_count     || 0;

                var tbody = document.getElementById('restoreDiffTable');
                tbody.innerHTML = '';
                (diff.files || []).forEach(function(f) {
                    var cls  = f.status==='new' ? 'text-success' : f.status==='modified' ? 'text-warning' : 'text-muted';
                    var icon = f.status==='new' ? 'fa-plus-circle' : f.status==='modified' ? 'fa-pencil-alt' : 'fa-equals';
                    tbody.innerHTML +=
                        '<tr>' +
                        '<td><i class="fas '+icon+' '+cls+'"></i> <span class="'+cls+'">'+f.status+'</span></td>' +
                        '<td class="text-break" style="max-width:200px;">'+f.path+'</td>' +
                        '<td>'+( f.backup_size||'—')+'</td>' +
                        '<td>'+(f.live_size  ||'—')+'</td>' +
                        '<td class="text-muted">'+(f.backup_mtime||'—')+'</td>' +
                        '</tr>';
                });
                if (!diff.files || !diff.files.length) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">No differences found.</td></tr>';
                }
            };


/* execute restore - NON-BLOCKING VERSION */
window.wbExecuteRestore = function() {
    var mode = document.querySelector('input[name=restoreMode]:checked').value;
    if (!confirm('Execute ' + (mode==='full'?'FULL':'Newer-Only') + ' restore?')) return;

    var fd = new FormData();
    fd.append('_token', document.querySelector('meta[name=csrf-token]').content);
    fd.append('staging_path', stagingPath);
    fd.append('mode', mode);

    hide('restoreStep3'); 
    show('restoreStep2');
    progress(0, 'Restoring...'); 
    log('Starting restore (' + mode + ')...');

    // Use XMLHttpRequest instead of fetch for better progress handling
    var xhr = new XMLHttpRequest();
    
    xhr.open('POST', '{{ route("website.backup.restore.execute") }}', true);
    
    xhr.onload = function() {
        if (xhr.status !== 200) {
            alert('Restore failed: Server error ' + xhr.status);
            hide('restoreStep2'); 
            show('restoreStep3');
            return;
        }
        
        try {
            var res = JSON.parse(xhr.responseText);
            if (!res.success) {
                alert(res.message || 'Restore failed.');
                hide('restoreStep2'); 
                show('restoreStep3');
                return;
            }
            
            progress(100, 'Complete.');
            log('Done. Copied: ' + (res.copied || 0) + ', Skipped: ' + (res.skipped || 0));
            
            setTimeout(function() {
                hide('restoreStep2');
                document.getElementById('restoreDoneMsg').textContent =
                    (res.copied || 0) + ' file(s) copied, ' + (res.skipped || 0) + ' skipped.';
                show('restoreStep4');
            }, 400);
            
        } catch (e) {
            alert('Invalid response from server');
            hide('restoreStep2'); 
            show('restoreStep3');
        }
    };
    
    xhr.onerror = function() { 
        alert('Network error.'); 
        hide('restoreStep2'); 
        show('restoreStep3'); 
    };
    
    xhr.onabort = function() {
        log('Request aborted');
    };
    
    // Send as multipart/form-data (default for FormData)
    xhr.send(fd);
};

            /* info modal */
window.wbShowInfo = function(b) {

    document.getElementById('infoName').textContent    = b.name    || '—';
    document.getElementById('infoSize').textContent    = b.size    || '—';
    document.getElementById('infoTime').textContent    = b.time    || '—';
    document.getElementById('infoFolders').textContent = b.folders ? b.folders.join(', ') : '—';
    document.getElementById('infoNote').textContent    = b.note    || '(no note)';

    var modalEl = document.getElementById('backupInfoModal');
    document.body.appendChild(modalEl);

    $('#backupInfoModal').modal('show');
};

// Backup progress functions
function startBackup() {
    var form = document.getElementById('backupForm');
    var formData = new FormData(form);
    
    // Validate at least one folder selected
    var folders = formData.getAll('folders[]');
    if (folders.length === 0) {
        alert('Please select at least one folder to backup.');
        return;
    }
    
    // Show progress
    document.getElementById('backupStep1').classList.add('d-none');
    document.getElementById('backupStep2').classList.remove('d-none');
    
    var xhr = new XMLHttpRequest();
    
    xhr.open('POST', '{{ route("website.backup.run.ajax") }}', true);
    xhr.setRequest