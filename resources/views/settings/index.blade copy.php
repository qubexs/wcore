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
            <div class="card shadow">
                <div class="card-header fw-bold">
                    <i class="fas fa-cog me-2" style="color:#007AFF;"></i> General Settings
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
             2. DASHBOARD TAB
             ════════════════════════════════════════════ --}}
        <div class="tab-pane fade" id="dashboard" role="tabpanel">
            <div class="card shadow">
                <div class="card-header fw-bold">
                    <i class="fas fa-tachometer-alt me-2" style="color:#34C759;"></i> Dashboard Settings
                </div>
                <div class="card-body">

                    <div class="row g-3 mb-4">

                        <div class="grid-stack mb-4" id="dashboardGrid">

    {{-- Total Users --}}
    <div class="grid-stack-item"
         gs-w="3" gs-h="2"
         data-key="total_users">
        <div class="grid-stack-item-content">
            <div class="card text-center p-3"
                 style="background:linear-gradient(135deg,#007AFF,#5856D6);border:none;">
                <div class="fs-2 fw-bold text-white">
                    {{ $stats['total_users'] ?? 0 }}
                </div>
                <div class="text-white opacity-75 small">
                    Total Users
                </div>
            </div>
        </div>
    </div>

    {{-- Settings Rows --}}
    <div class="grid-stack-item"
         gs-w="3" gs-h="2"
         data-key="total_settings">
        <div class="grid-stack-item-content">
            <div class="card text-center p-3"
                 style="background:linear-gradient(135deg,#34C759,#30B050);border:none;">
                <div class="fs-2 fw-bold text-white">
                    {{ $stats['total_settings'] ?? 0 }}
                </div>
                <div class="text-white opacity-75 small">
                    Settings Rows
                </div>
            </div>
        </div>
    </div>

    {{-- Active Settings --}}
    <div class="grid-stack-item"
         gs-w="3" gs-h="2"
         data-key="active_settings">
        <div class="grid-stack-item-content">
            <div class="card text-center p-3"
                 style="background:linear-gradient(135deg,#5AC8FA,#32ADE6);border:none;">
                <div class="fs-2 fw-bold text-white">
                    {{ $stats['active_settings'] ?? 0 }}
                </div>
                <div class="text-white opacity-75 small">
                    Active Settings
                </div>
            </div>
        </div>
    </div>

    {{-- Last Updated --}}
    <div class="grid-stack-item"
         gs-w="3" gs-h="2"
         data-key="last_updated">
        <div class="grid-stack-item-content">
            <div class="card text-center p-3"
                 style="background:linear-gradient(135deg,#FF9500,#FF6B00);border:none;">
                <div class="fw-bold text-white" style="font-size:0.9rem;">
                    {{ $stats['last_updated'] ?? '—' }}
                </div>
                <div class="text-white opacity-75 small">
                    Last Updated
                </div>
            </div>
        </div>
    </div>

</div>

{{--  --}}
 @push('scripts')
