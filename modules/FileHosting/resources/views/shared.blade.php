@extends('layouts.admin')
@section('title', 'Shared File: ' . $file->name)
@section('main-content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-share-alt me-2"></i> Shared File</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="140">File Name:</th>
                            <td>{{ $file->name }}</td>
                        </tr>
                        <tr>
                            <th>Size:</th>
                            <td>{{ $file->size ? number_format($file->size / 1024, 1) . ' KB' : '—' }}</td>
                        </tr>
                        <tr>
                            <th>Folder:</th>
                            <td>{{ $file->folder?->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Shared By:</th>
                            <td>{{ optional($file->owner)->name ?? 'Unknown' }}</td>
                        </tr>
                        @if($share->tag_names)
                        <tr>
                            <th>Tags:</th>
                            <td>
                                @foreach(explode(',', $share->tag_names) as $tag)
                                    <span class="badge bg-secondary">{{ trim($tag) }}</span>
                                @endforeach
                            </td>
                        </tr>
                        @endif
                    </table>
                    
                    <hr>
                    
                    <div class="d-flex gap-2">
                        <a href="{{ route('filehosting.files.download', $file->id) }}" class="btn btn-success">
                            <i class="fas fa-download me-1"></i> Download
                        </a>
                        <a href="{{ $fileUrl }}" target="_blank" class="btn btn-outline-primary">
                            <i class="fas fa-eye me-1"></i> Preview
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
