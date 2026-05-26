@extends('layouts.admin')

@section('title', 'Module Management')

@section('main-content')
<div class="fh-container" style="padding-top:3.5rem;">

    <div class="fh-header">
        <div class="fh-header__left">
            <h1 class="fh-page-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                Module Management
            </h1>
        </div>
    </div>

{{-- 1. MODULE UPLOAD PANEL --}}
<div class="fh-settings-grid">
    <div class="fh-setting-row">
        <div class="fh-setting-row__label">
            <span>Install New Module</span>
            <span class="fh-setting-row__hint">Upload a .zip file to extend functionality</span>
        </div>
    </div>
</div>

<form id="moduleUploadForm" action="{{ route('modules.install') }}" method="POST" enctype="multipart/form-data" style="margin-top: 1rem;">
    @csrf

    <div class="module-dropzone" id="moduleDropzone" data-accept=".zip" data-field-name="module_zip" style="
        border: 2px dashed #d1d5db;
        border-radius: 0.5rem;
        padding: 2rem;
        background: #f9fafb;
        transition: all 0.3s ease;
        cursor: pointer;
        text-align: center;
    ">
        <input type="file" id="moduleFileInput" name="module_zip" accept=".zip" style="display:none;">
        
        <div class="dropzone-content">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 3rem; height: 3rem; color: #6b7280; margin-bottom: 0.75rem;">
                <path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            <h5 style="font-weight: 600; color: #111827; margin-bottom: 0.25rem;">Drag & Drop your .zip file here</h5>
            <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.75rem;">or click to browse files</p>

            <button type="button" id="dropzoneBrowseBtn" class="fh-btn" style="background: #2563eb; color: #fff; border: none;">
                Choose File
            </button>
        </div>

        <div id="dropzoneFileInfo" class="mt-3" style="display: none;">
            <div style="display: inline-flex; align-items: center; background: #fff; padding: 0.5rem 1rem; border-radius: 0.375rem; border: 1px solid #e5e7eb;">
                <svg viewBox="0 0 20 20" fill="currentColor" style="width: 1rem; height: 1rem; color: #10b981; margin-right: 0.5rem;"><path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd"/></svg>
                <span id="dropzoneFileName" style="font-weight: 600; margin-right: 0.5rem;">filename.zip</span>
                <span id="dropzoneFileSize" style="color: #6b7280; font-size: 0.875rem; margin-right: 0.5rem;">(0 MB)</span>
                <svg id="dropzoneFileRemove" viewBox="0 0 20 20" fill="currentColor" style="width: 1rem; height: 1rem; color: #ef4444; cursor: pointer;" title="Remove"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </div>
        </div>

        <div id="dropzoneError" style="color: #ef4444; text-align: center; margin-top: 0.5rem; font-weight: 500; display: none;"></div>
    </div>

    <div style="text-align: center; margin-top: 1rem;">
        <button type="submit" id="moduleUploadBtn" class="fh-btn" style="background: #10b981; color: #fff; border: none; display: none;">
            Upload & Install
        </button>
    </div>
</form>


{{-- 2. MODULE LIST TABLE --}}
<div class="fh-settings-grid" style="margin-top: 1.5rem;">
    <div class="fh-setting-row" style="justify-content: space-between; font-weight: 600; background: #f3f4f6;">
        <span>Name</span>
        <span>Slug</span>
        <span>Version</span>
        <span>Status</span>
        <span>Actions</span>
    </div>
    @foreach($registry->all() as $module)
        @php $dbModule = $modules->firstWhere('slug', $module['slug']); @endphp
        <div class="fh-setting-row">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 2rem; height: 2rem; background: linear-gradient(135deg, #3b82f6, #1d4ed8); border-radius: 0.375rem; display: flex; align-items: center; justify-content: center;">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width: 1rem; height: 1rem; color: #fff;"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                </div>
                <span style="font-weight: 600;">{{ $module['name'] }}</span>
            </div>
            <code style="color: #6b7280;">{{ $module['slug'] }}</code>
            <span style="background: #e5e7eb; padding: 0.125rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500;">{{ $module['version'] ?? '1.0.0' }}</span>
            <span>
                @if($dbModule && $dbModule->active)
                    <span style="color: #10b981; font-weight: 500; font-size: 0.875rem;">● Active</span>
                @else
                    <span style="color: #6b7280; font-weight: 500; font-size: 0.875rem;">● Inactive</span>
                @endif
            </span>
            <div style="display: flex; gap: 0.5rem;">
                @if($dbModule && $dbModule->active)
                    <form action="{{ route('modules.deactivate', $module['slug']) }}" method="POST">@csrf <button type="submit" style="background: none; border: 1px solid #d1d5db; padding: 0.25rem 0.5rem; border-radius: 0.25rem; cursor: pointer; color: #f59e0b;" title="Deactivate">⏸</button></form>
                @else
                    <form action="{{ route('modules.activate', $module['slug']) }}" method="POST">@csrf <button type="submit" style="background: none; border: 1px solid #d1d5db; padding: 0.25rem 0.5rem; border-radius: 0.25rem; cursor: pointer; color: #10b981;" title="Activate">▶</button></form>
                @endif
                <a href="{{ route('modules.settings', $module['slug']) }}" style="background: none; border: 1px solid #d1d5db; padding: 0.25rem 0.5rem; border-radius: 0.25rem; cursor: pointer; color: #2563eb; text-decoration: none;">⚙</a>
                <form action="{{ route('modules.uninstall', $module['slug']) }}" method="POST" data-confirm-uninstall>
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="keep_data" value="0">
                    <button type="submit" style="background: none; border: 1px solid #d1d5db; padding: 0.25rem 0.5rem; border-radius: 0.25rem; cursor: pointer; color: #ef4444;">🗑</button>
                </form>
            </div>
        </div>
    @endforeach
</div>

@push('styles')
@include('filehosting::_partials.styles')
@endpush

@endsection