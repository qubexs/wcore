@extends('layouts.admin')

@section('main-content')
<h1>File Hosting - Upload</h1>

<!-- Flash messages -->
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
        <form action="{{ route('filehosting.upload') }}" method="POST" enctype="multipart/form-data" class="row g-3">
            @csrf
            <div class="col-md-6">
                <input type="file" name="file" class="form-control" required>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary">Upload</button>
            </div>
        </form>
    </div>
</div>

<!-- Files Table -->
<div class="card">
    <div class="card-header">Uploaded Files</div>
    <div class="card-body table-responsive">
        @php
            // Ensure $files is always defined
            $files = $files ?? collect();
        @endphp
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Thumbnail</th>
                    <th>Uploaded By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($files as $file)
                    <tr>
                        <td>
                            <a href="{{ Storage::url($file->path) }}" target="_blank">{{ $file->name }}</a>
                        </td>
                        <td>
                            @if($file->thumbnail)
                                <img src="{{ Storage::url($file->thumbnail) }}" width="50" class="img-thumbnail" alt="Thumbnail">
                            @else
                                &mdash;
                            @endif
                        </td>
                        <td>{{ $file->uploaded_by ?? 'N/A' }}</td>
                        <td>
                            <form action="{{ route('filehosting.delete', $file) }}" method="POST" onsubmit="return confirm('Delete this file?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">No files uploaded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
