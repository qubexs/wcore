@extends('layouts.admin')

@section('main-content')
<h1>File Hosting</h1>

<!-- Upload Form -->
<form action="{{ route('filehosting.upload') }}" method="POST" enctype="multipart/form-data" class="mb-4">
    @csrf
    <input type="file" name="file" required>
    <button class="btn btn-primary">Upload</button>
</form>

<!-- File Table -->
<table class="table table-bordered">
    <thead>
        <tr>
            <th>File</th>
            <th>Thumbnail</th>
            <th>Uploaded By</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($files as $file)
            <tr>
                <td><a href="{{ Storage::url($file->path) }}" target="_blank">{{ $file->name }}</a></td>
                <td>
                    @if($file->thumbnail)
                        <img src="{{ Storage::url($file->thumbnail) }}" width="50">
                    @endif
                </td>
                <td>{{ $file->uploaded_by }}</td>
                <td>
                    <form action="{{ route('filehosting.delete', $file) }}" method="POST" onsubmit="return confirm('Delete file?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
