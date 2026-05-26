@extends('layouts.admin')

@section('main-content')
<h1 class="mb-4">
    <i class="fas fa-cog me-2"></i> File Hosting Settings
</h1>

<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#files" type="button">
            <i class="fas fa-file me-1"></i> Files
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#roles" type="button">
            <i class="fas fa-user-shield me-1"></i> Role & Permission
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#public" type="button">
            <i class="fas fa-globe me-1"></i> Public Access
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#other" type="button">
            <i class="fas fa-sliders-h me-1"></i> Other
        </button>
    </li>
</ul>

<form method="POST" action="#">
@csrf

<div class="tab-content">

    {{-- ================= FILE SETTINGS ================= --}}
    <div class="tab-pane fade show active" id="files">
        <div class="card">
            <div class="card-body">

                <h5><i class="fas fa-folder-open me-1"></i> File Settings</h5>
                <hr>

                <div class="mb-3">
                    <label class="form-label">Max Upload Size (MB)</label>
                    <input type="number" class="form-control" value="10">
                </div>

                <div class="mb-3">
                    <label class="form-label">Allowed File Types</label>
                    <input type="text" class="form-control" value="pdf,jpg,png,docx">
                    <small class="text-muted">Comma separated</small>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" checked>
                    <label class="form-check-label">
                        Enable Image Thumbnails
                    </label>
                </div>

            </div>
        </div>
    </div>

    {{-- ================= ROLE & PERMISSION ================= --}}
    <div class="tab-pane fade" id="roles">
        <div class="card">
            <div class="card-body">

                <h5><i class="fas fa-user-shield me-1"></i> Role & Permission</h5>
                <hr>

                <p class="text-muted">
                    These permissions are managed via the system role manager.
                </p>

                <ul class="list-group">
                    <li class="list-group-item">
                        <code>filehosting.view</code> – View files
                    </li>
                    <li class="list-group-item">
                        <code>filehosting.upload</code> – Upload files
                    </li>
                    <li class="list-group-item">
                        <code>filehosting.delete</code> – Delete files
                    </li>
                </ul>

            </div>
        </div>
    </div>

    {{-- ================= PUBLIC ACCESS ================= --}}
    <div class="tab-pane fade" id="public">
        <div class="card">
            <div class="card-body">

                <h5><i class="fas fa-globe me-1"></i> Public Access</h5>
                <hr>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox">
                    <label class="form-check-label">
                        Allow public (unauthenticated) access to files
                    </label>
                </div>

                <div class="mb-3">
                    <label class="form-label">Public URL Expiry (minutes)</label>
                    <input type="number" class="form-control" value="60">
                </div>

            </div>
        </div>
    </div>

    {{-- ================= OTHER ================= --}}
    <div class="tab-pane fade" id="other">
        <div class="card">
            <div class="card-body">

                <h5><i class="fas fa-sliders-h me-1"></i> Other Settings</h5>
                <hr>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox">
                    <label class="form-check-label">
                        Enable file access logging
                    </label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox">
                    <label class="form-check-label">
                        Notify admin on large uploads
                    </label>
                </div>

            </div>
        </div>
    </div>

</div>

<div class="mt-4">
    <button class="btn btn-primary">
        <i class="fas fa-save me-1"></i> Save Settings
    </button>
    <a href="{{ route('modules.index') }}" class="btn btn-secondary ms-2">
        Cancel
    </a>
</div>

</form>
@endsection
