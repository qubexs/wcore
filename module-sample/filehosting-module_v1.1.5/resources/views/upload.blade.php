@extends('layouts.admin')

@section('title', 'Upload Files')

@php
$referer = request()->header('referer') ?? '';
$showBack = str_contains($referer, '/modules') || str_contains($referer, '/filehosting');
$activeTab = request()->query('tab', 'upload');
@endphp

@section('main-content')
@include('FileHosting::_partials.styles')
<div class="fh-container" style="padding-top: 3.5rem;">
    
    <div class="fh-header">
        <div class="fh-header__left">
            @if($showBack)
            <a href="{{ url()->previous() }}" class="fh-back-link">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/></svg>
                Back
            </a>
            @endif
            <h1 class="fh-page-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Upload Files
            </h1>
        </div>
    </div>

    <div class="fh-tabs" id="filehostingTab">
        <a href="{{ route('filehosting.files.upload', ['tab' => 'upload']) }}" class="fh-tab {{ $activeTab === 'upload' ? 'fh-tab--active' : '' }}">
            <svg viewBox="0 0 20 20" fill="currentColor" style="width:1rem;height:1rem;margin-right:0.25rem"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
            Upload
        </a>
        <a href="{{ route('filehosting.files.upload', ['tab' => 'files']) }}" class="fh-tab {{ $activeTab === 'files' ? 'fh-tab--active' : '' }}">
            <svg viewBox="0 0 20 20" fill="currentColor" style="width:1rem;height:1rem;margin-right:0.25rem"><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd"/></svg>
            Files
        </a>
        <a href="{{ route('filehosting.files.upload', ['tab' => 'folders']) }}" class="fh-tab {{ $activeTab === 'folders' ? 'fh-tab--active' : '' }}">
            <svg viewBox="0 0 20 20" fill="currentColor" style="width:1rem;height:1rem;margin-right:0.25rem"><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd"/></svg>
            Folders
        </a>
    </div>

    @if($activeTab === 'folders')
    <div class="glass-panel p-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center">
                <div class="me-3"><i class="fas fa-folder fa-2x text-warning"></i></div>
                <div>
                    <h4 class="mb-0 fw-bold">Manage Folders</h4>
                    <p class="mb-0 text-muted small">Create and manage folders for organizing files</p>
                </div>
            </div>
            <button type="button" class="btn btn-primary" onclick="createFolder()">
                <i class="fas fa-plus me-1"></i> New Folder
            </button>
        </div>

        <div class="table-responsive">
            <table class="fh-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Parent</th>
                        <th>Visibility</th>
                        <th>Files</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($folders as $folder)
                    <tr>
                        <td>
                            <i class="fas fa-folder text-warning me-2"></i>
                            {{ $folder->name }}
                        </td>
                        <td>{{ $folder->parent->name ?? '—' }}</td>
                        <td>
                            <span class="badge badge-{{ $folder->visibility === 'public' ? 'success' : ($folder->visibility === 'restricted' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($folder->visibility) }}
                            </span>
                        </td>
                        <td>{{ $folder->files_count ?? $folder->files->count() }}</td>
                        <td>{{ $folder->created_at->format('Y-m-d') }}</td>
                        <td>
                            <button class="btn btn-sm btn-info" title="Rename" onclick="renameFolder({{ $folder->id }}, '{{ $folder->name }}')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" title="Delete" onclick="deleteFolder({{ $folder->id }})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <i class="fas fa-folder-open fa-2x text-muted mb-2"></i>
                            <p class="mb-0 text-muted">No folders yet. Create your first folder!</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($activeTab === 'upload')
    <div class="glass-panel p-4">

        {{-- Header --}}
        <div class="d-flex align-items-center mb-4">
            <div class="me-3"><i class="fas fa-cloud-upload-alt fa-2x text-primary"></i></div>
            <div>
                <h4 class="mb-0 fw-bold">Upload Files</h4>
                <p class="mb-0 text-muted small">Drag & drop your files or browse to upload</p>
            </div>
        </div>

        {{-- 
            IDs must match uiglass.js exactly:
            #moduleUploadForm, #moduleDropzone, #moduleFileInput,
            #dropzoneIcon, #dropzoneFileInfo, #dropzoneFileName,
            #dropzoneFileSize, #dropzoneFileRemove, #moduleUploadBtn, #dropzoneError
        --}}
        <form id="moduleUploadForm" action="{{ route('filehosting.files.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Dropzone --}}
            <div id="moduleDropzone" 
                data-field-name="file"
                data-accept=".jpg,.jpeg,.pdf,.ppt,.pptx,.doc,.docx,.xls,.xlsx" data-max-size="500"
                style="border:2px dashed #a5b4fc; border-radius:12px; padding:40px 20px;
                 text-align:center; cursor:pointer; transition:all 0.3s ease; background:rgba(255,255,255,0.05);">

                <input type="file" id="moduleFileInput" name="file" multiple style="display:none;">

                {{-- Default content --}}
                <div class="dropzone-content">
                    <div class="mb-3">
                        <i class="fas fa-cloud-upload-alt" id="dropzoneIcon"
                           style="font-size:48px; color:#a5b4fc;"></i>
                    </div>
                    <p class="fw-bold mb-1">Drag & Drop files here, or</p>
                    <button type="button" id="dropzoneBrowseBtn"
                            class="btn btn-primary btn-sm mt-2" style="border-radius:20px;">
                        Browse Files
                    </button>
                    <p class="text-muted small mt-2">Maximum file size: {{ ini_get('upload_max_filesize') }}</p>
                </div>

                {{-- File info (shown after selection) --}}
                <div id="dropzoneFileInfo" class="mt-3 text-center" style="display:none;">
                    <div class="d-inline-flex align-items-center bg-white px-3 py-2 rounded-pill shadow-sm border">
                        <i class="fas fa-file-archive text-success me-2"></i>
                        <span id="dropzoneFileName" class="fw-bold me-2">—</span>
                        <span id="dropzoneFileSize" class="text-muted small me-3"></span>
                        <i class="fas fa-times-circle text-danger" id="dropzoneFileRemove"
                           style="cursor:pointer;" title="Remove"></i>
                    </div>
                </div>

                <div id="dropzoneError" class="text-danger text-center mt-2 fw-bold" style="display:none; background:#fee2e2; padding:0.5rem; border-radius:0.5rem; border:1px solid #ef4444;"></div>
            </div>

            {{-- Options --}}
            <div class="mt-4 row g-3">
                <div class="col-md-6">
                    <label class="form-label">Destination Folder</label>
                    <div class="input-group">
                        <select name="folder_id" class="form-select">
                            <option value="">— Root (no folder) —</option>
                            @foreach($folders as $folder)
                            <option value="{{ $folder->id }}">{{ $folder->name }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="new_folder" class="form-control" placeholder="Or create new...">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Visibility</label>
                    <select name="visibility" class="form-select">
                        <option value="private">Private</option>
                        <option value="public">Public</option>
                        <option value="restricted">Restricted</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Description (optional)</label>
                    <textarea name="description" rows="3" class="form-control"
                              placeholder="Brief description…"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Expires At (optional)</label>
                    <input type="datetime-local" name="expires_at" class="form-control">
                </div>
            </div>

            {{-- Submit — hidden until uiglass.js shows it --}}
            <div class="text-center mt-3">
                <button type="submit" id="moduleUploadBtn"
                        class="btn btn-success px-4 py-2" style="display:none; border-radius:20px;">
                    <i class="fas fa-bolt me-2"></i>Upload File
                </button>
            </div>

        </form>
    </div>
    @else
    <div class="d-flex gap-3">
        <div class="flex-grow-1 glass-panel">
            <div class="table-responsive">
                <table class="fh-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Folder</th>
                            <th>Uploaded By</th>
                            <th>Size</th>
                            <th>Visibility</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($files as $file)
                        @php
                            $ext = strtolower(pathinfo($file->name, PATHINFO_EXTENSION));
                            $fileUrl = \Storage::url($file->path);
                        @endphp
                        <tr>
                            <td>
                                @if($ext === 'pdf')
                                    <a href="{{ $fileUrl }}" target="_blank" class="pdf-preview" data-pdf="{{ $fileUrl }}">{{ $file->name }}</a>
                                @else
                                    <a href="{{ $fileUrl }}" target="_blank">{{ $file->name }}</a>
                                @endif
                            </td>
                            <td>{{ $file->folder->name ?? '—' }}</td>
                            <td>{{ optional($file->owner)->name ?? $file->uploaded_by ?? 'N/A' }}</td>
                            <td>{{ $file->size ? number_format($file->size / 1024, 1) . ' KB' : '—' }}</td>
                            <td>
                                <span class="badge badge-{{ $file->visibility === 'public' ? 'success' : ($file->visibility === 'restricted' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($file->visibility) }}
                                </span>
                            </td>
                            <td>{{ $file->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-primary btn-sm" title="Preview" onclick="previewFile('{{ $fileUrl }}', '{{ $ext }}')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-info btn-sm" title="Edit" onclick="editFile({{ $file->id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-secondary btn-sm" title="Move" onclick="moveFile({{ $file->id }})">
                                                <i class="fas fa-folder"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm" title="Delete" onclick="deleteFile({{ $file->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-folder-open fa-2x text-muted mb-2"></i>
                                        <p class="mb-0 text-muted">No files found.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($files->hasPages())
                    <div class="px-3 py-2 border-top mt-2">
                        {{ $files->links() }}
                    </div>
                    @endif
                </div>
        <div id="filePreviewPanel" class="glass-panel" style="width: 700px; min-height: 600px; display: none;">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <h6 class="mb-0 fw-bold">Preview</h6>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="closePreview()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div style="height: 550px;">
                <iframe id="filePreviewFrame" src="" style="width: 100%; height: 100%; border: 1px solid #e5e7eb; border-radius: 8px;"></iframe>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
function previewFile(url, ext) {
    var previewUrl = url;
    if (ext === 'pdf' || ext === 'doc' || ext === 'docx' || ext === 'xls' || ext === 'xlsx' || ext === 'ppt' || ext === 'pptx') {
        previewUrl = url;
    }
    document.getElementById('filePreviewFrame').src = previewUrl;
    document.getElementById('filePreviewPanel').style.display = 'block';
}

function closePreview() {
    document.getElementById('filePreviewPanel').style.display = 'none';
    document.getElementById('filePreviewFrame').src = '';
}

function editFile(id) {
    Swal.fire({
        title: 'Edit File',
        text: 'Edit functionality coming soon',
        icon: 'info'
    });
}
function moveFile(id) {
    Swal.fire({
        title: 'Move File',
        text: 'Move functionality coming soon',
        icon: 'info'
    });
}
function deleteFile(id) {
    Swal.fire({
        title: 'Delete File',
        text: 'Are you sure you want to delete this file?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/filehosting/files/' + id, {
                method: 'DELETE',
                headers: { 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            }).then(res => {
                if (!res.ok) {
                    return res.json().then(err => Promise.reject(err));
                }
                return res.json();
            }).then(data => {
                Swal.fire('Deleted!', 'File has been deleted.', 'success').then(() => {
                    location.reload();
                });
            }).catch(err => {
                console.error('Delete error:', err);
                Swal.fire('Error', err.message || 'Failed to delete file.', 'error');
            });
        }
    });
}

function createFolder() {
    Swal.fire({
        title: 'New Folder',
        html: `
            <input type="text" id="swal-folder-name" class="swal2-input" placeholder="Folder name">
            <select id="swal-folder-visibility" class="swal2-select">
                <option value="private">Private</option>
                <option value="public">Public</option>
                <option value="restricted">Restricted</option>
            </select>
        `,
        preConfirm: () => {
            const name = document.getElementById('swal-folder-name').value;
            const visibility = document.getElementById('swal-folder-visibility').value;
            if (!name.trim()) {
                Swal.showValidationMessage('Folder name is required');
                return false;
            }
            return { name, visibility };
        },
        showCancelButton: true,
        confirmButtonText: 'Create',
        cancelButtonText: 'Cancel'
    }).then(result => {
        if (result.isConfirmed) {
            fetch('/filehosting/folders', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(result.value)
            }).then(res => res.json()).then(data => {
                Swal.fire('Success', 'Folder created!', 'success').then(() => location.reload());
            }).catch(err => {
                Swal.fire('Error', err.message || 'Failed to create folder.', 'error');
            });
        }
    });
}

function renameFolder(id, currentName) {
    Swal.fire({
        title: 'Rename Folder',
        html: `
            <input type="text" id="swal-folder-name" class="swal2-input" value="${currentName}" placeholder="Folder name">
            <select id="swal-folder-visibility" class="swal2-select">
                <option value="private">Private</option>
                <option value="public">Public</option>
                <option value="restricted">Restricted</option>
            </select>
        `,
        preConfirm: () => {
            const name = document.getElementById('swal-folder-name').value;
            const visibility = document.getElementById('swal-folder-visibility').value;
            if (!name.trim()) {
                Swal.showValidationMessage('Folder name is required');
                return false;
            }
            return { name, visibility };
        },
        showCancelButton: true,
        confirmButtonText: 'Save',
        cancelButtonText: 'Cancel'
    }).then(result => {
        if (result.isConfirmed) {
            fetch('/filehosting/folders/' + id + '/rename', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(result.value)
            }).then(res => res.json()).then(data => {
                Swal.fire('Success', 'Folder renamed!', 'success').then(() => location.reload());
            }).catch(err => {
                Swal.fire('Error', err.message || 'Failed to rename folder.', 'error');
            });
        }
    });
}

function deleteFolder(id) {
    Swal.fire({
        title: 'Delete Folder',
        text: 'Are you sure? All files in this folder will be moved to root.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete!'
    }).then(result => {
        if (result.isConfirmed) {
            fetch('/filehosting/folders/' + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            }).then(res => res.json()).then(data => {
                Swal.fire('Deleted!', 'Folder deleted.', 'success').then(() => {
                    location.reload();
                });
            }).catch(err => {
                Swal.fire('Error', err.message || 'Failed to delete folder.', 'error');
            });
        }
    });
}
</script>
@endpush
@endsection
