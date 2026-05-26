@extends('layouts.admin')

@section('title', 'Upload Files')

@php
$referer = request()->header('referer') ?? '';
$showBack = str_contains($referer, '/modules') || str_contains($referer, '/filehosting');
$activeTab = request()->query('tab', 'upload');
@endphp

@section('main-content')
@include('filehosting::_partials.styles')
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
                    @php $depth = $folder->depth ?? 0; @endphp
                    <tr>
                        <td>
                            <span style="display:inline-block;width:{{ $depth * 20 }}px;"></span>
                            <i class="fas fa-folder text-warning me-2"></i>
                            {{ $folder->name }}
                        </td>
                        <td>{{ $folder->parent_name ?? '—' }}</td>
                        <td>
                            <span class="badge badge-{{ $folder->visibility === 'public' ? 'success' : ($folder->visibility === 'restricted' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($folder->visibility) }}
                            </span>
                        </td>
                        <td>{{ $folder->files_count ?? $folder->files->count() }}</td>
                        <td>{{ $folder->created_at->format('Y-m-d') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-info btn-sm" title="Rename" onclick="renameFolder({{ $folder->id }}, '{{ $folder->name }}')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-secondary btn-sm" title="Move" onclick="moveFolder({{ $folder->id }})">
                                    <i class="fas fa-folder"></i>
                                </button>
                                <button class="btn btn-danger btn-sm" title="Delete" onclick="deleteFolder({{ $folder->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
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
                data-accept=".jpg,.jpeg,.pdf,.ppt,.pptx,.doc,.docx,.xls,.xlsx,.pub" data-max-size="500"
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
                    <p class="text-muted small mt-2">Maximum file size: {{ round($maxUploadSize / 1048576) }}MB</p>
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
                            @php
                                function renderFolderOptions($folders, $parentId = null, $depth = 0) {
                                    foreach ($folders->where('parent_id', $parentId) as $folder) {
                                        echo '<option value="' . $folder->id . '">' . str_repeat('> ', $depth) . $folder->name . '</option>';
                                        renderFolderOptions($folders, $folder->id, $depth + 1);
                                    }
                                }
                                renderFolderOptions($folders);
                            @endphp
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
    @elseif($activeTab === 'files')
    <div class="d-flex gap-3" style="align-items: flex-start;">
        <div class="flex-grow-1">
            {{-- Header with Search & View Toggle --}}
            <div class="glass-panel mb-3">
                <div class="d-flex align-items-center justify-content-between p-3">
                    <div class="d-flex align-items-center flex-grow-1">
                        {{-- Search Bar --}}
                        <div style="width: 100%; max-width: 400px; position: relative;">
                            <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none;">
                                <svg viewBox="0 0 20 20" fill="currentColor" style="width: 1.1rem; height: 1.1rem;">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                            <input type="text" id="fileSearch" placeholder="Search files or folders…" autocomplete="off"
                                style="width: 100%; padding: 0.5rem 4rem 0.5rem 2.5rem; border: 1.5px solid #d1d5db; border-radius: 999px; font-size: 0.9rem; outline: none;"
                                onfocus="this.style.borderColor='#3b82f6';"
                                onblur="this.style.borderColor='#d1d5db';">
                            <span id="searchClear" onclick="clearFileSearch()" title="Clear"
                                  style="display:none; position:absolute; right:40px; top:50%; transform:translateY(-50%); cursor:pointer; color:#9ca3af;">
                                <i class="fas fa-times"></i>
                            </span>
                            <span id="resultCount" style="position:absolute; right:14px; top:50%; transform:translateY(-50%); font-size:0.75rem; color:#6b7280;"></span>
                        </div>
                    </div>
                    <div class="view-toggle-group ms-3">
                        <button type="button" class="view-toggle-btn active" data-view="table" onclick="switchView('table')">
                            <i class="fas fa-list me-1"></i> Table
                        </button>
                        <button type="button" class="view-toggle-btn" data-view="explorer" onclick="switchView('explorer')">
                            <i class="fas fa-folder-open me-1"></i> Explorer
                        </button>
                    </div>
                </div>
            </div>

            {{-- VIEW 1: Table (List) --}}
            <div id="tableView" class="glass-panel">
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
                            $fileUrl = url('/uploads/' . str_replace('uploads/', '', $file->path));
                        @endphp
                        <tr class="file-table-row">
                            <td>
                                @if($ext === 'pdf')
                                    <a href="{{ $fileUrl }}" target="_blank">{{ $file->name }}</a>
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
                                    <button class="btn btn-info btn-sm" title="Edit" onclick="editFile({{ $file->id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-secondary btn-sm" title="Move" onclick="moveFile({{ $file->id }})">
                                        <i class="fas fa-folder"></i>
                                    </button>
                                    <button class="btn btn-primary btn-sm" title="Preview" onclick="previewFile('{{ $fileUrl }}', '{{ $ext }}', {{ $file->id }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="{{ route('filehosting.files.download', $file->id) }}" class="btn btn-success btn-sm" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <button class="btn btn-danger btn-sm" title="Delete" onclick="deleteFile({{ $file->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm" title="Report" onclick="reportFile({{ $file->id }}, '{{ addslashes($file->name) }}')">
                                        <i class="fas fa-flag"></i>
                                    </button>
                                    <button class="btn btn-outline-warning btn-sm" title="Favorite" onclick="toggleFavorite({{ $file->id }}, this)">
                                        <i class="far fa-star"></i>
                                    </button>
                                    <button class="btn btn-dark btn-sm" title="Share" onclick="shareFile({{ $file->id }}, '{{ addslashes($file->name) }}')">
                                        <i class="fas fa-share-alt"></i>
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

            {{-- VIEW 2: Explorer (Tree) --}}
            <div id="explorerView" class="glass-panel" style="display: none;">
                <table class="fh-table" id="explorerTable">
                    <thead>
                        <tr>
                            <th style="width: 40px;"></th>
                            <th>Folder | Filename</th>
                            <th>Uploaded By</th>
                            <th>Size</th>
                            <th>Visibility</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        function getFileIcon($ext) {
                            if ($ext == 'pdf') return 'fa-file-pdf text-danger';
                            if (in_array($ext, ['doc', 'docx'])) return 'fa-file-word text-primary';
                            if (in_array($ext, ['xls', 'xlsx'])) return 'fa-file-excel text-success';
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) return 'fa-image text-info';
                            return 'fa-file text-secondary';
                        }
                        @endphp

                        @foreach($folders as $folder)
                        <tr class="folder-row" data-folder-id="{{ $folder->id }}">
                            <td class="expand-icon"><i class="fas fa-chevron-right"></i></td>
                            <td><i class="fas fa-folder text-warning me-2"></i><span class="fw-semibold">{{ $folder->name }}</span></td>
                            <td>{{ optional($folder->owner)->name ?? '—' }}</td>
                            <td>{{ $folder->files_count ?? 0 }} files</td>
                            <td><span class="badge badge-{{ $folder->visibility === 'public' ? 'success' : ($folder->visibility === 'restricted' ? 'warning' : 'secondary') }}">{{ ucfirst($folder->visibility ?? 'public') }}</span></td>
                            <td>{{ $folder->created_at->format('Y-m-d') }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-warning btn-sm" onclick="event.stopPropagation()"><i class="far fa-star"></i></button>
                                    <button class="btn btn-dark btn-sm" onclick="event.stopPropagation()"><i class="fas fa-share-alt"></i></button>
                                </div>
                            </td>
                        </tr>
                        @foreach($folder->files as $file)
                        @php $ext = strtolower(pathinfo($file->name, PATHINFO_EXTENSION)); $fileUrl = url('/uploads/' . str_replace('uploads/', '', $file->path)); @endphp
                        <tr class="file-row expandable" data-parent="{{ $folder->id }}" style="display: none;">
                            <td></td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fas {{ getFileIcon($ext) }} me-2"></i><a href="{{ $fileUrl }}" target="_blank">{{ $file->name }}</a></td>
                            <td>{{ optional($file->owner)->name ?? '—' }}</td>
                            <td>{{ number_format($file->size / 1024, 1) }} KB</td>
                            <td><span class="badge badge-{{ $file->visibility === 'public' ? 'success' : ($file->visibility === 'restricted' ? 'warning' : 'secondary') }}">{{ ucfirst($file->visibility) }}</span></td>
                            <td>{{ $file->created_at->format('Y-m-d') }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-info btn-sm" title="Edit" onclick="editFile({{ $file->id }})"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-secondary btn-sm" title="Move" onclick="moveFile({{ $file->id }})"><i class="fas fa-folder"></i></button>
                                    <button class="btn btn-primary btn-sm" title="Preview" onclick="previewFile('{{ $fileUrl }}', '{{ $ext }}', {{ $file->id }})"><i class="fas fa-eye"></i></button>
                                    <a href="/filehosting/files/{{ $file->id }}/download" class="btn btn-success btn-sm"><i class="fas fa-download"></i></a>
                                    <button class="btn btn-danger btn-sm" title="Delete" onclick="deleteFile({{ $file->id }})"><i class="fas fa-trash"></i></button>
                                    <button class="btn btn-warning btn-sm" onclick="reportFile({{ $file->id }}, '{{ addslashes($file->name) }}')"><i class="fas fa-flag"></i></button>
                                    <button class="btn btn-outline-warning btn-sm" onclick="toggleFavorite({{ $file->id }}, this)"><i class="far fa-star"></i></button>
                                    <button class="btn btn-dark btn-sm" onclick="shareFile({{ $file->id }}, '{{ addslashes($file->name) }}')"><i class="fas fa-share-alt"></i></button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @endforeach

                        @php $rootFiles = $files->filter(fn($f) => is_null($f->folder_id)); @endphp
                        @if($rootFiles->count() > 0)
                        <tr class="folder-row" data-folder-id="root">
                            <td class="expand-icon"><i class="fas fa-chevron-right"></i></td>
                            <td><i class="fas fa-folder-open text-warning me-2"></i><span class="fw-semibold">Root (No Folder)</span></td>
                            <td>—</td>
                            <td>{{ $rootFiles->count() }} files</td>
                            <td>—</td>
                            <td>—</td>
                            <td></td>
                        </tr>
                        @foreach($rootFiles as $file)
                        @php $ext = strtolower(pathinfo($file->name, PATHINFO_EXTENSION)); $fileUrl = url('/uploads/' . str_replace('uploads/', '', $file->path)); @endphp
                        <tr class="file-row expandable" data-parent="root" style="display: none;">
                            <td></td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fas {{ getFileIcon($ext) }} me-2"></i><a href="{{ $fileUrl }}" target="_blank">{{ $file->name }}</a></td>
                            <td>{{ optional($file->owner)->name ?? '—' }}</td>
                            <td>{{ number_format($file->size / 1024, 1) }} KB</td>
                            <td><span class="badge badge-{{ $file->visibility === 'public' ? 'success' : ($file->visibility === 'restricted' ? 'warning' : 'secondary') }}">{{ ucfirst($file->visibility) }}</span></td>
                            <td>{{ $file->created_at->format('Y-m-d') }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-info btn-sm" title="Edit" onclick="editFile({{ $file->id }})"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-secondary btn-sm" title="Move" onclick="moveFile({{ $file->id }})"><i class="fas fa-folder"></i></button>
                                    <button class="btn btn-primary btn-sm" title="Preview" onclick="previewFile('{{ $fileUrl }}', '{{ $ext }}', {{ $file->id }})"><i class="fas fa-eye"></i></button>
                                    <a href="/filehosting/files/{{ $file->id }}/download" class="btn btn-success btn-sm"><i class="fas fa-download"></i></a>
                                    <button class="btn btn-danger btn-sm" title="Delete" onclick="deleteFile({{ $file->id }})"><i class="fas fa-trash"></i></button>
                                    <button class="btn btn-warning btn-sm" onclick="reportFile({{ $file->id }}, '{{ addslashes($file->name) }}')"><i class="fas fa-flag"></i></button>
                                    <button class="btn btn-outline-warning btn-sm" onclick="toggleFavorite({{ $file->id }}, this)"><i class="far fa-star"></i></button>
                                    <button class="btn btn-dark btn-sm" onclick="shareFile({{ $file->id }}, '{{ addslashes($file->name) }}')"><i class="fas fa-share-alt"></i></button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Preview Panel --}}
        <div id="filePreviewPanel" class="glass-panel" style="width: 900px; min-height: 700px; display: none;">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <h6 class="mb-0 fw-bold">Preview</h6>
                <div class="d-flex align-items-center gap-2" id="zoomControls" style="display: none;">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="previewZoom(-10)"><i class="fas fa-minus"></i></button>
                    <span id="zoomLevel" class="badge bg-secondary">100%</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="previewZoom(10)"><i class="fas fa-plus"></i></button>
                    <span class="text-muted ms-2">|</span>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="closePreview()"><i class="fas fa-times"></i></button>
            </div>
            <div style="height: 620px; overflow: auto;" class="scrollbar-thin">
                <iframe id="filePreviewFrame" src="" style="width: 100%; height: 100%; border: 1px solid #e5e7eb; border-radius: 8px;" data-zoom="100"></iframe>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<style>
    .view-btn.active {
        background: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }
</style>
<script>
    var currentView = 'table';
    var searchTimeout;

    /* ── View Switch ─────────────────────────── */
    function switchView(view) {
        currentView = view;
        document.querySelectorAll('.view-btn').forEach(function(btn) {
            btn.classList.toggle('active', btn.dataset.view === view);
        });
        document.getElementById('tableView').style.display = view === 'table' ? '' : 'none';
        document.getElementById('explorerView').style.display = view === 'explorer' ? '' : 'none';
        
        if (view === 'explorer') {
            initExplorerView();
        }
    }

    function initExplorerView() {
        document.querySelectorAll('#explorerTable .folder-row').forEach(function(row) {
            row.style.cursor = 'pointer';
            row.onclick = function() {
                var folderId = this.dataset.folderId;
                var icon = this.querySelector('.expand-icon i');
                var isExpanded = icon.classList.contains('fa-chevron-down');
                
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-right');
                
                document.querySelectorAll('.file-row[data-parent="' + folderId + '"]').forEach(function(fileRow) {
                    fileRow.style.display = isExpanded ? 'none' : '';
                });
            };
        });
    }

    /* ── Search ──────────────────────────────── */
    var searchInput = document.getElementById('fileSearch');
    var searchClear = document.getElementById('searchClear');
    var resultCount = document.getElementById('resultCount');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            var q = this.value.trim();
            searchClear.style.display = q ? 'inline' : 'none';
            
            if (q.length === 0) {
                clearFileSearch();
                return;
            }
            
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                performSearch(q);
            }, 300);
        });
    }

    function clearFileSearch() {
        if (searchInput) {
            searchInput.value = '';
            searchClear.style.display = 'none';
        }
        if (resultCount) resultCount.textContent = '';
        
        // Reset table view
        var tableView = document.getElementById('tableView');
        if (tableView) {
            tableView.querySelectorAll('.file-table-row').forEach(function(row) {
                row.style.display = '';
            });
        }
        
        // Reset explorer view - hide all files, show folders collapsed
        var explorerView = document.getElementById('explorerView');
        if (explorerView) {
            explorerView.querySelectorAll('.file-row').forEach(function(row) {
                row.style.display = 'none';
            });
            explorerView.querySelectorAll('.folder-row').forEach(function(row) {
                row.style.display = '';
                var icon = row.querySelector('.expand-icon i');
                if (icon) {
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-right');
                }
            });
        }
        
        // Switch to table view
        switchView('table');
    }

    function performSearch(q) {
        var tableView = document.getElementById('tableView');
        var explorerView = document.getElementById('explorerView');
        var tableCount = 0;
        var explorerCount = 0;
        
        if (tableView) {
            tableView.querySelectorAll('.file-table-row').forEach(function(row) {
                var text = row.innerText.toLowerCase();
                var match = text.includes(q.toLowerCase());
                row.style.display = match ? '' : 'none';
                if (match) tableCount++;
            });
        }
        
        if (explorerView) {
            var folderRows = explorerView.querySelectorAll('.folder-row');
            var fileRows = explorerView.querySelectorAll('.file-row');
            var visibleFolderIds = [];
            
            fileRows.forEach(function(row) {
                var text = row.innerText.toLowerCase();
                var match = text.includes(q.toLowerCase());
                row.style.display = match ? '' : 'none';
                if (match) {
                    explorerCount++;
                    var parentId = row.dataset.parent;
                    if (parentId && visibleFolderIds.indexOf(parentId) === -1) {
                        visibleFolderIds.push(parentId);
                    }
                }
            });
            
            folderRows.forEach(function(row) {
                var text = row.innerText.toLowerCase();
                var match = text.includes(q.toLowerCase());
                var folderId = row.dataset.folderId;
                var hasVisibleFiles = visibleFolderIds.indexOf(folderId) !== -1;
                var showFolder = match || hasVisibleFiles;
                
                row.style.display = showFolder ? '' : 'none';
                
                var icon = row.querySelector('.expand-icon i');
                if (icon) {
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-right');
                }
                
                if (hasVisibleFiles) {
                    row.querySelector('.expand-icon i').classList.remove('fa-chevron-right');
                    row.querySelector('.expand-icon i').classList.add('fa-chevron-down');
                    explorerView.querySelectorAll('.file-row[data-parent="' + folderId + '"]').forEach(function(fileRow) {
                        fileRow.style.display = '';
                    });
                }
            });
        }
        
        if (resultCount) {
            var total = tableCount + explorerCount;
            resultCount.textContent = total > 0 ? total + ' result(s)' : '0 results';
        }
        
        // Auto switch to explorer view for search results
        switchView('explorer');
    }

    /* ── Init explorer on page load if needed ── */
    if (document.getElementById('explorerView')) {
        initExplorerView();
        // Initially hide all files, show only folders collapsed
        document.querySelectorAll('#explorerView .file-row').forEach(function(row) {
            row.style.display = 'none';
        });
        document.querySelectorAll('#explorerView .folder-row').forEach(function(row) {
            row.style.display = '';
            var icon = row.querySelector('.expand-icon i');
            if (icon) {
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-right');
            }
        });
    }

