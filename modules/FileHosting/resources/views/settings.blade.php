@extends('layouts.admin')

@section('title', 'File Hosting – Settings')

@section('main-content')
@php
    // Defensive defaults for all required variables
    $settings = $settings ?? [
        'disk' => 'local',
        'thumbnail_disk' => 'local',
        'file' => [
            'versioning_enabled' => true,
            'max_versions' => 5,
            'allowed_extensions' => ['pdf','doc','docx','xls','xlsx','jpg','jpeg','png','gif','zip','rar'],
            'scan_malware' => false,
        ],
        'folder' => [
            'default_structure' => '',
            'share_enabled' => true,
        ],
        'limits' => ['max_folder_depth' => 5, 'max_files_per_folder' => 100, 'max_upload_size_mb' => 10],
        'thumbnails' => ['enabled' => false, 'quality' => 80, 'driver' => 'gd'],
        'audit' => ['enabled' => false, 'retain_days' => 30],
        'roles' => [],
    ];
    $usageStats = $usageStats ?? ['total_uploads' => 0, 'total_downloads' => 0, 'recent_activity' => 0];
    $maxUpload = $maxUpload ?? 10485760;
@endphp
<div class="fh-container" x-data="fhSettings()" style="padding-top: 3.5rem;">

    <div class="fh-header">
        <div class="fh-header__left">
            <a href="{{ route('modules.index') }}" class="fh-back-link">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/></svg>
                Back
            </a>
            <h1 class="fh-page-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                Settings
            </h1>
        </div>
    </div>

    {{-- ================================================================
         Usage Stats
    ================================================================ --}}
    <div class="fh-stats">
        <div class="fh-stat">
            <div class="fh-stat__value">{{ number_format($usageStats['total_uploads'] ?? 0) }}</div>
            <div class="fh-stat__label">Total Uploads</div>
        </div>
        <div class="fh-stat">
            <div class="fh-stat__value">{{ number_format($usageStats['total_downloads'] ?? 0) }}</div>
            <div class="fh-stat__label">Total Downloads</div>
        </div>
        <div class="fh-stat">
            <div class="fh-stat__value">{{ number_format($usageStats['recent_activity'] ?? 0) }}</div>
            <div class="fh-stat__label">Activity (7d)</div>
        </div>
        <div class="fh-stat">
            <div class="fh-stat__value">{{ isset($maxUpload) && $maxUpload >= 1073741824 ? round($maxUpload/1073741824,1).'GB' : round(($maxUpload ?? 1048576)/1048576).'MB' }}</div>
            <div class="fh-stat__label">Max Upload</div>
        </div>
    </div>

    {{-- ================================================================
         Settings Tabs
    ================================================================ --}}
    <div class="fh-tabs">
        <button @click="tab='files'"    :class="tab==='files'    ? 'fh-tab--active' : ''" class="fh-tab">Files</button>
        <button @click="tab='folders'"  :class="tab==='folders'  ? 'fh-tab--active' : ''" class="fh-tab">Folders</button>
        <button @click="tab='general'"  :class="tab==='general'  ? 'fh-tab--active' : ''" class="fh-tab">General</button>
        <button @click="tab='limits'"   :class="tab==='limits'   ? 'fh-tab--active' : ''" class="fh-tab">Limits</button>
        <button @click="tab='thumbs'"   :class="tab==='thumbs'   ? 'fh-tab--active' : ''" class="fh-tab">Thumbnails</button>
        <button @click="tab='audit'"    :class="tab==='audit'    ? 'fh-tab--active' : ''" class="fh-tab">Audit</button>
        <button @click="tab='roles'"    :class="tab==='roles'    ? 'fh-tab--active' : ''" class="fh-tab">Roles</button>
    </div>

    <div class="fh-tab-content">

        {{-- Files --}}
        <div x-show="tab === 'files'">
            <h3 style="margin-bottom: 1rem;">File Management Settings</h3>
            <div class="fh-settings-grid">
                @include('filehosting::_partials.setting_row', [
                    'key' => 'file.versioning_enabled',
                    'label' => 'Enable File Versioning',
                    'value' => $settings['file']['versioning_enabled'] ?? true,
                    'type' => 'toggle',
                ])
                @include('filehosting::_partials.setting_row', [
                    'key' => 'file.max_versions',
                    'label' => 'Max Versions per File',
                    'value' => $settings['file']['max_versions'] ?? 5,
                    'type' => 'number',
                ])
                @php $allowedExt = $settings['file']['allowed_extensions'] ?? []; if (is_string($allowedExt)) { $allowedExt = explode(',', $allowedExt); } @endphp
                @include('filehosting::_partials.setting_row', [
                    'key' => 'file.allowed_extensions',
                    'label' => 'Allowed Extensions',
                    'value' => implode(', ', is_array($allowedExt) ? $allowedExt : ['pdf','doc','docx','xls','xlsx','jpg','jpeg','png','gif','zip','rar']),
                    'type' => 'text',
                ])
                @include('filehosting::_partials.setting_row', [
                    'key' => 'file.scan_malware',
                    'label' => 'Scan Files for Malware',
                    'value' => $settings['file']['scan_malware'] ?? false,
                    'type' => 'toggle',
                ])
            </div>
        </div>

        {{-- Folders --}}
        <div x-show="tab === 'folders'">
            <h3 style="margin-bottom: 1rem;">Folder Management Settings</h3>
            <div class="fh-settings-grid">
                @include('filehosting::_partials.setting_row', [
                    'key' => 'folder.default_structure',
                    'label' => 'Default Folder Structure',
                    'value' => $settings['folder']['default_structure'] ?? '',
                    'type' => 'textarea',
                    'hint' => 'One folder per line',
                ])
                @include('filehosting::_partials.setting_row', [
                    'key' => 'folder.max_depth',
                    'label' => 'Maximum Folder Depth',
                    'value' => $settings['limits']['max_folder_depth'] ?? 5,
                    'type' => 'number',
                ])
                @include('filehosting::_partials.setting_row', [
                    'key' => 'folder.share_enabled',
                    'label' => 'Enable Folder Sharing',
                    'value' => $settings['folder']['share_enabled'] ?? true,
                    'type' => 'toggle',
                ])
            </div>
        </div>

        {{-- General --}}
        <div x-show="tab === 'general'">
            <div class="fh-settings-grid">
                @include('filehosting::_partials.setting_row', [
                    'key' => 'disk',
                    'label' => 'Storage Disk',
                    'value' => $settings['disk'] ?? 'local',
                    'type' => 'text',
                    'hint' => 'Laravel filesystem disk (local, public, s3)',
                ])
                @include('filehosting::_partials.setting_row', [
                    'key' => 'thumbnail_disk',
                    'label' => 'Thumbnail Disk',
                    'value' => $settings['thumbnail_disk'] ?? 'local',
                    'type' => 'text',
                ])
            </div>
        </div>

        {{-- Limits --}}
        <div x-show="tab === 'limits'">
            <div class="fh-settings-grid">
                @include('filehosting::_partials.setting_row', [
                    'key' => 'limits.max_folder_depth',
                    'label' => 'Max Folder Depth',
                    'value' => $settings['limits']['max_folder_depth'] ?? 5,
                    'type' => 'number',
                ])
                @include('filehosting::_partials.setting_row', [
                    'key' => 'limits.max_files_per_folder',
                    'label' => 'Max Files per Folder',
                    'value' => $settings['limits']['max_files_per_folder'] ?? 100,
                    'type' => 'number',
                ])
                @include('filehosting::_partials.setting_row', [
                    'key' => 'limits.max_upload_size_mb',
                    'label' => 'Max Upload Size (MB)',
                    'value' => $settings['limits']['max_upload_size_mb'] ?? 10,
                    'type' => 'number',
                ])
            </div>
        </div>

        {{-- Thumbnails --}}
        <div x-show="tab === 'thumbs'">
            <div class="fh-settings-grid">
                @include('filehosting::_partials.setting_toggle', [
                    'key' => 'thumbnails.enabled',
                    'label' => 'Enable Thumbnails',
                    'value' => $settings['thumbnails']['enabled'] ?? false,
                ])
                @include('filehosting::_partials.setting_row', [
                    'key' => 'thumbnails.quality',
                    'label' => 'JPEG Quality (1–100)',
                    'value' => $settings['thumbnails']['quality'] ?? 80,
                    'type' => 'number',
                ])
                @include('filehosting::_partials.setting_row', [
                    'key' => 'thumbnails.driver',
                    'label' => 'Image Driver',
                    'value' => $settings['thumbnails']['driver'] ?? 'gd',
                    'type' => 'text',
                    'hint' => 'auto | gd | imagick',
                ])
            </div>
        </div>

        {{-- Audit --}}
        <div x-show="tab === 'audit'">
            <div class="fh-settings-grid">
                @include('filehosting::_partials.setting_toggle', [
                    'key' => 'audit.enabled',
                    'label' => 'Enable Audit Logging',
                    'value' => $settings['audit']['enabled'] ?? true,
                ])
                @include('filehosting::_partials.setting_row', [
                    'key' => 'audit.retain_days',
                    'label' => 'Retain Logs (days)',
                    'value' => $settings['audit']['retain_days'] ?? 90,
                    'type' => 'number',
                    'hint' => '0 = keep forever',
                ])
            </div>
        </div>

        {{-- Roles --}}
        <div x-show="tab === 'roles'">
            <form id="rolesForm" method="POST" action="{{ route('filehosting.settings.updateRoles') }}">
                @csrf
                
                @php
                $allPerms = [
                    'folder' => ['create', 'rename', 'move', 'delete', 'delete_own', 'view_all', 'view_department', 'view_assigned', 'manage_permissions', 'create_in_assigned'],
                    'file' => ['upload', 'edit', 'edit_own', 'delete', 'delete_own', 'replace', 'view', 'download', 'move', 'move_own', 'share', 'version_manage', 'upload_in_assigned'],
                    'system' => ['view_logs', 'manage_settings', 'backup_restore', 'index']
                ];
                $roleNames = ['SuperAdmin', 'Admin', 'HR', 'Registrar', 'Employee', 'Doctor', 'Nurse', 'Pharmacist', 'Technician', 'Dietitian', 'Social Worker', 'IT Staff', 'Finance Staff', 'Security Staff', 'Research Officer', 'Medicolegal Officer', 'Infection Control Officer', 'Counselor', 'Unit Supervisor', 'Head of Hospital'];
                @endphp
                
                @foreach($roleNames as $roleName)
                    @if(isset($settings['roles'][$roleName]))
                    <div class="fh-role-block">
                        <div class="fh-role-block__title">{{ $roleName }}</div>
                        <div class="fh-role-block__perms">
                            @foreach($allPerms as $type => $perms)
                            <div class="fh-role-perm-group">
                                <span class="fh-role-perm-type">{{ ucfirst($type) }}</span>
                                @foreach($perms as $perm)
                                    <label style="display: inline-flex; align-items: center; margin: 2px;">
                                        <input type="checkbox" 
                                               name="role_perms[{{ $roleName }}][{{ $type }}][]" 
                                               value="{{ $perm }}"
                                               {{ isset($settings['roles'][$roleName][$type]) && in_array($perm, $settings['roles'][$roleName][$type]) ? 'checked' : '' }}
                                               style="margin-right: 4px;">
                                        <span style="font-size: 0.85rem;">{{ $perm }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endforeach
                
                <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">
                    <i class="fas fa-save"></i> Save Role Permissions
                </button>
            </form>
        </div>

    </div>

    {{-- Save feedback --}}
    <div x-show="saved" x-transition class="fh-toast fh-toast--success" style="position:fixed;top:80px;right:20px;z-index:9999;">✓ Settings saved.</div>
    <div x-show="saveError" x-transition class="fh-toast fh-toast--error" style="position:fixed;top:80px;right:20px;z-index:9999;" x-text="saveError"></div>

    {{-- Cache flush button --}}
    <div style="margin-top:2rem; padding-top:1rem; border-top:1px solid #e5e7eb">
        <button @click="flushCache(); saved = true; setTimeout(() => saved = false, 3000)" class="fh-btn fh-btn--ghost">Flush Settings Cache</button>
    </div>

</div>
@endsection

@push('styles')
@include('filehosting::_partials.styles')
@endpush

@push('scripts')
<script>
function fhSettings() {
    return {
        tab: 'general',
        saved: false,
        saveError: '',

        async saveSetting(key, value) {
            this.saved = false; this.saveError = '';
            const res = await fetch('{{ route('filehosting.settings.update') }}', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({ key, value }),
            });
            if (res.ok) {
                this.saved = true;
                setTimeout(() => this.saved = false, 3000);
            } else {
                const j = await res.json();
                this.saveError = j.message || 'Failed to save.';
                setTimeout(() => this.saveError = '', 5000);
            }
        },

        async flushCache() {
            const res = await fetch('{{ route('filehosting.settings.flush') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
            });
            if (res.ok) { this.saved = true; setTimeout(() => this.saved = false, 3000); }
        },
    };
}
</script>
@endpush
