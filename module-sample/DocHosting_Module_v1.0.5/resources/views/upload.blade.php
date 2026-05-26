<!-- resources/views/upload.blade.php -->
@extends('layouts.admin')

@section('main-content')
<div class="container-fluid mt-4">
    <h1>File Hosting - UploadX</h1>

    <!-- Success / Error Messages -->
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

    <!-- Upload Form -->
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

    <!-- Files Table -->
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
                        @endphp
                        <tr>
                            <!-- File Name & Hover Preview -->
                            <td>
                                <a href="{{ $fileUrl }}" target="_blank"
                                   class="file-preview-link"
                                   data-toggle="popover"
                                   data-html="true"
                                   data-trigger="hover"
                                   title="{{ $file->name }}"
                                   data-content='
                                        @if($ext === "pdf")
                                            <embed src="{{ $fileUrl }}" type="application/pdf" width="250" height="300">
                                        @elseif(in_array($ext, ["mp4","webm","ogg"]))
                                            <video width="250" height="150" controls>
                                                <source src="{{ $fileUrl }}" type="video/{{ $ext }}">
                                                Your browser does not support the video tag.
                                            </video>
                                        @elseif($file->thumbnail)
                                            <img src="{{ Storage::url($file->thumbnail) }}" width="250">
                                        @else
                                            <i class="fas fa-file fa-3x"></i>
                                        @endif
                                   '>
                                    {{ $file->name }}
                                </a>
                            </td>

                            <!-- Thumbnail -->
                            <td>
                                @if($file->thumbnail)
                                    <img src="{{ Storage::url($file->thumbnail) }}" width="50" class="img-thumbnail">
                                @elseif($ext === 'pdf')
                                    <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                @elseif(in_array($ext, ['mp4','webm','ogg']))
                                    <i class="fas fa-file-video fa-2x text-primary"></i>
                                @else
                                    <i class="fas fa-file fa-2x text-secondary"></i>
                                @endif
                            </td>

                            <!-- Uploaded By -->
                            <td>{{ optional($file->uploader)->name ?? $file->uploaded_by ?? 'N/A' }}</td>

                            <!-- Uploaded At -->
                            <td>{{ $file->created_at->format('d M Y, H:i') }}</td>

                            <!-- Actions -->
                            <td class="d-flex gap-1">
                                <a href="{{ route('filehosting.download', $file) }}" class="btn btn-success btn-sm" title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                                <form action="{{ route('filehosting.delete', $file) }}" method="POST" onsubmit="return confirm('Delete this file?')">
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
@endsection

@section('scripts')
<script>
$(document).ready(function(){
    $('.file-preview-link').popover({
        trigger: 'hover',
        html: true,
        placement: 'right'
    });
});
</script>
@endsection