function previewFile(url, ext, fileId) {
    ext = ext.toLowerCase();
    
    // Show/hide zoom controls based on file type
    const zoomControls = document.getElementById('zoomControls');
    const isDocOrExcel = ['doc', 'docx', 'xls', 'xlsx'].includes(ext);
    zoomControls.style.display = isDocOrExcel ? 'flex' : 'none';
    
    // For PDFs and images, use direct preview
    if (ext === 'pdf' || ext === 'jpg' || ext === 'jpeg' || ext === 'png' || ext === 'gif' || ext === 'webp' || ext === 'bmp') {
        document.getElementById('filePreviewFrame').src = url;
        document.getElementById('filePreviewPanel').style.display = 'block';
        return;
    }
    
    // For Word documents (doc, docx) - use server-side preview
    if (ext === 'doc' || ext === 'docx') {
        document.getElementById('filePreviewFrame').src = '/filehosting/preview/word/' + fileId;
        document.getElementById('filePreviewPanel').style.display = 'block';
        return;
    }
    
    // For Excel files (xls, xlsx) - use server-side preview
    if (ext === 'xls' || ext === 'xlsx') {
        document.getElementById('filePreviewFrame').src = '/filehosting/preview/excel/' + fileId;
        document.getElementById('filePreviewPanel').style.display = 'block';
        return;
    }
    
    // For other files
    Swal.fire({
        title: 'Preview Not Available',
        text: 'Download this file instead?',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Download',
        cancelButtonText: 'Close'
    }).then((result) => {
        if (result.isConfirmed) {
            window.open(url, '_blank');
        }
    });
}

