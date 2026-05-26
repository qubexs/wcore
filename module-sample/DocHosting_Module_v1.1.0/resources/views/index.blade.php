@extends('layouts.admin')

@section('title', 'Document Hosting - File Manager')

@section('main-content')
<div class="container-fluid py-4">
    <!-- Header & Breadcrumbs -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('filehosting.index') }}"><i class="fas fa-home"></i> Home</a></li>
                    @if(isset($currentFolder))
                        @foreach($breadcrumbs as $crumb)
                            <li class="breadcrumb-item">
                                <a href="{{ route('filehosting.browse', $crumb['id']) }}">{{ $crumb['name'] }}</a>
                            </li>
                        @endforeach
                        <li class="breadcrumb-item active">{{ $currentFolder->name }}</li>
                    @endif
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-folder-open text-warning me-2"></i>
                    {{ $currentFolder->name ?? 'My Files' }}
                </h2>
                
                <div class="btn-group">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="fas fa-cloud-upload-alt me-2"></i>Upload Files
                    </button>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createFolderModal">
                        <i class="fas fa-folder-plus me-2"></i>New Folder
                    </button>
                    @can('manage_settings')
                    <a href="{{ route('filehosting.settings') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-cog"></i>
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-uppercase">Total Files</h6>
                            <h3 class="mb-0">{{ $stats['total_files'] ?? 0 }}</h3>
                        </div>
                        <i class="fas fa-file fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-uppercase">Storage Used</h6>
                            <h3 class="mb-0">{{ $stats['storage_used'] ?? '0 MB' }}</h3>
                        </div>
                        <i class="fas fa-hdd fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-uppercase">Folders</h6>
                            <h3 class="mb-0">{{ $stats['total_folders'] ?? 0 }}</h3>
                        </div>
                        <i class="fas fa-folder fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-uppercase">Downloads</h6>
                            <h3 class="mb-0">{{ $stats['total_downloads'] ?? 0 }}</h3>
                        </div>
                        <i class="fas fa-download fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- File Manager Grid -->
    <div class="card">
        <div class="card-header bg-white">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="searchFiles" placeholder="Search files and folders...">
                    </div>
                </div>
                <div class="col-md-8 text-end">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary active" id="viewGrid"><i class="fas fa-th"></i></button>
                        <button class="btn btn-outline-secondary" id="viewList"><i class="fas fa-list"></i></button>
                    </div>
                    <select class="form-select form-select-sm d-inline-block w-auto ms-2" id="sortBy">
                        <option value="name">Name</option>
                        <option value="date">Date Modified</option>
                        <option value="size">Size</option>
                        <option value="type">Type</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Folders Section -->
            @if($folders->count() > 0)
            <h6 class="text-muted mb-3 text-uppercase">Folders</h6>
            <div class="row g-3 mb-4" id="foldersGrid">
                @foreach($folders as $folder)
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 folder-item" data-name="{{ $folder->name }}">
                    <div class="card folder-card h-100 border-0 shadow-sm hover-shadow">
                        <div class="card-body text-center position-relative">
                            <div class="dropdown position-absolute top-0 end-0 m-2">
                                <button class="btn btn-sm btn-link text-dark" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('filehosting.browse', $folder->id) }}"><i class="fas fa-folder-open me-2"></i>Open</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="renameFolder({{ $folder->id }}, '{{ $folder->name }}')"><i class="fas fa-edit me-2"></i>Rename</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="moveFolder({{ $folder->id }})"><i class="fas fa-arrows-alt me-2"></i>Move</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteFolder({{ $folder->id }})"><i class="fas fa-trash me-2"></i>Delete</a></li>
                                </ul>
                            </div>
                            
                            <a href="{{ route('filehosting.browse', $folder->id) }}" class="text-decoration-none">
                                <i class="fas fa-folder fa-4x text-warning mb-3"></i>
                                <h6 class="card-title text-dark mb-1 text-truncate">{{ $folder->name }}</h6>
                                <small class="text-muted">{{ $folder->files_count ?? 0 }} items</small>
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <!-- Files Section -->
            @if($files->count() > 0)
            <h6 class="text-muted mb-3 text-uppercase">Files</h6>
            <div class="row g-3" id="filesGrid">
                @foreach($files as $file)
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 file-item" 
                     data-name="{{ $file->original_name }}" 
                     data-type="{{ $file->extension }}"
                     data-size="{{ $file->size }}"
                     data-date="{{ $file->updated_at }}">
                    <div class="card file-card h-100 border-0 shadow-sm hover-shadow">
                        <div class="card-body text-center position-relative">
                            <div class="dropdown position-absolute top-0 end-0 m-2">
                                <button class="btn btn-sm btn-link text-dark" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('filehosting.files.show', $file->id) }}"><i class="fas fa-eye me-2"></i>Preview</a></li>
                                    <li><a class="dropdown-item" href="{{ route('filehosting.files.download', $file->id) }}"><i class="fas fa-download me-2"></i>Download</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="shareFile({{ $file->id }})"><i class="fas fa-share-alt me-2"></i>Share</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#" onclick="moveFile({{ $file->id }})"><i class="fas fa-arrows-alt me-2"></i>Move</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="renameFile({{ $file->id }}, '{{ $file->original_name }}')"><i class="fas fa-edit me-2"></i>Rename</a></li>
                                    @can('replace', $file)
                                    <li><a class="dropdown-item" href="#" onclick="replaceFile({{ $file->id }})"><i class="fas fa-exchange-alt me-2"></i>Replace</a></li>
                                    @endcan
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteFile({{ $file->id }})"><i class="fas fa-trash me-2"></i>Delete</a></li>
                                </ul>
                            </div>

                            <!-- File Icon/Thumbnail -->
                            <div class="file-preview mb-3">
                                @if(in_array($file->extension, ['jpg', 'jpeg', 'png', 'gif']))
                                    <img src="{{ asset('storage/' . $file->file_path) }}" class="img-fluid rounded" style="height: 80px; object-fit: cover;">
                                @elseif($file->extension == 'pdf')
                                    <i class="fas fa-file-pdf fa-4x text-danger"></i>
                                @elseif(in_array($file->extension, ['doc', 'docx']))
                                    <i class="fas fa-file-word fa-4x text-primary"></i>
                                @elseif(in_array($file->extension, ['xls', 'xlsx']))
                                    <i class="fas fa-file-excel fa-4x text-success"></i>
                                @else
                                    <i class="fas fa-file fa-4x text-secondary"></i>
                                @endif
                            </div>

                            <h6 class="card-title text-dark mb-1 text-truncate" title="{{ $file->original_name }}">
                                {{ Str::limit($file->original_name, 20) }}
                            </h6>
                            <small class="text-muted d-block">{{ $file->extension }} • {{ $file->size_formatted }}</small>
                            <small class="text-muted">{{ $file->updated_at->diffForHumans() }}</small>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
                @if($folders->count() == 0)
                <div class="text-center py-5">
                    <i class="fas fa-folder-open fa-5x text-muted mb-3"></i>
                    <h5 class="text-muted">This folder is empty</h5>
                    <p class="text-muted">Upload files or create a new folder to get started</p>
                </div>
                @endif
            @endif
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Files</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('filehosting.files.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                @csrf
                <input type="hidden" name="folder_id" value="{{ $currentFolder->id ?? '' }}">
                
                <div class="modal-body">
                    <div class="upload-zone border border-dashed rounded p-5 text-center" id="dropZone">
                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                        <h5>Drag & Drop files here</h5>
                        <p class="text-muted">or</p>
                        <input type="file" name="files[]" multiple class="d-none" id="fileInput">
                        <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('fileInput').click()">
                            Browse Files
                        </button>
                        <div id="fileList" class="mt-3 text-start"></div>
                    </div>
                    
                    <div class="mt-3">
                        <label class="form-label">Visibility</label>
                        <select name="visibility" class="form-select">
                            <option value="private">Private (Only me)</option>
                            <option value="public">Public (Everyone)</option>
                            <option value="restricted">Restricted (Specific users)</option>
                        </select>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="uploadBtn">
                        <i class="fas fa-upload me-2"></i>Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Folder Modal -->
<div class="modal fade" id="createFolderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Folder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('filehosting.folders.store') }}" method="POST">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $currentFolder->id ?? '' }}">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Folder Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="Enter folder name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description (Optional)</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Visibility</label>
                        <select name="visibility" class="form-select">
                            <option value="private">Private</option>
                            <option value="public">Public</option>
                            <option value="restricted">Restricted</option>
                        </select>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Create Folder</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .hover-shadow:hover {
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }
    .folder-card, .file-card {
        cursor: pointer;
        transition: all 0.2s;
    }
    .upload-zone {
        background: #f8f9fa;
        transition: all 0.3s;
    }
    .upload-zone.dragover {
        background: #e3f2fd;
        border-color: #2196f3 !important;
    }
