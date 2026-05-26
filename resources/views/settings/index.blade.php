@extends('layouts.admin')

@section('title', 'Settings')

@section('main-content')
<div class="fh-container" style="padding-top: 3.5rem;">

    <style>
    .modal { z-index: 99999 !important; }
    .modal-backdrop { z-index: 99990 !important; }
    body.modal-open { overflow: hidden !important; }
    </style>

    <div class="fh-header">
        <div class="fh-header__left">
            <h1 class="fh-page-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                Settings
            </h1>
        </div>
    </div>

    @if(session('success'))
        <div class="fh-toast fh-toast--success">✓ {{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="fh-toast fh-toast--error">{{ $errors->first() }}</div>
    @endif

    <div class="fh-tabs" id="settingsTab">
        <button class="fh-tab fh-tab--active" data-tab="general" onclick="switchSettingsTab('general')">
            <svg viewBox="0 0 20 20" fill="currentColor" style="width:1rem;height:1rem;margin-right:0.25rem"><path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/></svg>
            General
        </button>
        <button class="fh-tab" data-tab="dashboard" onclick="switchSettingsTab('dashboard')">
            <svg viewBox="0 0 20 20" fill="currentColor" style="width:1rem;height:1rem;margin-right:0.25rem"><path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"/><path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"/></svg>
            Dashboard
        </button>
        <button class="fh-tab" data-tab="database" onclick="switchSettingsTab('database')">
            <svg viewBox="0 0 20 20" fill="currentColor" style="width:1rem;height:1rem;margin-right:0.25rem"><path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm14 1a1 1 0 11-2 0 1 1 0 012 0zM2 13a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2v-2zm14 1a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd"/></svg>
            Database
        </button>
        <button class="fh-tab" data-tab="backup" onclick="switchSettingsTab('backup')">
            <svg viewBox="0 0 20 20" fill="currentColor" style="width:1rem;height:1rem;margin-right:0.25rem"><path fill-rule="evenodd" d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z" clip-rule="evenodd"/></svg>
            Backup
        </button>
        <button class="fh-tab" data-tab="update" onclick="switchSettingsTab('update')">
            <svg viewBox="0 0 20 20" fill="currentColor" style="width:1rem;height:1rem;margin-right:0.25rem"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/></svg>
            Update
        </button>
        <button class="fh-tab" data-tab="mail" onclick="switchSettingsTab('mail')">
            <svg viewBox="0 0 20 20" fill="currentColor" style="width:1rem;height:1rem;margin-right:0.25rem"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg>
            Mail
        </button>
        <a href="{{ route('settings.departmentMenus') }}" class="fh-tab" style="text-decoration: none;">
            <svg viewBox="0 0 20 20" fill="currentColor" style="width:1rem;height:1rem;margin-right:0.25rem"><path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>
            Department Menus
        </a>
    </div>

    <div style="margin-top: 1.5rem;">

        {{-- GENERAL TAB --}}
        <div id="tab-general" class="tab-panel" style="display: block;">
            <div class="fh-settings-grid" style="gap: 0.75rem;">
                <div style="grid-column: 1 / -1;">
                    <h3 style="font-size:1.25rem;font-weight:600;color:#111827;padding-bottom:0.5rem;border-bottom:1px solid #e5e7eb;">General Settings</h3>
                </div>

                <form id="generalSettingsForm" action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" style="grid-column:1/-1;">
                    @csrf

                    <div class="fh-setting-row" style="display:grid;grid-template-columns:180px 1fr 200px;gap:1rem;align-items:center;">
                        <label style="font-weight:600;color:#374151;">Website Name</label>
                        <input type="text" name="settings[site_name]" class="fh-setting-row__input" style="width:100%;" value="{{ old('settings.site_name', $settings['site_name'] ?? '') }}" placeholder="My Awesome Site">
                        <small style="color:#9ca3af;">&#123;&#123; $settings['site_name'] &#125;&#125;</small>
                    </div>

                    <div class="fh-setting-row" style="display:grid;grid-template-columns:180px 1fr 200px;gap:1rem;align-items:center;">
                        <label style="font-weight:600;color:#374151;">Website Email</label>
                        <input type="email" name="settings[site_email]" class="fh-setting-row__input" style="width:100%;" value="{{ old('settings.site_email', $settings['site_email'] ?? '') }}" placeholder="hello@example.com">
                        <small style="color:#9ca3af;">&#123;&#123; $settings['site_email'] &#125;&#125;</small>
                    </div>

                    <div class="fh-setting-row" style="display:grid;grid-template-columns:180px 1fr 200px;gap:1rem;align-items:center;">
                        <label style="font-weight:600;color:#374151;">Support Email</label>
                        <input type="email" name="settings[support_email]" class="fh-setting-row__input" style="width:100%;" value="{{ old('settings.support_email', $settings['support_email'] ?? '') }}" placeholder="support@example.com">
                        <small style="color:#9ca3af;">&#123;&#123; $settings['support_email'] &#125;&#125;</small>
                    </div>

                    <div class="fh-setting-row" style="display:grid;grid-template-columns:180px 1fr 200px;gap:1rem;align-items:center;">
                        <label style="font-weight:600;color:#374151;">Phone</label>
                        <input type="text" name="settings[site_phone]" class="fh-setting-row__input" style="width:100%;" value="{{ old('settings.site_phone', $settings['site_phone'] ?? '') }}" placeholder="+60 12 345 6789">
                        <small style="color:#9ca3af;">&#123;&#123; $settings['site_phone'] &#125;&#125;</small>
                    </div>

                    <div class="fh-setting-row" style="display:grid;grid-template-columns:180px 1fr 200px;gap:1rem;align-items:start;">
                        <label style="font-weight:600;color:#374151;padding-top:0.5rem;">Address</label>
                        <textarea name="settings[site_address]" class="fh-setting-row__input" style="width:100%;" rows="2" placeholder="No. 1, Jalan Example…">{{ old('settings.site_address', $settings['site_address'] ?? '') }}</textarea>
                        <small style="color:#9ca3af;padding-top:0.5rem;">&#123;&#123; $settings['site_address'] &#125;&#125;</small>
                    </div>

                    <div class="fh-setting-row" style="display:grid;grid-template-columns:180px 1fr 200px;gap:1rem;align-items:center;">
                        <label style="font-weight:600;color:#374151;">Logo</label>
                        <input type="file" name="settings[site_logo]" class="fh-setting-row__input" style="width:100%;" accept="image/*">
                        <small style="color:#9ca3af;">&#123;&#123; $settings['site_logo'] &#125;&#125;</small>
                    </div>

                    <div class="fh-setting-row" style="display:grid;grid-template-columns:180px 1fr 200px;gap:1rem;align-items:center;">
                        <label style="font-weight:600;color:#374151;">Favicon</label>
                        <input type="file" name="settings[site_favicon]" class="fh-setting-row__input" style="width:100%;" accept="image/*">
                        <small style="color:#9ca3af;">&#123;&#123; $settings['site_favicon'] &#125;&#125;</small>
                    </div>

                    <div class="fh-setting-row" style="display:grid;grid-template-columns:180px 1fr 200px;gap:1rem;align-items:center;">
                        <label style="font-weight:600;color:#374151;">Meta Title</label>
                        <input type="text" name="settings[meta_title]" class="fh-setting-row__input" style="width:100%;" value="{{ old('settings.meta_title', $settings['meta_title'] ?? '') }}" placeholder="My Site — Best in Town">
                        <small style="color:#9ca3af;">&#123;&#123; $settings['meta_title'] &#125;&#125;</small>
                    </div>

                    <div class="fh-setting-row" style="display:grid;grid-template-columns:180px 1fr 200px;gap:1rem;align-items:start;">
                        <label style="font-weight:600;color:#374151;padding-top:0.5rem;">Meta Description</label>
                        <textarea name="settings[meta_description]" class="fh-setting-row__input" style="width:100%;" rows="2" placeholder="Short description…">{{ old('settings.meta_description', $settings['meta_description'] ?? '') }}</textarea>
                        <small style="color:#9ca3af;padding-top:0.5rem;">&#123;&#123; $settings['meta_description'] &#125;&#125;</small>
                    </div>

                    <div class="fh-setting-row" style="display:grid;grid-template-columns:180px 1fr 200px;gap:1rem;align-items:center;">
                        <label style="font-weight:600;color:#374151;">Registration</label>
                        <div style="display:flex;align-items:center;gap:0.5rem;">
                            <input type="hidden" name="settings[enable_registration]" value="0">
                            <input type="checkbox" name="settings[enable_registration]" value="1" {{ old('settings.enable_registration', $settings['enable_registration'] ?? 0) == 1 ? 'checked' : '' }} style="width:1.25rem;height:1.25rem;">
                            <span>Allow new registrations</span>
                        </div>
                        <small style="color:#9ca3af;"></small>
                    </div>

                    <div class="fh-setting-row" style="display:grid;grid-template-columns:180px 1fr 200px;gap:1rem;align-items:center;">
                        <label style="font-weight:600;color:#374151;">Maintenance</label>
                        <div style="display:flex;align-items:center;gap:0.5rem;">
                            <input type="hidden" name="settings[maintenance_mode]" value="0">
                            <input type="checkbox" name="settings[maintenance_mode]" value="1" {{ old('settings.maintenance_mode', $settings['maintenance_mode'] ?? 0) == 1 ? 'checked' : '' }} style="width:1.25rem;height:1.25rem;">
                            <span>Enable maintenance mode</span>
                        </div>
                        <small style="color:#9ca3af;"></small>
                    </div>

                    <div style="margin-top:1.5rem;padding-top:1rem;border-top:1px solid #e5e7eb;">
                        <button type="submit" class="fh-btn" style="background:#2563eb;color:#fff;border:none;">
                            <svg viewBox="0 0 20 20" fill="currentColor" style="width:1rem;height:1rem;margin-right:0.25rem"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Save Settings
                        </button>
                    </div>
                </form>

                <script>
                // Before submit: disable empty text/email/textarea fields so they are
                // NOT included in POST — the DB keeps its existing value unchanged.
                // File inputs (logo/favicon) are always sent so they can still be updated.
                document.getElementById('generalSettingsForm').addEventListener('submit', function () {
                    this.querySelectorAll('input[type="text"], input[type="email"], textarea').forEach(function (el) {
                        if (el.value.trim() === '') {
                            el.disabled = true;
                        }
                    });
                });
                </script>
            </div>
        </div>

        {{-- TEMPORARY DEBUG — paste this at the top of the General tab, remove after fixing 
<div style="background:#fef3c7;border:1px solid #f59e0b;border-radius:8px;padding:1rem;margin-bottom:1rem;font-size:0.8rem;">
    <strong>DEBUG — Keys from DB:</strong><br>
    <pre style="margin:0;">{{ json_encode(array_keys($settings), JSON_PRETTY_PRINT) }}</pre>
    <strong>Full values:</strong><br>
    <pre style="margin:0;">{{ json_encode($settings, JSON_PRETTY_PRINT) }}</pre>
</div>--}}

        {{-- DASHBOARD TAB --}}
        <div id="tab-dashboard" class="tab-panel" style="display:none;">

            {{-- Header --}}
            <div class="fh-setting-row" style="display:block;margin-bottom:1rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;">
                    <div>
                        <h3 style="font-size:1.25rem;font-weight:600;color:#111827;margin:0 0 0.25rem;">
                            <svg viewBox="0 0 20 20" fill="currentColor" style="width:1.1rem;height:1.1rem;margin-right:0.4rem;vertical-align:middle;color:#10b981"><path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"/></svg>
                            Dashboard Builder
                        </h3>
                        <p style="color:#6b7280;margin:0;font-size:0.875rem;">Drag widgets from the library to build your dashboard. Resize and rearrange as needed.</p>
                    </div>
                    <div style="display:flex;gap:0.5rem;">
                        <button type="button" class="fh-btn fh-btn--ghost" id="dashboardTemplateBtn">
                            <svg viewBox="0 0 20 20" fill="currentColor" style="width:0.9rem;height:0.9rem;margin-right:0.25rem"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            Templates
                        </button>
                        <button type="button" class="fh-btn" style="background:#2563eb;color:#fff;border:none;" id="dashboardSaveBtn">
                            <svg viewBox="0 0 20 20" fill="currentColor" style="width:0.9rem;height:0.9rem;margin-right:0.25rem"><path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h5a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2h5v5.586l-1.293-1.293z"/></svg>
                            Save Layout
                        </button>
                        <button type="button" class="fh-btn fh-btn--ghost" id="dashboardResetBtn">
                            <svg viewBox="0 0 20 20" fill="currentColor" style="width:0.9rem;height:0.9rem;margin-right:0.25rem"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            {{-- Info banner --}}
            <div style="background:rgba(59,130,246,0.08);border:1px solid rgba(59,130,246,0.2);border-radius:0.5rem;padding:0.75rem 1rem;margin-bottom:1.25rem;color:#1e40af;font-size:0.875rem;">
                <svg viewBox="0 0 20 20" fill="currentColor" style="width:1rem;height:1rem;margin-right:0.35rem;vertical-align:middle"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                <strong>Drag & Drop to Customize:</strong> Drag widgets from the library below into the dashboard preview. Your layout saves automatically.
            </div>

            {{-- Two-column layout: Widget Library | Dashboard Preview --}}
            <div style="display:grid;grid-template-columns:220px 1fr;gap:1.25rem;align-items:start;">

                {{-- ── Widget Library ── --}}
                <div class="fh-setting-row" style="display:block;padding:0;position:sticky;top:1rem;">
                    <div style="padding:0.75rem 1rem;border-bottom:1px solid #e5e7eb;">
                        <h6 style="font-weight:600;color:#111827;margin:0;font-size:0.875rem;">
                            <svg viewBox="0 0 20 20" fill="currentColor" style="width:0.9rem;height:0.9rem;margin-right:0.35rem;vertical-align:middle;color:#6366f1"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM14 11a1 1 0 011 1v1h1a1 1 0 110 2h-1v1a1 1 0 11-2 0v-1h-1a1 1 0 110-2h1v-1a1 1 0 011-1z"/></svg>
                            Widget Library
                        </h6>
                    </div>
                    <div style="max-height:65vh;overflow-y:auto;padding:0.5rem;">

                        {{-- Statistics --}}
                        <div style="margin-bottom:0.75rem;">
                            <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#9ca3af;padding:0.25rem 0.5rem;">Statistics</div>
                            <div class="db-widget-item" draggable="true" data-widget-type="total_users"><svg viewBox="0 0 20 20" fill="currentColor" style="color:#3b82f6"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg> Total Users</div>
                            <div class="db-widget-item" draggable="true" data-widget-type="active_users"><svg viewBox="0 0 20 20" fill="currentColor" style="color:#10b981"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg> Active Users</div>
                            <div class="db-widget-item" draggable="true" data-widget-type="total_roles"><svg viewBox="0 0 20 20" fill="currentColor" style="color:#6366f1"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg> Total Roles</div>
                            <div class="db-widget-item" draggable="true" data-widget-type="total_departments"><svg viewBox="0 0 20 20" fill="currentColor" style="color:#f59e0b"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v1h8v-1zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-1a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v1h-3zM4.75 14.094A5.973 5.973 0 004 17v1H1v-1a3 3 0 013.75-2.906z"/></svg> Departments</div>
                            <div class="db-widget-item" draggable="true" data-widget-type="total_permissions"><svg viewBox="0 0 20 20" fill="currentColor" style="color:#ef4444"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg> Permissions</div>
                        </div>

                        {{-- Activity --}}
                        <div style="margin-bottom:0.75rem;">
                            <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#9ca3af;padding:0.25rem 0.5rem;">Activity</div>
                            <div class="db-widget-item" draggable="true" data-widget-type="recent_logins"><svg viewBox="0 0 20 20" fill="currentColor" style="color:#3b82f6"><path fill-rule="evenodd" d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Recent Logins</div>
                            <div class="db-widget-item" draggable="true" data-widget-type="activity_log"><svg viewBox="0 0 20 20" fill="currentColor" style="color:#6366f1"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg> Activity Log</div>
                            <div class="db-widget-item" draggable="true" data-widget-type="pending_requests"><svg viewBox="0 0 20 20" fill="currentColor" style="color:#f59e0b"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/></svg> Pending Requests</div>
                        </div>

                        {{-- System --}}
                        <div style="margin-bottom:0.75rem;">
                            <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#9ca3af;padding:0.25rem 0.5rem;">System</div>
                            <div class="db-widget-item" draggable="true" data-widget-type="system_health"><svg viewBox="0 0 20 20" fill="currentColor" style="color:#ef4444"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/></svg> System Health</div>
                            <div class="db-widget-item" draggable="true" data-widget-type="storage_usage"><svg viewBox="0 0 20 20" fill="currentColor" style="color:#f59e0b"><path d="M3 12v3c0 1.657 3.134 3 7 3s7-1.343 7-3v-3c0 1.657-3.134 3-7 3s-7-1.343-7-3z"/><path d="M3 7v3c0 1.657 3.134 3 7 3s7-1.343 7-3V7c0 1.657-3.134 3-7 3S3 8.657 3 7z"/><path d="M17 5c0 1.657-3.134 3-7 3S3 6.657 3 5s3.134-3 7-3 7 1.343 7 3z"/></svg> Storage Usage</div>
                            <div class="db-widget-item" draggable="true" data-widget-type="last_backup"><svg viewBox="0 0 20 20" fill="currentColor" style="color:#6366f1"><path d="M3 12v3c0 1.657 3.134 3 7 3s7-1.343 7-3v-3c0 1.657-3.134 3-7 3s-7-1.343-7-3z"/><path d="M3 7v3c0 1.657 3.134 3 7 3s7-1.343 7-3V7c0 1.657-3.134 3-7 3S3 8.657 3 7z"/></svg> Last Backup</div>
                        </div>

                        {{-- Server Health — Super Admin Only --}}
                        @can('view server stats')
                        <div style="margin-bottom:0.75rem;">
                            <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#9ca3af;padding:0.25rem 0.5rem;">Server Health</div>
                            <div class="db-widget-item" draggable="true" data-widget-type="cpu_usage"><svg viewBox="0 0 20 20" fill="currentColor" style="color:#6366f1"><path d="M13 7H7v6h6V7z"/><path fill-rule="evenodd" d="M7 2a1 1 0 012 0v1h2V2a1 1 0 112 0v1h2a2 2 0 012 2v2h1a1 1 0 110 2h-1v2h1a1 1 0 110 2h-1v2a2 2 0 01-2 2h-2v1a1 1 0 11-2 0v-1H9v1a1 1 0 11-2 0v-1H5a2 2 0 01-2-2v-2H2a1 1 0 110-2h1V9H2a1 1 0 010-2h1V5a2 2 0 012-2h2V2zM5 5h10v10H5V5z" clip-rule="evenodd"/></svg> CPU Usage</div>
                            <div class="db-widget-item" draggable="true" data-widget-type="memory_usage"><svg viewBox="0 0 20 20" fill="currentColor" style="color:#10b981"><path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H5V5h10v7H8.771z" clip-rule="evenodd"/></svg> Memory Usage</div>
                            <div class="db-widget-item" draggable="true" data-widget-type="uptime"><svg viewBox="0 0 20 20" fill="currentColor" style="color:#3b82f6"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg> Server Uptime</div>
                            <div class="db-widget-item" draggable="true" data-widget-type="database_size"><svg viewBox="0 0 20 20" fill="currentColor" style="color:#f59e0b"><path d="M3 12v3c0 1.657 3.134 3 7 3s7-1.343 7-3v-3c0 1.657-3.134 3-7 3s-7-1.343-7-3z"/><path d="M3 7v3c0 1.657 3.134 3 7 3s7-1.343 7-3V7c0 1.657-3.134 3-7 3S3 8.657 3 7z"/></svg> Database Size</div>
                        </div>
                        @endcan

                        {{-- Personal --}}
                        <div style="margin-bottom:0.75rem;">
                            <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#9ca3af;padding:0.25rem 0.5rem;">Personal</div>
                            <div class="db-widget-item" draggable="true" data-widget-type="my_profile"><svg viewBox="0 0 20 20" fill="currentColor" style="color:#3b82f6"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg> My Profile</div>
                            <div class="db-widget-item" draggable="true" data-widget-type="my_activity"><svg viewBox="0 0 20 20" fill="currentColor" style="color:#6366f1"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg> My Activity</div>
                        </div>

                        {{-- Quick Layouts --}}
                        <div style="border-top:1px solid #e5e7eb;padding-top:0.75rem;margin-top:0.5rem;">
                            <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#9ca3af;padding:0.25rem 0.5rem;margin-bottom:0.5rem;">Quick Layouts</div>
                            <button class="fh-btn fh-btn--ghost db-template-btn" data-template="default" style="width:100%;justify-content:flex-start;margin-bottom:0.35rem;font-size:0.8rem;">
                                <svg viewBox="0 0 20 20" fill="currentColor" style="width:0.8rem;height:0.8rem;margin-right:0.35rem"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                Default (4 Cards)
                            </button>
                            <button class="fh-btn fh-btn--ghost db-template-btn" data-template="dual" style="width:100%;justify-content:flex-start;margin-bottom:0.35rem;font-size:0.8rem;">
                                <svg viewBox="0 0 20 20" fill="currentColor" style="width:0.8rem;height:0.8rem;margin-right:0.35rem"><path d="M2 4a1 1 0 011-1h6a1 1 0 011 1v12a1 1 0 01-1 1H3a1 1 0 01-1-1V4zm9 0a1 1 0 011-1h4a1 1 0 011 1v12a1 1 0 01-1 1h-4a1 1 0 01-1-1V4z"/></svg>
                                Two Columns
                            </button>
                            <button class="fh-btn fh-btn--ghost db-template-btn" data-template="triple" style="width:100%;justify-content:flex-start;font-size:0.8rem;">
                                <svg viewBox="0 0 20 20" fill="currentColor" style="width:0.8rem;height:0.8rem;margin-right:0.35rem"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM14 11a1 1 0 011 1v1h1a1 1 0 110 2h-1v1a1 1 0 11-2 0v-1h-1a1 1 0 110-2h1v-1a1 1 0 011-1z"/></svg>
                                Three Columns
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ── Dashboard Preview ── --}}
                <div>
                    <div class="fh-setting-row" style="display:block;padding:0;">
                        <div style="padding:0.75rem 1rem;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;">
                            <h6 style="font-weight:600;color:#111827;margin:0;font-size:0.875rem;">
                                <svg viewBox="0 0 20 20" fill="currentColor" style="width:0.9rem;height:0.9rem;margin-right:0.35rem;vertical-align:middle;color:#6366f1"><path d="M2 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1H3a1 1 0 01-1-1V4zM8 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1H9a1 1 0 01-1-1V4zM15 3a1 1 0 00-1 1v12a1 1 0 001 1h2a1 1 0 001-1V4a1 1 0 00-1-1h-2z"/></svg>
                                Dashboard Preview
                            </h6>
                            <label style="display:flex;align-items:center;gap:0.4rem;font-size:0.8rem;color:#6b7280;cursor:pointer;">
                                <input type="checkbox" id="enableGridLines" checked style="width:0.9rem;height:0.9rem;">
                                Show Grid
                            </label>
                        </div>

                        {{-- GridStack drop zone --}}
                        <div style="padding:1rem;min-height:500px;background:#f9fafb;">
                            <div class="grid-stack" id="dashboardGrid" style="min-height:460px;">
                                {{-- Widgets dropped here dynamically --}}
                            </div>
                        </div>

                        {{-- Footer stats --}}
                        <div style="padding:0.75rem 1rem;border-top:1px solid #e5e7eb;display:grid;grid-template-columns:repeat(4,1fr);gap:0.5rem;text-align:center;">
                            <div><span style="font-size:0.75rem;color:#9ca3af;">Widgets</span><br><strong id="widgetCount" style="color:#111827;">0</strong></div>
                            <div><span style="font-size:0.75rem;color:#9ca3af;">Layout</span><br><strong id="layoutType" style="color:#111827;">Custom</strong></div>
                            <div><span style="font-size:0.75rem;color:#9ca3af;">Last Saved</span><br><strong id="lastSaved" style="color:#111827;">Never</strong></div>
                            <div>
                                <button class="fh-btn" style="background:#10b981;color:#fff;border:none;font-size:0.8rem;padding:0.35rem 0.75rem;" id="saveLayoutBtn">
                                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:0.8rem;height:0.8rem;margin-right:0.25rem"><path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h5a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2h5v5.586l-1.293-1.293z"/></svg>
                                    Save Now
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Saved Profiles --}}
                    <div class="fh-setting-row" style="display:block;margin-top:1rem;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem;">
                            <h6 style="font-weight:600;color:#111827;margin:0;font-size:0.875rem;">
                                <svg viewBox="0 0 20 20" fill="currentColor" style="width:0.9rem;height:0.9rem;margin-right:0.35rem;vertical-align:middle;color:#6366f1"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                                My Dashboard Profiles
                            </h6>
                            <button class="fh-btn fh-btn--ghost" id="createProfileBtn" style="font-size:0.8rem;padding:0.35rem 0.75rem;">
                                <svg viewBox="0 0 20 20" fill="currentColor" style="width:0.8rem;height:0.8rem;margin-right:0.25rem"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                                New Profile
                            </button>
                        </div>
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:0.75rem;" id="profilesList">
                            <div class="fh-profile-card">
                                <div style="font-weight:600;color:#111827;margin-bottom:0.25rem;font-size:0.9rem;">Default Dashboard</div>
                                <div style="font-size:0.75rem;color:#9ca3af;margin-bottom:0.75rem;">4 widgets · Last modified: Today</div>
                                <div style="display:flex;gap:0.35rem;">
                                    <button class="fh-btn fh-btn--ghost" style="flex:1;font-size:0.75rem;padding:0.25rem;">
                                        <svg viewBox="0 0 20 20" fill="currentColor" style="width:0.75rem;height:0.75rem;margin-right:0.2rem"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/></svg>Load
                                    </button>
                                    <button class="fh-btn fh-btn--ghost" style="flex:1;font-size:0.75rem;padding:0.25rem;">
                                        <svg viewBox="0 0 20 20" fill="currentColor" style="width:0.75rem;height:0.75rem;margin-right:0.2rem"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>Edit
                                    </button>
                                    <button class="fh-btn" style="background:#fee2e2;color:#dc2626;border:none;padding:0.25rem 0.5rem;font-size:0.75rem;">
                                        <svg viewBox="0 0 20 20" fill="currentColor" style="width:0.75rem;height:0.75rem"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>{{-- /grid --}}

            <style>
            /* Widget library items */
            .db-widget-item {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 0.75rem;
                margin-bottom: 0.3rem;
                background: rgba(255,255,255,0.6);
                border: 1px solid #e5e7eb;
                border-radius: 0.5rem;
                cursor: grab;
                font-size: 0.82rem;
                color: #374151;
                transition: all 0.15s ease;
            }
            .db-widget-item:hover {
                background: rgba(99,102,241,0.07);
                border-color: #a5b4fc;
                transform: translateX(3px);
            }
            .db-widget-item:active { cursor: grabbing; }
            .db-widget-item svg { width: 1rem; height: 1rem; flex-shrink: 0; }

            /* GridStack */
            .grid-stack {
                background: repeating-linear-gradient(0deg,transparent,transparent 79px,#e5e7eb 79px,#e5e7eb 80px),
                            repeating-linear-gradient(90deg,transparent,transparent 79px,#e5e7eb 79px,#e5e7eb 80px);
                border-radius: 0.5rem;
            }
            .grid-stack.no-grid { background: none; }
            .grid-stack-item-content {
                background: white;
                border-radius: 0.75rem;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                overflow: visible; /* must be visible so × button can sit on the edge */
                border: 1px solid #e5e7eb;
                transition: box-shadow 0.2s;
            }
            .grid-stack-item-content:hover {
                box-shadow: 0 4px 16px rgba(0,0,0,0.13);
            }

            /* ── Widget card — content centered ── */
            .widget-card {
                position: relative;
                height: 100%;
                display: flex;
                flex-direction: column;
                text-align: center;
                padding: 20px 16px 0;
            }
            .widget-card .widget-content {
                flex: 1;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }

            /* ── × Remove button — absolute top-right, appears on hover ── */
            .widget-card .widget-action-btn {
                position: absolute;
                top: -8px;
                right: -8px;
                width: 22px;
                height: 22px;
                border-radius: 50%;
                border: 2px solid white;
                background: #fee2e2;
                color: #dc2626;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 10px;
                line-height: 1;
                transition: background 0.15s, transform 0.15s;
                z-index: 20;
                opacity: 0;
                box-shadow: 0 1px 4px rgba(0,0,0,0.15);
            }
            .grid-stack-item-content:hover .widget-action-btn { opacity: 1; }
            .widget-card .widget-action-btn:hover {
                background: #dc2626;
                color: #fff;
                transform: scale(1.2);
            }

            /* ── Widget title — small caps above value ── */
            .widget-card .widget-header {
                width: 100%;
                display: flex;
                justify-content: center;
                align-items: center;
                margin-bottom: 8px;
            }
            .widget-card .widget-title {
                font-size: 0.72rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.06em;
                color: #9ca3af;
            }
            .widget-card .widget-actions { display: none; } /* handled by absolute btn */

            /* ── Widget content — centered ── */
            .widget-card .widget-content {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                flex: 1;
            }
            .widget-card .widget-icon {
                font-size: 1.75rem;
                margin-bottom: 6px;
                opacity: 0.7;
            }
            .widget-card .widget-value {
                font-size: 2.4rem;
                font-weight: 700;
                line-height: 1;
                color: #111827;
            }
            .widget-card .widget-label {
                font-size: 0.78rem;
                color: #9ca3af;
                margin-top: 4px;
            }

            /* Remove button at bottom center */
            .widget-card .widget-remove-btn {
                display: block;
                width: 100%;
                padding: 0.4rem;
                margin-top: auto;
                background: #fee2e2;
                color: #dc2626;
                border: none;
                border-radius: 0 0 0.5rem 0.5rem;
                font-size: 0.7rem;
                font-weight: 600;
                cursor: pointer;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                transition: background 0.15s, color 0.15s;
            }
            .widget-card .widget-remove-btn:hover {
                background: #dc2626;
                color: #fff;
            }

            /* Profile card */
            .fh-profile-card {
                padding: 0.875rem;
                background: rgba(255,255,255,0.6);
                border: 1px solid #e5e7eb;
                border-radius: 0.75rem;
                transition: all 0.15s ease;
            }
            .fh-profile-card:hover {
                border-color: #a5b4fc;
                box-shadow: 0 2px 8px rgba(99,102,241,0.1);
            }
            </style>

            <script>
            // Add X button to all widgets
            function addRemoveButtons() {
                document.querySelectorAll('#dashboardGrid .widget-card').forEach(function(card) {
                    if (!card.querySelector('.widget-remove-btn')) {
                        var removeBtn = document.createElement('button');
                        removeBtn.className = 'widget-remove-btn';
                        removeBtn.innerHTML = '✕';
                        removeBtn.title = 'Remove';
                        removeBtn.style.cssText = 'position:absolute;top:4px;right:4px;width:20px;height:20px;padding:0;background:#fee2e2;color:#dc2626;border:none;border-radius:50%;cursor:pointer;font-size:12px;line-height:1;z-index:10;';
                        removeBtn.onclick = function() {
                            var item = card.closest('.grid-stack-item');
                            if (item && typeof dashboardBuilder !== 'undefined') {
                                dashboardBuilder.removeWidget(item.dataset.widgetId);
                            }
                        };
                        card.appendChild(removeBtn);
                    }
                });
            }

            // Make widget library items draggable
            function setupDraggable() {
                document.querySelectorAll('.db-widget-item').forEach(function(item) {
                    item.setAttribute('draggable', 'true');
                    item.addEventListener('dragstart', function(e) {
                        e.dataTransfer.setData('widgetType', this.dataset.widgetType);
                        this.classList.add('dragging');
                    });
                    item.addEventListener('dragend', function() {
                        this.classList.remove('dragging');
                    });
                });
            }

            // Setup drop zone on dashboard grid
            function setupDropZone() {
                var gridElement = document.getElementById('dashboardGrid');
                if (!gridElement) return;

                gridElement.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'copy';
                    gridElement.classList.add('drag-over');
                });

                gridElement.addEventListener('dragleave', function() {
                    gridElement.classList.remove('drag-over');
                });

                gridElement.addEventListener('drop', function(e) {
                    e.preventDefault();
                    gridElement.classList.remove('drag-over');
                    var widgetType = e.dataTransfer.getData('widgetType');
                    console.log('Dropped widget type:', widgetType);
                    if (widgetType && typeof dashboardBuilder !== 'undefined') {
                        try {
                            dashboardBuilder.addWidget(widgetType);
                        } catch(err) {
                            console.error('Error adding widget:', err);
                        }
                    }
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    addRemoveButtons();
                    setupDraggable();
                    setupDropZone();
                }, 1000);
            });

            // Also watch for dynamic widget additions
            document.addEventListener('DOMContentLoaded', function() {
                var observer = new MutationObserver(function(mutations) {
                    addRemoveButtons();
                });
                var gridElement = document.getElementById('dashboardGrid');
                if (gridElement) {
                    observer.observe(gridElement, { childList: true, subtree: true });
                }
            });
            </script>

            <script>
            // Dashboard Profiles functionality
            function loadProfiles() {
                fetch('{{ route("dashboard.profiles.get") }}')
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        var list = document.getElementById('profilesList');
                        if (data.profiles && data.profiles.length > 0) {
                            list.innerHTML = data.profiles.map(function(p) {
                                return '<div class="fh-profile-card" data-profile-id="' + p.id + '">' +
                                    '<div style="font-weight:600;color:#111827;margin-bottom:0.25rem;font-size:0.9rem;">' + p.name + '</div>' +
                                    '<div style="font-size:0.75rem;color:#9ca3af;margin-bottom:0.75rem;">' + (p.widget_count || 0) + ' widgets' + (p.is_default ? ' · Default' : '') + '</div>' +
                                    '<div style="display:flex;gap:0.35rem;">' +
                                    '<button class="fh-btn fh-btn--ghost load-profile-btn" style="flex:1;font-size:0.75rem;padding:0.25rem;" data-id="' + p.id + '">Load</button>' +
                                    '<button class="fh-btn fh-btn--ghost edit-profile-btn" style="flex:1;font-size:0.75rem;padding:0.25rem;" data-id="' + p.id + '" data-name="' + p.name + '">Edit</button>' +
                                    (p.is_default ? '' : '<button class="fh-btn delete-profile-btn" style="background:#fee2e2;color:#dc2626;border:none;padding:0.25rem 0.5rem;font-size:0.75rem;" data-id="' + p.id + '">✕</button>') +
                                    '</div></div>';
                            }).join('');
                            // Add event listeners
                            list.querySelectorAll('.load-profile-btn').forEach(function(btn) {
                                btn.onclick = function() { loadProfile(this.dataset.id); };
                            });
                            list.querySelectorAll('.edit-profile-btn').forEach(function(btn) {
                                btn.onclick = function() { editProfile(this.dataset.id, this.dataset.name); };
                            });
                            list.querySelectorAll('.delete-profile-btn').forEach(function(btn) {
                                btn.onclick = function() { deleteProfile(this.dataset.id); };
                            });
                        } else {
                            list.innerHTML = '<div class="fh-profile-card"><div style="color:#6b7280;font-size:0.875rem;">No profiles yet. Create one!</div></div>';
                        }
                    })
                    .catch(function() {});
            }

            function loadProfile(profileId) {
                fetch('{{ route("dashboard.profiles.load", ["id" => ":id"]) }}'.replace(':id', profileId))
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.layout && typeof dashboardBuilder !== 'undefined') {
                            dashboardBuilder.grid.removeAll();
                            data.layout.forEach(function(item) {
                                dashboardBuilder.addWidget(item.widgetType);
                            });
                            Swal.fire('Success', 'Profile loaded!', 'success');
                        }
                    });
            }

            function editProfile(profileId, currentName) {
                Swal.fire({
                    title: 'Edit Profile',
                    input: 'text',
                    inputValue: currentName,
                    showCancelButton: true,
                    confirmButtonText: 'Save',
                    cancelButtonText: 'Cancel'
                }).then(function(result) {
                    if (result.isConfirmed && result.value) {
                        fetch('{{ route("dashboard.profiles.update", ["id" => ":id"]) }}'.replace(':id', profileId), {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ name: result.value })
                        }).then(function(r) { return r.json(); })
                        .then(function() {
                            Swal.fire('Success', 'Profile updated!', 'success');
                            loadProfiles();
                        });
                    }
                });
            }

            function deleteProfile(profileId) {
                Swal.fire({
                    title: 'Delete Profile?',
                    text: 'This cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    cancelButtonText: 'Cancel'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        fetch('/dashboard/profiles/' + profileId, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        }).then(function(r) { return r.json(); })
                        .then(function() {
                            Swal.fire('Deleted', 'Profile deleted!', 'success');
                            loadProfiles();
                        });
                    }
                });
            }

            // Create new profile
            document.getElementById('createProfileBtn').addEventListener('click', function() {
                Swal.fire({
                    title: 'New Profile',
                    input: 'text',
                    inputPlaceholder: 'Profile name',
                    showCancelButton: true,
                    confirmButtonText: 'Create',
                    cancelButtonText: 'Cancel'
                }).then(function(result) {
                    if (result.isConfirmed && result.value) {
                        var layout = [];
                        if (typeof dashboardBuilder !== 'undefined') {
                            layout = dashboardBuilder.grid.save();
                        }
                        fetch('{{ route("dashboard.profiles.create") }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ name: result.value, layout: layout })
                        }).then(function(r) { return r.json(); })
                        .then(function() {
                            Swal.fire('Success', 'Profile created!', 'success');
                            loadProfiles();
                        });
                    }
                });
            });

            // Templates button
            document.getElementById('dashboardTemplateBtn').addEventListener('click', function() {
                Swal.fire({
                    title: 'Select Template',
                    html: '<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;">' +
                        '<button class="template-btn swal2-styled" data-template="default" style="padding:15px;border:1px solid #e5e7eb;border-radius:8px;background:#f9fafb;cursor:pointer;">' +
                        '<div style="font-weight:600;">Default</div><div style="font-size:12px;color:#6b7280;">4 widgets</div></button>' +
                        '<button class="template-btn swal2-styled" data-template="single" style="padding:15px;border:1px solid #e5e7eb;border-radius:8px;background:#f9fafb;cursor:pointer;">' +
                        '<div style="font-weight:600;">Single</div><div style="font-size:12px;color:#6b7280;">3 widgets</div></button>' +
                        '<button class="template-btn swal2-styled" data-template="dual" style="padding:15px;border:1px solid #e5e7eb;border-radius:8px;background:#f9fafb;cursor:pointer;">' +
                        '<div style="font-weight:600;">Dual</div><div style="font-size:12px;color:#6b7280;">6 widgets</div></button>' +
                        '<button class="template-btn swal2-styled" data-template="triple" style="padding:15px;border:1px solid #e5e7eb;border-radius:8px;background:#f9fafb;cursor:pointer;grid-column:span 3;">' +
                        '<div style="font-weight:600;">Triple</div><div style="font-size:12px;color:#6b7280;">8 widgets</div></button>' +
                        '</div>',
                    showCancelButton: false,
                    showConfirmButton: false
                });
                // Add click handlers for template buttons
                document.querySelectorAll('.template-btn').forEach(function(btn) {
                    btn.onclick = function() {
                        var template = this.dataset.template;
                        if (typeof dashboardBuilder !== 'undefined') {
                            dashboardBuilder.applyTemplate(template);
                            // Add remove buttons to new widgets
                            setTimeout(addRemoveButtons, 500);
                        }
                        Swal.close();
                    };
                });
            });

            // Load profiles on page load
            loadProfiles();
            </script>
        </div>

        {{-- DATABASE TAB --}}
        <div id="tab-database" class="tab-panel" style="display:none;">
            <div class="fh-settings-grid">
                <div class="fh-setting-row" style="display:block;">
                    <h3 style="font-size:1.25rem;font-weight:600;color:#111827;margin-bottom:1rem;">Database Backup</h3>
                    
                    <form method="POST" action="{{ route('database.backup.run') }}" style="margin-bottom:1.5rem;">
                        @csrf
                        <button type="submit" class="fh-btn" style="background:#10b981;color:#fff;border:none;">
                            <svg viewBox="0 0 20 20" fill="currentColor" style="width:1rem;height:1rem;margin-right:0.25rem"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586l-.707.707A1 1 0 009 14h6a1 1 0 001.707-.707L11 12.586V7z" clip-rule="evenodd"/></svg>
                            Create Backup
                        </button>
                    </form>

                    <h4 style="font-size:1rem;font-weight:600;margin-bottom:0.75rem;">Backup History</h4>
                    <table style="width:100%;border-collapse:collapse;">
                        <thead>
                            <tr style="background:#f3f4f6;">
                                <th style="padding:0.75rem;text-align:left;border-bottom:1px solid #e5e7eb;">#</th>
                                <th style="padding:0.75rem;text-align:left;border-bottom:1px solid #e5e7eb;">File</th>
                                <th style="padding:0.75rem;text-align:left;border-bottom:1px solid #e5e7eb;">Size</th>
                                <th style="padding:0.75rem;text-align:left;border-bottom:1px solid #e5e7eb;">Date</th>
                                <th style="padding:0.75rem;text-align:center;border-bottom:1px solid #e5e7eb;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($backups ?? [] as $backup)
                            <tr>
                                <td style="padding:0.75rem;border-bottom:1px solid #e5e7eb;">{{ $loop->iteration }}</td>
                                <td style="padding:0.75rem;border-bottom:1px solid #e5e7eb;"><small>{{ $backup['name'] }}</small></td>
                                <td style="padding:0.75rem;border-bottom:1px solid #e5e7eb;">{{ $backup['size'] }}</td>
                                <td style="padding:0.75rem;border-bottom:1px solid #e5e7eb;"><small>{{ $backup['time'] }}</small></td>
                                <td style="padding:0.75rem;border-bottom:1px solid #e5e7eb;text-align:center;">
                                    <a href="{{ route('database.backup.download', $backup['name']) }}" class="fh-btn" style="background:#10b981;color:#fff;border:none;padding:0.25rem 0.5rem;font-size:0.75rem;">Download</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" style="padding:1.5rem;text-align:center;color:#6b7280;">No backups found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- BACKUP TAB --}}
        <div id="tab-backup" class="tab-panel" style="display:none;">
            <div class="fh-settings-grid" style="gap: 1.5rem;">
                
                {{-- CREATE BACKUP --}}
                <div class="fh-setting-row" style="display:block;grid-column:1/-1;">
                    <h3 style="font-size:1.25rem;font-weight:600;color:#111827;padding-bottom:0.5rem;border-bottom:1px solid #e5e7eb;margin-bottom:1rem;">
                        <svg viewBox="0 0 20 20" fill="currentColor" style="width:1.25rem;height:1.25rem;margin-right:0.5rem;vertical-align:middle;color:#10b981"><path fill-rule="evenodd" d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z" clip-rule="evenodd"/></svg>
                        Create Backup
                    </h3>
                    
                    <form method="POST" action="{{ route('website.backup.run') }}">
                        @csrf
                        
                        <div style="margin-bottom:1rem;">
                            <label style="font-weight:600;color:#374151;display:block;margin-bottom:0.5rem;">Select Folders to Backup</label>
                            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:0.5rem;">
                                @foreach(['app', 'bootstrap', 'config', 'database', 'lang', 'module-sample', 'modules', 'public', 'resources', 'routes', 'tests'] as $folder)
                                <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                                    <input type="checkbox" name="folders[]" value="{{ $folder }}" checked style="width:1rem;height:1rem;">
                                    <span>{{ $folder }}</span>
                                </label>
                                @endforeach
                            </div>
                            @error('folders')
                                <small style="color:#ef4444;display:block;margin-top:0.25rem;">The folders field is required.</small>
                            @enderror
                        </div>

                        <div style="margin-bottom:1rem;">
                            <label style="font-weight:600;color:#374151;display:block;margin-bottom:0.5rem;">Note (optional)</label>
                            <input type="text" name="note" class="fh-setting-row__input" style="width:100%;max-width:400px;" placeholder="Backup description...">
                        </div>

                        <button type="submit" class="fh-btn" style="background:#10b981;color:#fff;border:none;">
                            <svg viewBox="0 0 20 20" fill="currentColor" style="width:1rem;height:1rem;margin-right:0.25rem"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586l-.707.707A1 1 0 009 14h6a1 1 0 001.707-.707L11 12.586V7z" clip-rule="evenodd"/></svg>
                            Create Backup
                        </button>
                    </form>
                </div>

                {{-- RESTORE BACKUP --}}
                <div class="fh-setting-row" style="display:block;grid-column:1/-1;">
                    <h3 style="font-size:1.25rem;font-weight:600;color:#111827;padding-bottom:0.5rem;border-bottom:1px solid #e5e7eb;margin-bottom:1rem;">
                        <svg viewBox="0 0 20 20" fill="currentColor" style="width:1.25rem;height:1.25rem;margin-right:0.5rem;vertical-align:middle;color:#f59e0b"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                        Restore from Upload
                    </h3>
                    
                    <form method="POST" action="{{ route('website.backup.restore.upload') }}" enctype="multipart/form-data" style="display:flex;gap:0.5rem;align-items:center;flex-wrap:wrap;">
                        @csrf
                        <input type="file" name="backup_file" accept=".zip" class="fh-setting-row__input" style="width:auto;">
                        <select name="mode" class="fh-setting-row__input" style="width:auto;">
                            <option value="full">Full Restore</option>
                            <option value="newer">Restore Newer Only</option>
                        </select>
                        <button type="submit" class="fh-btn" style="background:#f59e0b;color:#fff;border:none;">
                            <svg viewBox="0 0 20 20" fill="currentColor" style="width:1rem;height:1rem;margin-right:0.25rem"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                            Restore
                        </button>
                    </form>
                </div>

                {{-- BACKUP LIST --}}
                <div class="fh-setting-row" style="display:block;grid-column:1/-1;">
                    <h3 style="font-size:1.25rem;font-weight:600;color:#111827;padding-bottom:0.5rem;border-bottom:1px solid #e5e7eb;margin-bottom:1rem;">
                        <svg viewBox="0 0 20 20" fill="currentColor" style="width:1.25rem;height:1.25rem;margin-right:0.5rem;vertical-align:middle;color:#6b7280"><path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm14 1a1 1 0 11-2 0 1 1 0 012 0zM2 13a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2v-2zm14 1a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd"/></svg>
                        Backup List
                    </h3>
                    
                    <table style="width:100%;border-collapse:collapse;">
                        <thead>
                            <tr style="background:#f3f4f6;">
                                <th style="padding:0.75rem;text-align:left;border-bottom:1px solid #e5e7eb;">#</th>
                                <th style="padding:0.75rem;text-align:left;border-bottom:1px solid #e5e7eb;">File</th>
                                <th style="padding:0.75rem;text-align:left;border-bottom:1px solid #e5e7eb;">Size</th>
                                <th style="padding:0.75rem;text-align:left;border-bottom:1px solid #e5e7eb;">Date</th>
                                <th style="padding:0.75rem;text-align:center;border-bottom:1px solid #e5e7eb;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($websiteBackups ?? [] as $backup)
                            <tr>
                                <td style="padding:0.75rem;border-bottom:1px solid #e5e7eb;">{{ $loop->iteration }}</td>
                                <td style="padding:0.75rem;border-bottom:1px solid #e5e7eb;">
                                    <small>{{ $backup['name'] }}</small>
                                    @if(isset($backup['note']) && $backup['note'])
                                        <br><small style="color:#6b7280;">{{ $backup['note'] }}</small>
                                    @endif
                                </td>
                                <td style="padding:0.75rem;border-bottom:1px solid #e5e7eb;">{{ $backup['size'] }}</td>
                                <td style="padding:0.75rem;border-bottom:1px solid #e5e7eb;"><small>{{ $backup['time'] }}</small></td>
                                <td style="padding:0.75rem;border-bottom:1px solid #e5e7eb;text-align:center;">
                                    <a href="{{ route('website.backup.download', $backup['name']) }}" class="fh-btn" style="background:#10b981;color:#fff;border:none;padding:0.25rem 0.5rem;font-size:0.75rem;margin-right:0.25rem;">Download</a>
                                    <button type="button" class="fh-btn" style="background:#ef4444;color:#fff;border:none;padding:0.25rem 0.5rem;font-size:0.75rem;margin-right:0.25rem;" onclick="deleteBackup('{{ route('website.backup.delete', $backup['name']) }}', '{{ $backup['name'] }}')">Delete</button>
                                    <button type="button" class="fh-btn" style="background:#f59e0b;color:#fff;border:none;padding:0.25rem 0.5rem;font-size:0.75rem;" onclick="restoreBackup('{{ route('website.backup.restore.local') }}', '{{ $backup['name'] }}')">Restore</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" style="padding:1.5rem;text-align:center;color:#6b7280;">No backups found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- MAIL TAB --}}
        <div id="tab-mail" class="tab-panel" style="display:none;">
            <div class="fh-settings-grid">
                <form action="{{ route('settings.update') }}" method="POST" style="grid-column:1/-1;">
                    @csrf

                    <div style="grid-column: 1 / -1;">
                        <h3 style="font-size:1.25rem;font-weight:600;color:#111827;padding-bottom:0.5rem;border-bottom:1px solid #e5e7eb;">SMTP Mail Settings</h3>
                    </div>

                    <div class="fh-setting-row" style="display:grid;grid-template-columns:180px 1fr 200px;gap:1rem;align-items:center;">
                        <label style="font-weight:600;color:#374151;">Mail Host</label>
                        <input type="text" name="settings[mail_host]" class="fh-setting-row__input" style="width:100%;" value="{{ old('settings.mail_host', $settings['mail_host'] ?? '') }}" placeholder="smtp.mailersend.net">
                        <small style="color:#9ca3af;">SMTP server hostname</small>
                    </div>

                    <div class="fh-setting-row" style="display:grid;grid-template-columns:180px 1fr 200px;gap:1rem;align-items:center;">
                        <label style="font-weight:600;color:#374151;">Mail Port</label>
                        <input type="number" name="settings[mail_port]" class="fh-setting-row__input" style="width:100%;" value="{{ old('settings.mail_port', $settings['mail_port'] ?? '587') }}" placeholder="587">
                        <small style="color:#9ca3af;">Common: 587 (TLS), 465 (SSL)</small>
                    </div>

                    <div class="fh-setting-row" style="display:grid;grid-template-columns:180px 1fr 200px;gap:1rem;align-items:center;">
                        <label style="font-weight:600;color:#374151;">Mail Username</label>
                        <input type="text" name="settings[mail_username]" class="fh-setting-row__input" style="width:100%;" value="{{ old('settings.mail_username', $settings['mail_username'] ?? '') }}" placeholder="">
                        <small style="color:#9ca3af;">SMTP username</small>
                    </div>

                    <div class="fh-setting-row" style="display:grid;grid-template-columns:180px 1fr 200px;gap:1rem;align-items:center;">
                        <label style="font-weight:600;color:#374151;">Mail Password</label>
                        <input type="password" name="settings[mail_password]" class="fh-setting-row__input" style="width:100%;" value="{{ old('settings.mail_password', $settings['mail_password'] ?? '') }}" placeholder="">
                        <small style="color:#9ca3af;">SMTP password</small>
                    </div>

                    <div class="fh-setting-row" style="display:grid;grid-template-columns:180px 1fr 200px;gap:1rem;align-items:center;">
                        <label style="font-weight:600;color:#374151;">Mail Encryption</label>
                        <input type="text" name="settings[mail_encryption]" class="fh-setting-row__input" style="width:100%;" value="{{ old('settings.mail_encryption', $settings['mail_encryption'] ?? 'tls') }}" placeholder="tls">
                        <small style="color:#9ca3af;">tls or ssl</small>
                    </div>

                    <div class="fh-setting-row" style="display:grid;grid-template-columns:180px 1fr 200px;gap:1rem;align-items:center;">
                        <label style="font-weight:600;color:#374151;">From Address</label>
                        <input type="email" name="settings[mail_from_address]" class="fh-setting-row__input" style="width:100%;" value="{{ old('settings.mail_from_address', $settings['mail_from_address'] ?? '') }}" placeholder="noreply@example.com">
                        <small style="color:#9ca3af;">&#123;&#123; config('mail.from.address') &#125;&#125;</small>
                    </div>

                    <div class="fh-setting-row" style="display:grid;grid-template-columns:180px 1fr 200px;gap:1rem;align-items:center;">
                        <label style="font-weight:600;color:#374151;">From Name</label>
                        <input type="text" name="settings[mail_from_name]" class="fh-setting-row__input" style="width:100%;" value="{{ old('settings.mail_from_name', $settings['mail_from_name'] ?? '') }}" placeholder="{{ $settings['site_name'] ?? 'wCore' }} Webmasters">
                        <small style="color:#9ca3af;">&#123;&#123; config('mail.from.name') &#125;&#125;</small>
                    </div>

                    <div style="margin-top:1.5rem;padding-top:1rem;border-top:1px solid #e5e7eb;">
                        <button type="submit" class="fh-btn" style="background:#2563eb;color:#fff;border:none;">
                            <svg viewBox="0 0 20 20" fill="currentColor" style="width:1rem;height:1rem;margin-right:0.25rem"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Save Mail Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- UPDATE TAB --}}
        <div id="tab-update" class="tab-panel" style="display:none;">
            <div class="fh-settings-grid">
                <div class="fh-setting-row" style="display:block;">
                    <h3 style="font-size:1.25rem;font-weight:600;color:#111827;margin-bottom:1rem;">Update / Rollback</h3>
                    <p style="color:#6b7280;margin-bottom:1rem;">Update system or rollback to previous versions.</p>
                    
                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:0.5rem;padding:1.5rem;margin-bottom:1.5rem;">
                        <div style="display:flex;align-items:center;justify-content:space-between;">
                            <div>
                                <h4 style="font-weight:600;color:#111827;margin-bottom:0.25rem;">Current Version</h4>
                                <p style="color:#6b7280;font-size:1.5rem;font-weight:700;color:#2563eb;">v1.0.0</p>
                            </div>
                            <div style="text-align:right;">
                                <span class="fh-badge" style="background:#dcfce7;color:#166534;">Up to date</span>
                            </div>
                        </div>
                    </div>

                    <button class="fh-btn" style="background:#f59e0b;color:#fff;border:none;" onclick="checkForUpdates()">
                        <svg viewBox="0 0 20 20" fill="currentColor" style="width:1rem;height:1rem;margin-right:0.25rem"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/></svg>
                        Check for Updates
                    </button>

                    <div id="update-result" style="margin-top:1rem;"></div>
                </div>
            </div>
        </div>

    </div>