function closePreview() {
    document.getElementById('filePreviewPanel').style.display = 'none';
    document.getElementById('filePreviewFrame').src = '';
    // Reset zoom
    const iframe = document.getElementById('filePreviewFrame');
    iframe.dataset.zoom = '100';
    document.getElementById('zoomLevel').textContent = '100%';
}

function previewZoom(delta) {
    const iframe = document.getElementById('filePreviewFrame');
    let zoom = parseInt(iframe.dataset.zoom) || 100;
    zoom = Math.max(10, Math.min(200, zoom + delta));
    iframe.dataset.zoom = zoom;
    document.getElementById('zoomLevel').textContent = zoom + '%';
    
    // Reload iframe with new zoom
    const currentSrc = iframe.src;
    const url = new URL(currentSrc);
    url.searchParams.set('zoom', zoom);
    iframe.src = url.toString();
}

function editFile(id) {
    // Get current file data
    fetch('/filehosting/files/' + id, {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(file => {
        Swal.fire({
            title: 'Edit File',
            width: '600px',
            html: `
                <div class="text-start">
                    <div class="mb-3">
                        <label class="form-label fw-bold">File Name</label>
                        <input type="text" class="form-control" value="${file.name}" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea id="swal-file-description" class="form-control" rows="3">${file.description || ''}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Visibility</label>
                        <select id="swal-file-visibility" class="form-select">
                            <option value="private" ${file.visibility === 'private' ? 'selected' : ''}>Private</option>
                            <option value="public" ${file.visibility === 'public' ? 'selected' : ''}>Public</option>
                            <option value="restricted" ${file.visibility === 'restricted' ? 'selected' : ''}>Restricted</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Expires At (optional)</label>
                        <input type="datetime-local" id="swal-file-expires" class="form-control" value="${file.expires_at || ''}">
                    </div>
                </div>
            `,
            preConfirm: () => {
                return {
                    description: document.getElementById('swal-file-description').value,
                    visibility: document.getElementById('swal-file-visibility').value,
                    expires_at: document.getElementById('swal-file-expires').value || null
                };
            },
            showCancelButton: true,
            confirmButtonText: 'Save',
            cancelButtonText: 'Cancel'
        }).then(result => {
            if (result.isConfirmed) {
                fetch('/filehosting/files/' + id, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(result.value)
                })
                .then(res => {
                    if (!res.ok) return res.json().then(err => Promise.reject(err));
                    return res.json();
                })
                .then(data => {
                    Swal.fire('Success', 'File updated successfully!', 'success').then(() => location.reload());
                })
                .catch(err => {
                    Swal.fire('Error', err.message || 'Failed to update file.', 'error');
                });
            }
        });
    })
    .catch(err => {
        Swal.fire('Error', 'Failed to load file details.', 'error');
    });
}
function moveFile(id) {
    fetch('/filehosting/folders/tree', {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(folders => {
        function flattenWithDepth(items, depth = 0) {
            let result = [];
            items.forEach(item => {
                result.push({ id: item.id, name: item.name, depth: depth });
                if (item.children && item.children.length > 0) {
                    result = result.concat(flattenWithDepth(item.children, depth + 1));
                }
            });
            return result;
        }
        const flatFolders = flattenWithDepth(folders);
        const folderOptions = flatFolders.map(f => 
            `<option value="${f.id}">${'>'.repeat(f.depth)}${f.depth > 0 ? ' ' : ''}${f.name}</option>`
        ).join('');

        Swal.fire({
            title: 'Move File',
            width: '600px',
            html: `
                <p class="text-muted small mb-2">Select a destination folder, or leave empty to move to root.</p>
                <select id="swal-move-file-folder" class="swal2-select" style="font-size:0.85rem;max-width:300px;">
                    <option value="">— Root (No Folder) —</option>
                    ${folderOptions}
                </select>
            `,
            preConfirm: () => {
                const folderId = document.getElementById('swal-move-file-folder').value;
                return { folder_id: folderId ? parseInt(folderId) : null };
            },
            showCancelButton: true,
            confirmButtonText: 'Move',
            cancelButtonText: 'Cancel'
        }).then(result => {
            if (result.isConfirmed) {
                fetch('/filehosting/files/' + id + '/move', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(result.value)
                })
                .then(res => res.json())
                .then(data => {
                    Swal.fire('Success', 'File moved successfully!', 'success').then(() => location.reload());
                })
                .catch(err => {
                    Swal.fire('Error', 'Failed to move file: ' + err.message, 'error');
                });
            }
        });
    })
    .catch(err => {
        Swal.fire('Error', 'Failed to load folders.', 'error');
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
                Swal.fire('Error', err.message || 'Failed to delete file', 'error');
            });
        }
    });
}

    function toggleFavorite(id, btn) {
        fetch('/filehosting/files/' + id + '/favorite', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.is_favorite) {
                btn.classList.remove('btn-outline-warning');
                btn.classList.add('btn-warning');
                btn.querySelector('i').classList.remove('far');
                btn.querySelector('i').classList.add('fas');
            } else {
                btn.classList.remove('btn-warning');
                btn.classList.add('btn-outline-warning');
                btn.querySelector('i').classList.remove('fas');
                btn.querySelector('i').classList.add('far');
            }
            Swal.fire(data.message, '', 'success');
        })
        .catch(err => {
            Swal.fire('Error', err.message, 'error');
        });
    }

    function shareFile(id, filename) {
        document.getElementById('shareUrlTemplate') && (document.getElementById('shareUrlTemplate').value = id);
        Swal.fire({
            title: 'Share File',
            html: `
                <div style="border: 2px solid #000; border-radius: 8px; padding: 16px; background: #fafafa;">
                    <p class="mb-2"><strong>File:</strong> ${filename}</p>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="shareUrl" value="{{ url('/filehosting/files/shared/') }}/${id}" readonly>
                        <button class="btn btn-outline-dark" onclick="copyShareLink()">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tags (optional):</label>
                        <input type="text" class="form-control" id="shareTags" placeholder="e.g., urgent, review, legal">
                    </div>
                    <button class="btn btn-dark w-100" onclick="createShareLink(${id})">Create Share Link</button>
                    <p class="text-muted small mt-2 mb-0">Copy the link above to share this file.</p>
                </div>
            `,
            icon: 'info',
            showConfirmButton: false
        });
    }

    function copyShareLink() {
        var url = document.getElementById('shareUrl').value;
        navigator.clipboard.writeText(url).then(function() {
            Swal.fire({ title: 'Copied!', text: 'Link copied to clipboard', icon: 'success', timer: 1500, showConfirmButton: false });
        });
    }

    function createShareLink(id) {
        var tags = document.getElementById('shareTags').value;
        fetch('/filehosting/files/' + id + '/share', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ tag_names: tags })
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            document.getElementById('shareUrl').value = data.share_url;
            Swal.fire('Success!', 'Share link created!', 'success');
        })
        .catch(function(err) {
            Swal.fire('Error', err.message, 'error');
        });
    }

    function copyShareLink() {
        var url = document.getElementById('shareUrl').value;
        navigator.clipboard.writeText(url).then(function() {
            Swal.fire({ title: 'Copied!', text: 'Link copied to clipboard', icon: 'success', timer: 1500, showConfirmButton: false });
        });
    }

    function reportFile(id, filename) {
        Swal.fire({
            title: 'Report File: ' + filename,
            input: 'select',
            inputOptions: {
                'broken_404': 'Broken Links (404)',
                'forbidden_403': 'Forbidden (403)',
                'new_version': 'Has New Version',
                'broken_tnc': 'Broken T&C',
                'other': 'Other'
            },
            inputPlaceholder: 'Select a reason',
            showCancelButton: true,
            confirmButtonText: 'Submit Report',
            cancelButtonText: 'Cancel'
        }).then(function(result) {
            if (result.isConfirmed) {
                fetch('/filehosting/files/' + id + '/report', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ reason: result.value })
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    Swal.fire('Thank You!', data.message, 'success');
                })
                .catch(function(err) {
                    Swal.fire('Error', err.message, 'error');
                });
            }
        });
    }

