<!-- resources/views/upload.blade.php -->
@extends('layouts.admin')

@section('title', 'Upload Files - Document Hosting')

@section('main-content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-cloud-upload-alt me-2"></i>Upload Files</h4>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('filehosting.files.store') }}" method="POST" enctype="multipart/form-data" id="advancedUploadForm">
                        @csrf
                        
                        <!-- Destination Folder -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Destination Folder</label>
                            <select name="folder_id" class="form-select form-select-lg">
                                <option value="">Root Directory</option>
                                @foreach($allFolders as $folder)
                                    <option value="{{ $folder->id }}" {{ request('folder') == $folder->id ? 'selected' : '' }}>
                                        {{ str_repeat('—', $folder->depth) }} {{ $folder->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Drop Zone -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Select Files</label>
                            <div class="border border-3 border-dashed rounded-3 p-5 text-center bg-light" id="advancedDropZone">
                                <i class="fas fa-cloud-upload-alt fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">Drag & drop files here</h5>
                                <p class="text-muted mb-3">or click to browse</p>
                                <input type="file" name="files[]" multiple class="d-none" id="advancedFileInput" accept="{{ $allowedMimeTypes ?? '*' }}">
                                <button type="button" class="btn btn-outline-primary btn-lg" onclick="document.getElementById('advancedFileInput').click()">
                                    <i class="fas fa-folder-open me-2"></i>Browse Files
                                </button>
                                <div class="mt-3 text-muted small">
                                    Max file size: {{ $maxFileSize ?? '100MB' }} | 
                                    Allowed: {{ $allowedExtensions ?? 'All files' }}
                                </div>
                            </div>
                        </div>

                        <!-- File Preview List -->
                        <div id="uploadQueue" class="mb-4">
                            <!-- Dynamic file list will appear here -->
                        </div>

                        <!-- Upload Options -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Visibility</label>
                                <select name="visibility" class="form-select">
                                    <option value="private">🔒 Private (Only me)</option>
                                    <option value="public">🌍 Public (Everyone)</option>
                                    <option value="restricted">👥 Restricted (Specific users)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Expiration (Optional)</label>
                                <input type="datetime-local" name="expires_at" class="form-control">
                                <div class="form-text">Leave empty for no expiration</div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Optional description for these files..."></textarea>
                        </div>

                        <!-- Progress Bar -->
                        <div class="progress mb-3 d-none" id="uploadProgress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg" id="startUploadBtn" disabled>
                                <i class="fas fa-upload me-2"></i>Start Upload
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Upload Tips -->
            <div class="card mt-4 border-info">
                <div class="card-body">
                    <h6 class="text-info"><i class="fas fa-lightbulb me-2"></i>Tips</h6>
                    <ul class="mb-0 small text-muted">
                        <li>You can upload multiple files at once</li>
                        <li>Large files may take time to process</li>
                        <li>Set expiration dates for temporary files</li>
                        <li>Use folders to organize your documents</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const dropZone = document.getElementById('advancedDropZone');
const fileInput = document.getElementById('advancedFileInput');
const uploadQueue = document.getElementById('uploadQueue');
const startBtn = document.getElementById('startUploadBtn');
let filesToUpload = [];

// Drag & Drop handlers
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    dropZone.addEventListener(eventName, () => dropZone.classList.add('border-primary', 'bg-white'), false);
});

['dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, () => dropZone.classList.remove('border-primary', 'bg-white'), false);
});

dropZone.addEventListener('drop', handleDrop, false);
fileInput.addEventListener('change', handleFiles, false);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    handleFiles({ target: { files: files } });
}

function handleFiles(e) {
    const files = [...e.target.files];
    files.forEach(file => {
        if (!filesToUpload.find(f => f.name === file.name)) {
            filesToUpload.push(file);
            addFileToQueue(file);
        }
    });
    updateSubmitButton();
}

function addFileToQueue(file) {
    const fileId = Math.random().toString(36).substr(2, 9);
    const fileSize = (file.size / (1024 * 1024)).toFixed(2);
    
    const html = `
        <div class="d-flex align-items-center p-3 border rounded mb-2 bg-white" id="file-${fileId}">
            <div class="flex-shrink-0">
                <i class="fas fa-file fa-2x text-primary me-3"></i>
            </div>
            <div class="flex-grow-1">
                <h6 class="mb-1">${file.name}</h6>
                <small class="text-muted">${fileSize} MB • ${file.type || 'Unknown type'}</small>
            </div>
            <div class="flex-shrink-0">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile('${fileId}')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    uploadQueue.insertAdjacentHTML('beforeend', html);
}

function removeFile(fileId) {
    document.getElementById(`file-${fileId}`).remove();
    filesToUpload = filesToUpload.filter((f, index) => {
        const el = document.getElementById(`file-${Math.random().toString(36).substr(2, 9)}`);
        return document.getElementById(`file-${fileId}`) !== null;
    });
    updateSubmitButton();
}

function updateSubmitButton() {
    startBtn.disabled = filesToUpload.length === 0;
    startBtn.innerHTML = `<i class="fas fa-upload me-2"></i>Upload ${filesToUpload.length} File(s)`;
}

// AJAX Upload with Progress
document.getElementById('advancedUploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.delete('files[]');
    filesToUpload.forEach(file => formData.append('files[]', file));
    
    const progressBar = document.querySelector('#uploadProgress');
    const progressBarInner = progressBar.querySelector('.progress-bar');
    progressBar.classList.remove('d-none');
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        onUploadProgress: (progressEvent) => {
            const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
            progressBarInner.style.width = percentCompleted + '%';
            progressBarInner.textContent = percentCompleted + '%';
        }
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            window.location.href = data.redirect || '{{ route("filehosting.index") }}';
        } else {
            alert('Upload failed: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Upload failed. Please try again.');
    });
});
</script>
@endpush
@endsection