</style>
@endpush

@push('scripts')
<script>
// Drag and drop upload
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');

dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('dragover');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('dragover');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('dragover');
    fileInput.files = e.dataTransfer.files;
    updateFileList();
});

fileInput.addEventListener('change', updateFileList);

function updateFileList() {
    const files = fileInput.files;
    const list = document.getElementById('fileList');
    list.innerHTML = '';
    for(let file of files) {
        list.innerHTML += `<div class="alert alert-info py-2"><i class="fas fa-file me-2"></i>${file.name} (${(file.size/1024).toFixed(1)} KB)</div>`;
    }
}

// Search functionality
document.getElementById('searchFiles').addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase();
    document.querySelectorAll('.folder-item, .file-item').forEach(item => {
        const name = item.getAttribute('data-name').toLowerCase();
        item.style.display = name.includes(term) ? 'block' : 'none';
    });
});

// View toggle
document.getElementById('viewList').addEventListener('click', function() {
    document.getElementById('foldersGrid').classList.replace('row', 'list-group');
    document.getElementById('filesGrid').classList.replace('row', 'list-group');
});

// Action functions
function deleteFolder(id) {
    if(confirm('Are you sure? This will delete all contents!')) {
        fetch(`/filehosting/folders/${id}`, {
            method: 'DELETE',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
        }).then(() => location.reload());
    }
}

function deleteFile(id) {
    if(confirm('Are you sure you want to delete this file?')) {
        fetch(`/filehosting/files/${id}`, {
            method: 'DELETE',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
        }).then(() => location.reload());
    }
}
</script>
@endpush
@endsection