function createFolder() {
    fetch('/filehosting/folders/tree', {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(folders => {
        function flattenWithDepth(items, depth = 0) {
            let result = [];
            items.forEach(item => {
                result.push({ id: item.id, name: item.name, depth: depth });
                if (item.children && item.children.length > 0) {
                    result = result.concat(flattenWithDepth(item.children, depth + 1));
                }
            });
            return result;
        }
        const flatFolders = flattenWithDepth(folders);
        const folderOptions = flatFolders.map(f => 
            `<option value="${f.id}">${'>'.repeat(f.depth)}${f.depth > 0 ? ' ' : ''}${f.name}</option>`
        ).join('');

        Swal.fire({
            title: 'New Folder',
            width: '600px',
            allowDrag: false,
            html: `
                <p class="text-muted small mb-2" style="font-size:0.85rem;">Select a parent folder, or leave empty to create at root.</p>
                <select id="swal-folder-parent" class="swal2-select" style="font-size:0.85rem;max-width:300px;">
                    <option value="">— Root (No Folder) —</option>
                    ${folderOptions}
                </select>
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
                const parentId = document.getElementById('swal-folder-parent').value;
                if (!name.trim()) {
                    Swal.showValidationMessage('Folder name is required');
                    return false;
                }
                return { name, visibility, parent_id: parentId ? parseInt(parentId) : null };
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

function moveFolder(id) {
    fetch('/filehosting/folders/tree', {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(folders => {
        function flattenWithDepth(items, depth = 0) {
            let result = [];
            items.forEach(item => {
                result.push({ id: item.id, name: item.name, depth: depth });
                if (item.children && item.children.length > 0) {
                    result = result.concat(flattenWithDepth(item.children, depth + 1));
                }
            });
            return result;
        }
        const flatFolders = flattenWithDepth(folders);
        const folderOptions = flatFolders.map(f => 
            `<option value="${f.id}">${'>'.repeat(f.depth)}${f.depth > 0 ? ' ' : ''}${f.name}</option>`
        ).join('');

        Swal.fire({
            title: 'Move Folder',
            width: '600px',
            allowDrag: false,
            html: `
                <p class="text-muted small mb-2" style="font-size:0.85rem;">Select a destination folder, or leave empty to move to root.</p>
                <select id="swal-move-parent" class="swal2-select" style="font-size:0.85rem;max-width:300px;">
                    <option value="">— Root (No Folder) —</option>
                    ${folderOptions}
                </select>
            `,
            preConfirm: () => {
                const parentId = document.getElementById('swal-move-parent').value;
                return { parent_id: parentId ? parseInt(parentId) : null };
            },
            showCancelButton: true,
            confirmButtonText: 'Move',
            cancelButtonText: 'Cancel'
        }).then(result => {
            if (result.isConfirmed) {
                fetch('/filehosting/folders/' + id + '/move', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(result.value)
                })
                .then(res => res.json())
                .then(data => {
                    Swal.fire('Success', 'Folder moved successfully!', 'success').then(() => location.reload());
                })
                .catch(err => {
                    console.error('Move error:', err);
                    Swal.fire('Error', 'Failed to move folder: ' + err.message, 'error');
                });
            }
        });
    })
    .catch(err => {
        Swal.fire('Error', 'Failed to load folders.', 'error');
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
