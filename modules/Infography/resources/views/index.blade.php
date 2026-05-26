@extends('layouts.admin')

@section('main-content')
<div class="container-fluid mt-4">
    <h2 class="mb-4">Infographics - Hover Preview</h2>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="thead-dark">
                <tr>
                    <th>Infographic</th>
                    <th>Thumbnail</th>
                    <th>Uploaded By</th>
                    <th>Uploaded At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($files as $file)
                    @php
                        $url = Storage::url($file->path);
                        $ext = strtolower(pathinfo($file->name, PATHINFO_EXTENSION));
                        $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                        $isPdf = ($ext === 'pdf');
                        $isVideo = in_array($ext, ['mp4','webm','ogg']);
                    @endphp
                    <tr>
                        <td>
                            <div class="hover-trigger" 
                                 data-preview-type="{{ $isImage ? 'image' : ($isPdf ? 'pdf' : ($isVideo ? 'video' : '')) }}"
                                 data-url="{{ $url }}">
                                <a href="{{ $url }}" target="_blank">{{ $file->name }}</a>
                            </div>
                        </td>
                        <td>
                            @if($file->thumbnail)
                                <img src="{{ Storage::url($file->thumbnail) }}" width="50" class="img-thumbnail">
                            @else
                                <i class="fas fa-file fa-2x text-muted"></i>
                            @endif
                        </td>
                        <td>{{ optional($file->uploader)->name ?? $file->uploaded_by ?? 'N/A' }}</td>
                        <td>{{ $file->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <form action="{{ route('infographic.delete', $file) }}" method="POST" onsubmit="return confirm('Delete infographic?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No infographics uploaded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Global Preview Container -->
<div id="global-preview" class="global-preview">
    <img id="preview-img" src="" alt="Preview">
    <iframe id="preview-pdf" src=""></iframe>
    <video id="preview-video" src="" autoplay muted loop></video>
</div>

<!-- Styles -->
<style>
.hover-trigger { position: relative; display: inline-block; cursor: pointer; }

.global-preview {
    display: none;
    position: fixed;
    width: 400px;
    height: 500px;
    border: 1px solid #ddd;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    border-radius: 8px;
    background: #fff;
    z-index: 9999;
    pointer-events: none;
    overflow: hidden;
}

.global-preview img,
.global-preview iframe,
.global-preview video { width: 100%; height: 100%; object-fit: contain; display: none; }

.global-preview.active-img #preview-img { display: block; }
.global-preview.active-pdf #preview-pdf { display: block; }
.global-preview.active-video #preview-video { display: block; }
</style>

<!-- JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const preview = document.getElementById('global-preview');
    const previewImg = document.getElementById('preview-img');
    const previewPdf = document.getElementById('preview-pdf');
    const previewVideo = document.getElementById('preview-video');
    const triggers = document.querySelectorAll('.hover-trigger');

    triggers.forEach(trigger => {
        trigger.addEventListener('mouseenter', function(e) {
            const type = this.dataset.previewType;
            const url = this.dataset.url;
            if (!type) return;

            preview.className = 'global-preview';

            if (type === 'image') {
                previewImg.src = url;
                preview.classList.add('active-img');
            } else if (type === 'pdf') {
                previewPdf.src = url;
                preview.classList.add('active-pdf');
            } else if (type === 'video') {
                previewVideo.src = url;
                preview.classList.add('active-video');
            }

            preview.style.display = 'block';
            positionPreview(e);
        });

        trigger.addEventListener('mousemove', positionPreview);
        trigger.addEventListener('mouseleave', function() {
            preview.style.display = 'none';
            previewImg.src = '';
            previewPdf.src = '';
            previewVideo.src = '';
        });
    });

    function positionPreview(e) {
        const offset = 20;
        let left = e.clientX + offset;
        let top = e.clientY - 250;

        if (left + 400 > window.innerWidth) left = e.clientX - 420;
        if (top < 0) top = 10;
        if (top + 500 > window.innerHeight) top = window.innerHeight - 510;

        preview.style.left = left + 'px';
        preview.style.top = top + 'px';
    }
});
</script>
@endsection