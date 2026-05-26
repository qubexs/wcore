<!-- resources/views/all.blade.php -->
@extends('layouts.admin')

@section('main-content')
<div class="container-fluid mt-4">
    <h1 class="mb-4">File Hosting</h1>

    <!-- Success / Error Messages -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- File Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="thead-dark">
                <tr>
                    <th>File</th>
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
                        <!-- File Name with Hover Preview -->
                        <td>
                            @if($ext === 'pdf')
                                <a href="{{ $fileUrl }}"
                                   target="_blank"
                                   class="pdf-preview"
                                   data-toggle="popover"
                                   data-trigger="hover"
                                   data-html="true"
                                   data-pdf="{{ $fileUrl }}"
                                   title="{{ $file->name }}">
                                    {{ $file->name }}
                                </a>
                            @elseif($file->thumbnail)
                                <a href="{{ $fileUrl }}"
                                   target="_blank"
                                   data-toggle="popover"
                                   data-trigger="hover"
                                   data-html="true"
                                   data-content="<img src='{{ Storage::url($file->thumbnail) }}' style='max-width:300px;'>"
                                   title="{{ $file->name }}">
                                    {{ $file->name }}
                                </a>
                            @else
                                <a href="{{ $fileUrl }}" target="_blank">
                                    {{ $file->name }}
                                </a>
                            @endif
                        </td>

                        <!-- Thumbnail -->
                        <td>
                            @if($file->thumbnail)
                                <img src="{{ Storage::url($file->thumbnail) }}" width="50" class="img-thumbnail" alt="Thumbnail">
                            @else
                                <i class="fas fa-file fa-2x text-muted"></i>
                            @endif
                        </td>

                        <!-- Uploaded By -->
                        <td>{{ optional($file->uploader)->name ?? $file->uploaded_by ?? 'N/A' }}</td>

                        <!-- Uploaded At -->
                        <td>{{ $file->created_at->format('Y-m-d H:i') }}</td>

                        <!-- Actions -->
                        <td>
                            <form action="{{ route('filehosting.delete', $file) }}"
                                  method="POST"
                                  onsubmit="return confirm('Delete file?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash-alt mr-1"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No files uploaded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection


@section('scripts')
<script>
$(document).ready(function(){

    // PDF hover preview
    $('.pdf-preview').popover({
        placement: 'right',
        trigger: 'hover',
        html: true,
        content: function() {
            var pdfUrl = $(this).data('pdf');
            return '<iframe src="' + pdfUrl + '" width="400" height="500" style="border:none;"></iframe>';
        }
    });

    // Image hover preview
    $('[data-toggle="popover"]').not('.pdf-preview').popover({
        trigger: 'hover',
        html: true,
        placement: 'right'
    });

});
</script>
@endsection