<script src="https://cdn.jsdelivr.net/npm/gridstack@8.4.0/dist/gridstack-all.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    let grid = GridStack.init({
        column: 12,
        cellHeight: 80,
        margin: 10,
        float: true
    }, '#dashboardGrid');

    // Save layout on change
    grid.on('change', function () {

        let layout = [];
        grid.engine.nodes.forEach(node => {
            layout.push({
                key: node.el.dataset.key,
                x: node.x,
                y: node.y,
                w: node.w,
                h: node.h
            });
        });

        fetch("{{ route('dashboard.layout.save') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ layout: layout })
        });
    });

});
</script>
@endpush                   
                    
                    </div>
                </div>
            </div>
        </div>{{-- /dashboard --}}

       {{-- ════════════════════════════════════════════
     3. DATABASE TAB — 3 stacked cards
════════════════════════════════════════════ --}}
<div class="tab-pane fade" id="database" role="tabpanel">

    {{-- Card 1 — Create Backup --}}
    <div class="card shadow mb-3">
        <div class="card-header fw-bold">
            <i class="fas fa-database me-2" style="color:#FF9500;"></i> Create Database Backup
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
    <div class="card shadow mb-3">
        <div class="card-header fw-bold">
            <i class="fas fa-undo me-2" style="color:#FF3B30;"></i> Restore Database
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
    <div class="card shadow mb-3">
        <div class="card-header fw-bold">
            <i class="fas fa-history me-2" style="color:#34C759;"></i> Backup History
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th><th>File</th><th>Size</th><th>Date</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($backups ?? [] as $backup)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="text-break">{{ $backup['name'] }}</td>
                                <td>{{ $backup['size'] }}</td>
                                <td class="small text-muted">{{ $backup['time'] }}</td>
                                <td>
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
                <div class="card shadow">
                    <div class="card-header fw-bold d-flex align-items-center gap-2">
                        <span class="badge rounded-pill text-bg-success" style="width:24px;height:24px;line-height:24px;font-size:.7rem;">1</span>
                        <i class="fas fa-archive" style="color:#34C759;"></i> Create Backup
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
                <div class="card shadow">
                    <div class="card-header fw-bold d-flex align-items-center gap-2">
                        <span class="badge rounded-pill text-bg-primary" style="width:24px;height:24px;line-height:24px;font-size:.7rem;">2</span>
                        <i class="fas fa-upload" style="color:#007AFF;"></i> Restore from External ZIP
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
                <div class="card shadow">
                    <div class="card-header fw-bold d-flex align-items-center gap-2">
                        <span class="badge rounded-pill text-bg-warning text-dark" style="width:24px;height:24px;line-height:24px;font-size:.7rem;">3</span>
                        <i class="fas fa-list" style="color:#FF9500;"></i> Backup List
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
    xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
    
    // Simulate progress
    var progress = 0;
    var progressInterval = setInterval(function() {
        if (progress < 90) {
            progress += Math.random() * 15;
            if (progress > 90) progress = 90;
            updateBackupProgress(Math.floor(progress), 'Archiving files...');
        }
    }, 500);
    
    xhr.onload = function() {
        clearInterval(progressInterval);
        
        if (xhr.status === 200) {
            try {
                var res = JSON.parse(xhr.responseText);
                if (res.success) {
                    updateBackupProgress(100, 'Complete!');
                    document.getElementById('backupLog').innerHTML += '<div>› Files archived: ' + res.files_count + '</div>';
                    document.getElementById('backupLog').innerHTML += '<div>› Saved as: ' + res.filename + '</div>';
                    
                    setTimeout(function() {
                        document.getElementById('backupStep2').classList.add('d-none');
                        document.getElementById('backupStep3').classList.remove('d-none');
                        document.getElementById('backupDoneMsg').textContent = res.message;
                    }, 800);
                } else {
                    alert(res.message || 'Backup failed');
                    resetBackup();
                }
            } catch (e) {
                alert('Invalid response from server');
                resetBackup();
            }
        } else {
            alert('Server error: ' + xhr.status);
            resetBackup();
        }
    };
    
    xhr.onerror = function() {
        clearInterval(progressInterval);
        alert('Network error');
        resetBackup();
    };
    
    xhr.send(formData);
}

function updateBackupProgress(pct, label) {
    document.getElementById('backupProgressBar').style.width = pct + '%';
    document.getElementById('backupProgressPct').textContent = pct + '%';
    document.getElementById('backupProgressLabel').textContent = label;
    document.getElementById('backupLog').innerHTML += '<div>› ' + label + ' (' + pct + '%)</div>';
}

function resetBackup() {
    document.getElementById('backupStep1').classList.remove('d-none');
    document.getElementById('backupStep2').classList.add('d-none');
    document.getElementById('backupStep3').classList.add('d-none');
    document.getElementById('backupProgressBar').style.width = '0%';
    document.getElementById('backupLog').innerHTML = '';
}



        })();
        </script>

        {{-- ════════════════════════════════════════════
             5. UPDATE TAB
             ════════════════════════════════════════════ --}}
        <div class="tab-pane fade" id="update" role="tabpanel">
            <div class="card shadow">
                <div class="card-header fw-bold">
                    <i class="fas fa-sync me-2" style="color:#AF52DE;"></i> Update Website
                </div>
                <div class="card-body">
                    <p class="text-muted">Upload a ZIP update package or check for available updates.</p>
                    <button class="btn btn-warning">
                        <i class="fas fa-search me-1"></i> Check for Updates
                    </button>
                </div>
            </div>
        </div>{{-- /update --}}

    </div>{{-- /tab-content --}}
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     TAB JAVASCRIPT
     — Uses class="glass-tab" and data-tab="id" exactly as in the HTML above
     — Also remembers the active tab across form submits via sessionStorage
     ══════════════════════════════════════════════════════════════════════ --}}


@endsection