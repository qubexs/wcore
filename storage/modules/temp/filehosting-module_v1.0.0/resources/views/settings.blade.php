@extends('layouts.admin')

@section('title', 'File Hosting – Settings')

@section('main-content')
<div class="fh-container" x-data="fhSettings()" style="padding-top: 3.5rem;">

    <div class="fh-header">
        <div class="fh-header__left">
            <a href="{{ route('filehosting.index') }}" class="fh-back-link">
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
            <div class="fh-stat__value">{{ number_format($usageStats['total_uploads']) }}</div>
            <div class="fh-stat__label">Total Uploads</div>
        </div>
        <div class="fh-stat">
            <div class="fh-stat__value">{{ number_format($usageStats['total_downloads']) }}</div>
            <div class="fh-stat__label">Total Downloads</div>
        </div>
        <div class="fh-stat">
            <div class="fh-stat__value">{{ number_format($usageStats['recent_activity']) }}</div>
            <div class="fh-stat__label">Activity (7d)</div>
        </div>
        <div class="fh-stat">
            <div class="fh-stat__value">{{ $maxUpload >= 1073741824 ? round($maxUpload/1073741824,1).'GB' : round($maxUpload/1048576).'MB' }}</div>
            <div class="fh-stat__label">Max Upload</div>
        </div>
    </div>

    {{-- ================================================================
         Settings Tabs
    ================================================================ --}}
    <div class="fh-tabs">
        <button @click="tab='general'"  :class="tab==='general'  ? 'fh-tab--active' : ''" class="fh-tab">General</button>
        <button @click="tab='limits'"   :class="tab==='limits'   ? 'fh-tab--active' : ''" class="fh-tab">Limits</button>
        <button @click="tab='thumbs'"   :class="tab==='thumbs'   ? 'fh-tab--active' : ''" class="fh-tab">Thumbnails</button>
        <button @click="tab='audit'"    :class="tab==='audit'    ? 'fh-tab--active' : ''" class="fh-tab">Audit</button>
        <button @click="tab='roles'"    :class="tab==='roles'    ? 'fh-tab--active' : ''" class="fh-tab">Roles</button>
    </div>

    <div class="fh-tab-content">

        {{-- General --}}
        <div x-show="tab === 'general'">
            <div class="fh-settings-grid">
                @include('filehosting::_partials.setting_row', [
                    'key' => 'disk',
                    'label' => 'Storage Disk',
                    'value' => $settings['disk'],
                    'type' => 'text',
                    'hint' => 'Laravel filesystem disk (local, public, s3)',
                ])
                @include('filehosting::_partials.setting_row', [
                    'key' => 'thumbnail_disk',
                    'label' => 'Thumbnail Disk',
                    'value' => $settings['thumbnail_disk'],
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
                    'value' => $settings['limits']['max_folder_depth'],
                    'type' => 'number',
                ])
                @include('filehosting::_partials.setting_row', [
                    'key' => 'limits.max_files_per_folder',
                    'label' => 'Max Files per Folder',
                    'value' => $settings['limits']['max_files_per_folder'],
                    'type' => 'number',
                ])
                @include('filehosting::_partials.setting_row', [
                    'key' => 'limits.max_upload_size_mb',
                    'label' => 'Max Upload Size (MB)',
                    'value' => $settings['limits']['max_upload_size_mb'],
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
                    'value' => $settings['thumbnails']['enabled'],
                ])
                @include('filehosting::_partials.setting_row', [
                    'key' => 'thumbnails.quality',
                    'label' => 'JPEG Quality (1–100)',
                    'value' => $settings['thumbnails']['quality'],
                    'type' => 'number',
                ])
                @include('filehosting::_partials.setting_row', [
                    'key' => 'thumbnails.driver',
                    'label' => 'Image Driver',
                    'value' => $settings['thumbnails']['driver'],
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
                    'value' => $settings['audit']['enabled'],
                ])
                @include('filehosting::_partials.setting_row', [
                    'key' => 'audit.retain_days',
                    'label' => 'Retain Logs (days)',
                    'value' => $settings['audit']['retain_days'],
                    'type' => 'number',
                    'hint' => '0 = keep forever',
                ])
            </div>
        </div>

        {{-- Roles --}}
        <div x-show="tab === 'roles'">
            @foreach($settings['roles'] as $roleName => $permissions)
            <div class="fh-role-block">
                <div class="fh-role-block__title">{{ $roleName }}</div>
                <div class="fh-role-block__perms">
                    @foreach($permissions as $type => $perms)
                    <div class="fh-role-perm-group">
                        <span class="fh-role-perm-type">{{ ucfirst($type) }}</span>
                        @forelse($perms as $perm)
                            <span class="fh-badge fh-badge--blue">{{ $perm }}</span>
                        @empty
                            <span class="fh-badge fh-badge--gray">none</span>
                        @endforelse
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
            <p class="fh-muted" style="margin-top:1rem">Role permissions are defined in <code>config/filehosting.php</code>.</p>
        </div>

    </div>

    {{-- Save feedback --}}
    <div x-show="saved" x-transition class="fh-toast fh-toast--success">✓ Setting saved.</div>
    <div x-show="saveError" x-transition class="fh-toast fh-toast--error" x-text="saveError"></div>

    {{-- Cache flush button --}}
    <div style="margin-top:2rem; padding-top:1rem; border-top:1px solid #e5e7eb">
        <button @click="flushCache()" class="fh-btn fh-btn--ghost">Flush Settings Cache</button>
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
