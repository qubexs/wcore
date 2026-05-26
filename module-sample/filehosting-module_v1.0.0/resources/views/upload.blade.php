@extends('layouts.admin')

@section('title', 'Upload Files')

@section('main-content')
<div class="container-fluid mt-4" style="padding-top: 2rem;">
    <div class="glass-panel p-4 mb-5">

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

                <div id="dropzoneError" class="text-danger text-center mt-2 fw-bold" style="display:none;"></div>
            </div>

            {{-- Options --}}
            <div class="mt-4 row g-3">
                <div class="col-md-6">
                    <label class="form-label">Destination Folder</label>
                    <select name="folder_id" class="form-select">
                        <option value="">— Root (no folder) —</option>
                    </select>
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
</div>
@endsection