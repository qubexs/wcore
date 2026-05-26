{{-- resources/views/filehosting/upload.blade.php --}}
@extends('layouts.admin')

@section('main-content')
<div class="container-fluid mt-4">
    <h1>File Hosting - UploadX</h1>

    {{-- Success / Error Messages --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Upload Form --}}
    <div class="card mb-4">
        <div class="card-header">Upload New File</div>
        <div class="card-body">
            <form action="{{ route('filehosting.upload.post') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <input type="file" name="file" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Files Table --}}
    <div class="card">
        <div class="card-header">Uploaded Files</div>
        <div class="card-body table-responsive">
            @php $files = $files ?? collect(); @endphp
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Thumbnail</th>
                        <th>Uploaded By</th>
                        <th>Uploaded At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($files as $file)
                        @php
                            $fileUrl = Storage::url($file->path);
                            $ext = strtolower(pathinfo($file->name, PATHINFO_EXTENSION));
                            $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                            $isPdf = ($ext === 'pdf');
                            $isVideo = in_array($ext, ['mp4','webm','ogg']);
                        @endphp
                        <tr>
                            {{-- File Name with Hover Preview --}}
                            <td>
                                <div class="hover-trigger"
                                     data-preview-type="{{ $isImage ? 'image' : ($isPdf ? 'pdf' : ($isVideo ? 'video' : '')) }}"
                                     data-url="{{ $fileUrl }}">
                                    <a href="{{ $fileUrl }}" target="_blank">{{ $file->name }}</a>
                                </div>
                            </td>

                            {{-- Thumbnail --}}
                            <td>
                                @if($file->thumbnail)
                                    <img src="{{ Storage::url($file->thumbnail) }}" width="50" class="img-thumbnail">
                                @elseif($isPdf)
                                    <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                @elseif($isVideo)
                                    <i class="fas fa-file-video fa-2x text-primary"></i>
                                @else
                                    <i class="fas fa-file fa-2x text-secondary"></i>
                                @endif
                            </td>

                            {{-- Uploaded By --}}
                            <td>{{ optional($file->uploader)->name ?? $file->uploaded_by ?? 'N/A' }}</td>

                            {{-- Uploaded At --}}
                            <td>{{ $file->created_at->format('d M Y, H:i') }}</td>

                            {{-- Actions --}}
                            <td class="d-flex gap-1">
                                <a href="{{ route('filehosting.download', $file) }}"
                                   class="btn btn-success btn-sm" title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                                <form action="{{ route('filehosting.delete', $file) }}" method="POST"
                                      onsubmit="return confirm('Delete this file?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No files uploaded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Hover Preview Container --}}
<div id="global-preview" class="global-preview">
    <img id="preview-img" src="" alt="Preview">
    <iframe id="preview-pdf" src=""></iframe>
    <video id="preview-video" src="" autoplay muted loop></video>
</div>

<style>
.hover-trigger {
    position: relative;
    display: inline-block;
    cursor: pointer;
}
.global-preview {
    display: none;
    position: fixed;
    width: 400px;
    height: 300px;
    border: 1px solid rgba(255,255,255,0.15);
    box-shadow: 0 10px 40px rgba(0,0,0,0.4);
    border-radius: 12px;
    background: #1a1f2e;
    z-index: 9999;
    pointer-events: none;
    overflow: hidden;
}
.global-preview img,
.global-preview iframe,
.global-preview video {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: none;
}
.global-preview.active-img #preview-img { display: block; }
.global-preview.active-pdf #preview-pdf { display: block; }
.global-preview.active-video #preview-video { display: block; }
</style>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const preview = document.getElementById('global-preview');
    const previewImg = document.getElementById('preview-img');
    const previewPdf = document.getElementById('preview-pdf');
    const previewVideo = document.getElementById('preview-video');

    document.querySelectorAll('.hover-trigger').forEach(trigger => {
        trigger.addEventListener('mouseenter', function (e) {
            const type = this.dataset.previewType;
            const url  = this.dataset.url;
            if (!type) return;

            preview.className = 'global-preview';

            if (type === 'image')      { previewImg.src   = url; preview.classList.add('active-img'); }
            else if (type === 'pdf')   { previewPdf.src   = url; preview.classList.add('active-pdf'); }
            else if (type === 'video') { previewVideo.src = url; preview.classList.add('active-video'); }

            preview.style.display = 'block';
            positionPreview(e);
        });

        trigger.addEventListener('mousemove', positionPreview);

        trigger.addEventListener('mouseleave', function () {
            preview.style.display = 'none';
            previewImg.src = previewPdf.src = previewVideo.src = '';
        });
    });

    function positionPreview(e) {
        let left = e.clientX + 20;
        let top  = e.clientY - 150;
        if (left + 400 > window.innerWidth)  left = e.clientX - 420;
        if (top < 0)                          top  = 10;
        if (top + 300 > window.innerHeight)   top  = window.innerHeight - 310;
        preview.style.left = left + 'px';
        preview.style.top  = top  + 'px';
    }
});
</script>
@endpush