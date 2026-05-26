<!-- resources/views/all.blade.php -->
@extends('layouts.admin')

@section('title', 'All Files')

@section('main-content')
@include('filehosting::_partials.styles')
<div class="fh-container" style="padding-top: 3.5rem;">

    {{-- Header (Upload button removed) --}}
    <div class="fh-header">
        <div class="fh-header__left">
            <h1 class="fh-page-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                All Files
            </h1>
        </div>
    </div>

    {{-- Success Toast --}}
    @if(session('success'))
        <div class="fh-toast fh-toast--success">✓ {{ session('success') }}</div>
    @endif

    {{-- Centered Search Bar --}}
    <div class="d-flex justify-content-center mb-4">
        <div style="width: 100%; max-width: 540px; position: relative;">
            <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none;">
                <svg viewBox="0 0 20 20" fill="currentColor" style="width: 1.1rem; height: 1.1rem;">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                </svg>
            </span>
            <input
                type="text"
                id="fileSearch"
                placeholder="Search files or folders…"
                autocomplete="off"
                style="
                    width: 100%;
                    padding: 0.6rem 2.5rem 0.6rem 2.5rem;
                    border: 1.5px solid #d1d5db;
                    border-radius: 999px;
                    font-size: 0.95rem;
                    outline: none;
                    box-shadow: 0 1px 6px rgba(0,0,0,0.07);
                    transition: border-color 0.2s, box-shadow 0.2s;
                "
                onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.15)';"
                onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='0 1px 6px rgba(0,0,0,0.07)';"
            >
            <span id="searchClear"
                  onclick="clearSearch()"
                  title="Clear"
                  style="display:none; position:absolute; right:14px; top:50%; transform:translateY(-50%); cursor:pointer; color:#9ca3af;">
                <svg viewBox="0 0 20 20" fill="currentColor" style="width:1rem;height:1rem;">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            </span>
        </div>
    </div>

    {{-- Flex wrapper: table + preview panel side-by-side --}}
    <div class="d-flex gap-3">

        <div class="flex-grow-1 glass-panel">
            <div class="table-responsive">
                <table class="fh-table" id="filesTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Folder</th>
                            <th>Uploaded By</th>
                            <th>Size</th>
                            <th>Visibility</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{--
                            Sort: files WITH a folder first (sorted by folder name),
                            then files with no folder at the bottom (sorted by filename).
                        --}}
                        @php
                            $withFolder    = $files->filter(fn($f) => !is_null($f->folder_id))
                                                   ->sortBy(fn($f) => optional($f->folder)->name);
                            $withoutFolder = $files->filter(fn($f) => is_null($f->folder_id))
                                                   ->sortBy('name');
                            $sorted        = $withFolder->concat($withoutFolder);
                        @endphp

                        @forelse($sorted as $file)
                            @php
                                $ext     = strtolower(pathinfo($file->name, PATHINFO_EXTENSION));
                                $fileUrl = \Storage::url($file->path);
                            @endphp
                            <tr class="file-row">
                                <td data-label="Name">
                                    @if($ext === 'pdf')
                                        <a href="{{ $fileUrl }}" target="_blank"
                                           class="pdf-preview"
                                           data-pdf="{{ $fileUrl }}">{{ $file->name }}</a>
                                    @else
                                        <a href="{{ $fileUrl }}" target="_blank">{{ $file->name }}</a>
                                    @endif
                                </td>
                                <td data-label="Folder">
                                    @if($file->folder)
                                        <span style="display:inline-flex;align-items:center;gap:0.3rem;">
                                            <svg viewBox="0 0 20 20" fill="currentColor"
                                                 style="width:.9rem;height:.9rem;color:#f59e0b;">
                                                <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                                            </svg>
                                            {{ $file->folder->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td data-label="Uploaded By">{{ optional($file->owner)->name ?? $file->uploaded_by ?? 'N/A' }}</td>
                                <td data-label="Size">{{ $file->size ? number_format($file->size / 1024, 1) . ' KB' : '—' }}</td>
                                <td data-label="Visibility">
                                    <span class="badge badge-{{ $file->visibility === 'public' ? 'success' : ($file->visibility === 'restricted' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($file->visibility ?? 'public') }}
                                    </span>
                                </td>
                                <td data-label="Created">{{ $file->created_at->format('Y-m-d H:i') }}</td>
                                <td data-label="Actions">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-primary btn-sm" title="Preview"
                                                onclick="previewFile('{{ $fileUrl }}', '{{ $ext }}')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-info btn-sm" title="Edit"
                                                onclick="editFile({{ $file->id }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-secondary btn-sm" title="Move"
                                                onclick="moveFile({{ $file->id }})">
                                            <i class="fas fa-folder"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" title="Delete"
                                                onclick="deleteFile({{ $file->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyRow">
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-folder-open fa-2x text-muted mb-2"></i>
                                    <p class="mb-0 text-muted">No files found.</p>
                                </td>
                            </tr>
                        @endforelse

                        {{-- Shown when search yields no results --}}
                        <tr id="noSearchResult" style="display:none;">
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-search fa-2x text-muted mb-2"></i>
                                <p class="mb-0 text-muted">No files match your search.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if($files->hasPages())
                <div class="px-3 py-2 border-top mt-2">
                    {{ $files->links() }}
                </div>
            @endif
        </div>

        {{-- Preview Panel --}}
        <div id="filePreviewPanel" class="glass-panel"
             style="width: 700px; min-height: 600px; display: none;">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <h6 class="mb-0 fw-bold">Preview</h6>
                <button type="button" class="btn btn-sm btn-outline-secondary"
                        onclick="closePreview()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div style="height: 550px;">
                <iframe id="filePreviewFrame" src=""
                        style="width: 100%; height: 100%; border: 1px solid #e5e7eb; border-radius: 8px;"></iframe>
            </div>
        </div>

    </div>{{-- end d-flex --}}

</div>{{-- end fh-container --}}
@endsection

@push('scripts')
<script>
    /* ── Search ─────────────────────────────────────────── */
    var searchInput = document.getElementById('fileSearch');
    var searchClear = document.getElementById('searchClear');

    searchInput.addEventListener('input', function () {
        var q = this.value.trim().toLowerCase();
        searchClear.style.display = q ? 'inline' : 'none';
        filterTable(q);
    });

    function clearSearch() {
        searchInput.value = '';
        searchClear.style.display = 'none';
        filterTable('');
        searchInput.focus();
    }

    function filterTable(q) {
        var rows     = document.querySelectorAll('#filesTable tbody .file-row');
        var noResult = document.getElementById('noSearchResult');
        var visible  = 0;

        rows.forEach(function (row) {
            var text = row.innerText.toLowerCase();
            if (!q || text.includes(q)) {
                row.style.display = '';
                visible++;
            } else {
                row.style.display = 'none';
            }
        });

        noResult.style.display = (rows.length > 0 && visible === 0) ? '' : 'none';
    }

    /* ── Preview ─────────────────────────────────────────── */
    function previewFile(url, ext) {
        document.getElementById('filePreviewFrame').src = url;
        document.getElementById('filePreviewPanel').style.display = 'block';
    }

    function closePreview() {
        document.getElementById('filePreviewPanel').style.display = 'none';
        document.getElementById('filePreviewFrame').src = '';
    }

    /* ── Actions ─────────────────────────────────────────── */
    function editFile(id) {
        Swal.fire({ title: 'Edit File', text: 'Edit functionality coming soon', icon: 'info' });
    }

    function moveFile(id) {
        Swal.fire({ title: 'Move File', text: 'Move functionality coming soon', icon: 'info' });
    }

    function deleteFile(id) {
        Swal.fire({
            title: 'Delete File?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel'
        }).then(function (result) {
            if (result.isConfirmed) {
                fetch('/filehosting/files/' + id, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                }).then(function (res) {
                    return res.json();
                }).then(function () {
                    location.reload();
                });
            }
        });
    }

    /* ── PDF hover popover ───────────────────────────────── */
    $(document).ready(function () {
        $('.pdf-preview').popover({
            placement: 'right',
            trigger: 'hover',
            html: true,
            content: function () {
                return '<iframe src="' + $(this).data('pdf') + '" width="400" height="500" style="border:none;"></iframe>';
            }
        });

        $('[data-toggle="popover"]').not('.pdf-preview').popover({
            trigger: 'hover',
            html: true,
            placement: 'right'
        });
    });
</script>
@endpush