</div>

@push('styles')
@include('filehosting::_partials.styles')
@endpush

<style>
.tab-panel { animation: fadeIn 0.2s ease; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>

<script>
function switchSettingsTab(tabName) {
    var panels = document.querySelectorAll('.tab-panel');
    panels.forEach(function(panel) {
        panel.style.display = 'none';
    });
    
    var selectedPanel = document.getElementById('tab-' + tabName);
    if (selectedPanel) {
        selectedPanel.style.display = 'block';
    }
    
    var tabs = document.querySelectorAll('#settingsTab .fh-tab');
    tabs.forEach(function(tab) {
        if (tab.dataset.tab === tabName) {
            tab.classList.add('fh-tab--active');
        } else {
            tab.classList.remove('fh-tab--active');
        }
    });
    
    localStorage.setItem('settingsTab', tabName);
}

document.addEventListener('DOMContentLoaded', function() {
    var savedTab = localStorage.getItem('settingsTab') || 'general';
    switchSettingsTab(savedTab);
});

function deleteBackup(url, name) {
    Swal.fire({
        title: 'Delete Backup',
        text: 'Are you sure you want to delete "' + name + '"? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then(function(result) {
        if (result.isConfirmed) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            var csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = document.querySelector('meta[name="csrf-token"]').content;
            form.appendChild(csrf);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function restoreBackup(url, name) {
    Swal.fire({
        title: 'Restore Backup',
        text: 'Are you sure you want to restore from "' + name + '"? Current data will be overwritten.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, restore!',
        cancelButtonText: 'Cancel'
    }).then(function(result) {
        if (result.isConfirmed) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            var csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = document.querySelector('meta[name="csrf-token"]').content;
            form.appendChild(csrf);
            var backupName = document.createElement('input');
            backupName.type = 'hidden';
            backupName.name = 'backup_name';
            backupName.value = name;
            form.appendChild(backupName);
            var mode = document.createElement('input');
            mode.type = 'hidden';
            mode.name = 'mode';
            mode.value = 'full';
            form.appendChild(mode);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function checkForUpdates() {
    var resultDiv = document.getElementById('update-result');
    resultDiv.innerHTML = '<div style="color:#6b7280;">Checking for updates...</div>';
    
    fetch('https://api.github.com/repos/qubexs/wcore/contents/update')
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            return response.json();
        })
        .then(files => {
            if (Array.isArray(files) && files.length > 0) {
                var latest = files[files.length - 1].name;
                resultDiv.innerHTML = '<div style="background:#fef3c7;border:1px solid #f59e0b;border-radius:0.5rem;padding:1rem;margin-top:1rem;">' +
                    '<p style="color:#92400e;font-weight:600;">New update available: ' + latest + '</p>' +
                    '<p style="color:#92400e;font-size:0.875rem;">Visit <a href="https://github.com/qubexs/wcore/releases" target="_blank" style="color:#2563eb;">GitHub Releases</a> to download.</p>' +
                    '</div>';
            } else {
                resultDiv.innerHTML = '<div style="background:#dcfce7;border:1px solid #16a34a;border-radius:0.5rem;padding:1rem;margin-top:1rem;color:#166534;">You are running the latest version.</div>';
            }
        })
        .catch(error => {
            resultDiv.innerHTML = '<div style="background:#fee2e2;border:1px solid #dc2626;border-radius:0.5rem;padding:1rem;margin-top:1rem;color:#dc2626;">Unable to check for updates. Please try again later.</div>';
        });
}
</script>

@endsection