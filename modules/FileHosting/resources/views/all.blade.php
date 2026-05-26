@extends('layouts.admin')

@section('title', 'All Files')

@section('main-content')
@include('filehosting::_partials.styles')
<div class="fh-container" style="padding-top: 3.5rem;">

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

    @if(session('success'))
        <div class="fh-toast fh-toast--success">✓ {{ session('success') }}</div>
    @endif

    <div class="d-flex justify-content-center mb-4">
        <div style="width: 100%; max-width: 540px; position: relative;">
            <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none;">
                <svg viewBox="0 0 20 20" fill="currentColor" style="width: 1.1rem; height: 1.1rem;">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                </svg>
            </span>
            <input type="text" id="fileSearch" placeholder="Search files or folders…" autocomplete="off"
                style="width: 100%; padding: 0.6rem 2.5rem 0.6rem 2.5rem; border: 1.5px solid #d1d5db; border-radius: 999px; font-size: 0.95rem; outline: none; box-shadow: 0 1px 6px rgba(0,0,0,0.07); transition: border-color 0.2s, box-shadow 0.2s;"
                onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.15)';"
                onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='0 1px 6px rgba(0,0,0,0.07)';">
            <span id="searchClear" onclick="clearSearch()" title="Clear"
                  style="display:none; position:absolute; right:40px; top:50%; transform:translateY(-50%); cursor:pointer; color:#9ca3af;">
                <svg viewBox="0 0 20 20" fill="currentColor" style="width:1rem;height:1rem;">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            </span>
            <span id="resultCount" style="position:absolute; right:14px; top:50%; transform:translateY(-50%); font-size:0.8rem; color:#6b7280;"></span>
        </div>
    </div>

    <div class="d-flex gap-2" style="align-items: flex-start; width: 100%; flex-wrap: nowrap; overflow-x: auto;">
        <div class="glass-panel" style="flex: 1; min-width: 400px;">
            <table class="fh-table" id="explorerTable">
                <thead>
                    <tr>
                        <th style="width: 40px;"></th>
                        <th>Folder | Filename</th>
                        <th>Uploaded By</th>
                        <th>Size</th>
                        <th>Visibility</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($folders->sortBy('name') as $folder)
                        @include('filehosting::_partials.folder_row', ['folder' => $folder, 'depth' => 0])
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No folders or files yet</p>
                            </td>
                        </tr>
                    @endforelse

                    @php $rootFiles = $files->filter(fn($f) => is_null($f->folder_id)); @endphp
                    @if($rootFiles->count() > 0)
                    <tr class="folder-row" data-folder-id="root" data-depth="0">
                        <td class="expand-icon" style="cursor:pointer;"><i class="fas fa-chevron-right"></i></td>
                        <td data-label="Folder"><i class="fas fa-folder-open text-warning me-2"></i><span class="fw-semibold">Root (No Folder)</span></td>
                        <td data-label="Uploaded By">—</td>
                        <td data-label="Size">{{ $rootFiles->count() }} files</td>
                        <td data-label="Visibility">—</td>
                        <td data-label="Created">—</td>
                        <td data-label="Actions"></td>
                    </tr>
                    @foreach($rootFiles as $file)
                    @php
                        $ext = strtolower(pathinfo($file->name, PATHINFO_EXTENSION));
                        $fileUrl = url('/uploads/' . str_replace('uploads/', '', $file->path));
                        if ($ext == 'pdf') $iconClass = 'fa-file-pdf text-danger';
                        elseif (in_array($ext, ['doc', 'docx'])) $iconClass = 'fa-file-word text-primary';
                        elseif (in_array($ext, ['xls', 'xlsx'])) $iconClass = 'fa-file-excel text-success';
                        elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) $iconClass = 'fa-image text-info';
                        else $iconClass = 'fa-file text-secondary';
                    @endphp
                    <tr class="file-row" data-parent-id="root" data-depth="1">
                        <td></td>
                        <td data-label="File">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fas {{ $iconClass }} me-2"></i><a href="{{ $fileUrl }}" target="_blank">{{ $file->name }}</a></td>
                        <td data-label="Uploaded By">{{ optional($file->owner)->name ?? '—' }}</td>
                        <td data-label="Size">{{ number_format($file->size / 1024, 1) }} KB</td>
                        <td data-label="Visibility"><span class="badge badge-{{ $file->visibility === 'public' ? 'success' : ($file->visibility === 'restricted' ? 'warning' : 'secondary') }}">{{ ucfirst($file->visibility) }}</span></td>
                        <td data-label="Created">{{ $file->created_at->format('Y-m-d') }}</td>
                        <td data-label="Actions">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-primary btn-sm" title="Preview" onclick="previewFile('{{ $fileUrl }}', '{{ $ext }}', {{ $file->id }}, this)"><i class="fas fa-eye"></i></button>
                                <a href="/filehosting/files/{{ $file->id }}/download" class="btn btn-success btn-sm" title="Download"><i class="fas fa-download"></i></a>
                                <button class="btn btn-warning btn-sm" title="Report" onclick="reportFile({{ $file->id }}, '{{ addslashes($file->name) }}')"><i class="fas fa-flag"></i></button>
                                <button class="btn btn-outline-warning btn-sm" title="Favorite" onclick="toggleFavorite({{ $file->id }}, this)"><i class="far fa-star"></i></button>
                                <button class="btn btn-dark btn-sm" title="Share" onclick="shareFile({{ $file->id }}, '{{ addslashes($file->name) }}')"><i class="fas fa-share-alt"></i></button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div id="filePreviewPanel" class="glass-panel d-none" style="width: 500px; min-width: 300px; min-height: 300px; position: fixed; z-index: 9999;">
        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom" style="cursor: move;" onmousedown="startDrag(event)">
            <h6 class="mb-0 fw-bold">Preview</h6>
            <div class="d-flex align-items-center gap-2" id="zoomControls" style="display: none;">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="previewZoom(-10)" title="Zoom Out"><i class="fas fa-minus"></i></button>
                <span id="zoomLevel" class="badge bg-secondary">100%</span>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="previewZoom(10)" title="Zoom In"><i class="fas fa-plus"></i></button>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="closePreview()"><i class="fas fa-times"></i></button>
        </div>
        <div id="previewContainer" style="height: 420px; overflow: auto; display: flex; align-items: center; justify-content: center;" class="scrollbar-thin">
            <iframe id="filePreviewFrame" src="" style="width: 100%; height: 100%; border: none; display: none;"></iframe>
            <img id="filePreviewImage" src="" style="max-width: 100%; max-height: 100%; display: none; transition: transform 0.2s ease;" />
        </div>
        <div style="position: absolute; bottom: 0; right: 0; width: 20px; height: 20px; cursor: se-resize; background: linear-gradient(135deg, transparent 50%, #6c757d 50%);" onmousedown="startResize(event)"></div>
    </div>
</div>
@endsection

@push('styles')
@include('filehosting::_partials.styles')
<style>
    .expand-icon { width: 40px; text-align: center; }
    .hidden-row { display: none !important; }
</style>
@endpush

@push('scripts')
<script>
    function toggleFolder(folderId, expand) {
        var row = document.querySelector('.folder-row[data-folder-id="' + folderId + '"]');
        var icon = row ? row.querySelector('.expand-icon i') : null;
        if (!row || !icon) return;

        if (expand) {
            icon.classList.remove('fa-chevron-right');
            icon.classList.add('fa-chevron-down');
            document.querySelectorAll('#explorerTable tbody tr').forEach(function(tr) {
                var parentId = tr.dataset.parentId;
                if (parentId == folderId) {
                    tr.style.display = '';
                    tr.classList.remove('hidden-row');
                }
            });
        } else {
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-right');
            document.querySelectorAll('#explorerTable tbody tr').forEach(function(tr) {
                var parentId = tr.dataset.parentId;
                if (parentId == folderId) {
                    tr.style.display = 'none';
                    tr.classList.add('hidden-row');
                    var childIcon = tr.querySelector('.expand-icon i');
                    if (childIcon) {
                        childIcon.classList.remove('fa-chevron-down');
                        childIcon.classList.add('fa-chevron-right');
                    }
                }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('#explorerTable tbody tr').forEach(function(tr) {
            var depth = tr.dataset.depth;
            if (depth && parseInt(depth) > 0) {
                tr.style.display = 'none';
                tr.classList.add('hidden-row');
            }
        });

        document.querySelectorAll('#explorerTable tbody .expand-icon').forEach(function(iconCell) {
            var icon = iconCell.querySelector('i');
            if (!icon) return;
            iconCell.style.cursor = 'pointer';
            iconCell.addEventListener('click', function(e) {
                e.stopPropagation();
                var row = this.closest('.folder-row');
                if (!row) return;
                var folderId = row.dataset.folderId;
                var isExpanded = icon.classList.contains('fa-chevron-down');
                toggleFolder(folderId, !isExpanded);
            });
        });
    });

    var searchTimeout;
    var searchInput = document.getElementById('fileSearch');
    var searchClear = document.getElementById('searchClear');
    var resultCount = document.getElementById('resultCount');

    searchInput.addEventListener('input', function () {
        var q = this.value.trim();
        searchClear.style.display = q ? 'inline' : 'none';
        if (q.length === 0) { clearSearch(); return; }
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() { filterTable(q.toLowerCase()); }, 400);
    });

    function clearSearch() {
        searchInput.value = '';
        searchClear.style.display = 'none';
        if (resultCount) resultCount.textContent = '';
        document.querySelectorAll('#explorerTable tbody .file-row').forEach(function(row) {
            row.classList.add('hidden-row');
        });
        document.querySelectorAll('#explorerTable tbody .folder-row').forEach(function(row) {
            row.classList.remove('hidden-row');
            row.style.display = '';
            var icon = row.querySelector('.expand-icon i');
            if (icon) { icon.classList.remove('fa-chevron-down'); icon.classList.add('fa-chevron-right'); }
        });
        document.querySelectorAll('#explorerTable tbody .folder-child').forEach(function(row) {
            row.classList.add('hidden-row');
        });
    }

    function filterTable(q) {
        var fileRows = document.querySelectorAll('#explorerTable tbody .file-row');
        var visibleFolderIds = [];
        var visibleCount = 0;

        fileRows.forEach(function (row) {
            var text = row.innerText.toLowerCase();
            var matches = text.includes(q);
            if (matches) {
                row.classList.remove('hidden-row');
                visibleCount++;
                var parentId = row.dataset.parentId;
                if (parentId && visibleFolderIds.indexOf(parentId) === -1) visibleFolderIds.push(parentId);
            } else { row.classList.add('hidden-row'); }
        });

        document.querySelectorAll('#explorerTable tbody .folder-row').forEach(function (row) {
            var text = row.innerText.toLowerCase();
            var folderId = row.dataset.folderId;
            var hasVisibleFiles = visibleFolderIds.indexOf(folderId) !== -1;
            var showFolder = text.includes(q) || hasVisibleFiles;
            if (showFolder) { row.classList.remove('hidden-row'); row.style.display = ''; }
            else { row.classList.add('hidden-row'); }
            var icon = row.querySelector('.expand-icon i');
            if (icon) {
                if (hasVisibleFiles) { icon.classList.remove('fa-chevron-right'); icon.classList.add('fa-chevron-down'); }
                else { icon.classList.remove('fa-chevron-down'); icon.classList.add('fa-chevron-right'); }
            }
            if (hasVisibleFiles) {
                document.querySelectorAll('.file-row[data-parent-id="' + folderId + '"]').forEach(function(fileRow) {
                    fileRow.classList.remove('hidden-row');
                });
            }
        });

        if (resultCount) resultCount.textContent = visibleCount > 0 ? visibleCount + ' result(s)' : 'No results';
    }

    function previewFile(url, ext, fileId, btn) {
        ext = ext.toLowerCase();
        var panel = document.getElementById('filePreviewPanel');
        var iframe = document.getElementById('filePreviewFrame');
        var img = document.getElementById('filePreviewImage');
        var zoomControls = document.getElementById('zoomControls');

        if (panel.parentElement !== document.body) document.body.appendChild(panel);

        var viewportWidth = window.innerWidth;
        var viewportHeight = window.innerHeight;
        panel.style.left = ((viewportWidth - 500) / 2) + 'px';
        panel.style.top = ((viewportHeight - 500) / 2) + 'px';

        img.style.transform = 'scale(1)';
        img.dataset.zoom = '100';
        document.getElementById('zoomLevel').textContent = '100%';

        var isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'].includes(ext);
        zoomControls.style.display = isImage ? 'flex' : 'none';

        if (isImage) {
            iframe.style.display = 'none';
            img.style.display = 'block';
            img.src = url;
        } else if (ext === 'pdf') {
            img.style.display = 'none'; iframe.style.display = 'block'; iframe.src = url;
        } else if (ext === 'doc' || ext === 'docx') {
            img.style.display = 'none'; iframe.style.display = 'block'; iframe.src = '/filehosting/preview/word/' + fileId;
        } else if (ext === 'xls' || ext === 'xlsx') {
            img.style.display = 'none'; iframe.style.display = 'block'; iframe.src = '/filehosting/preview/excel/' + fileId;
        } else {
            alert('Preview not available for this file type');
            window.open(url, '_blank');
            return;
        }
        panel.classList.remove('d-none');
    }

    function closePreview() {
        var panel = document.getElementById('filePreviewPanel');
        var iframe = document.getElementById('filePreviewFrame');
        var img = document.getElementById('filePreviewImage');
        panel.classList.add('d-none');
        iframe.src = ''; iframe.style.display = 'none';
        img.src = ''; img.style.display = 'none';
        img.style.transform = 'scale(1)';
        document.getElementById('zoomLevel').textContent = '100%';
        document.getElementById('zoomControls').style.display = 'none';
    }

    function previewZoom(delta) {
        var img = document.getElementById('filePreviewImage');
        var zoom = parseInt(img.dataset.zoom) || 100;
        zoom = Math.max(10, Math.min(300, zoom + delta));
        img.dataset.zoom = zoom;
        document.getElementById('zoomLevel').textContent = zoom + '%';
        img.style.transform = 'scale(' + (zoom / 100) + ')';
    }

    var isDragging = false, isResizing = false, dragOffset = { x: 0, y: 0 };
    var previewPanel = document.getElementById('filePreviewPanel');

    function startDrag(e) {
        if (e.target.tagName === 'BUTTON') return;
        isDragging = true;
        dragOffset.x = e.clientX - previewPanel.offsetLeft;
        dragOffset.y = e.clientY - previewPanel.offsetTop;
        document.addEventListener('mousemove', doDrag);
        document.addEventListener('mouseup', stopDrag);
    }
    function doDrag(e) { if (!isDragging) return; previewPanel.style.left = (e.clientX - dragOffset.x) + 'px'; previewPanel.style.top = (e.clientY - dragOffset.y) + 'px'; }
    function stopDrag() { isDragging = false; document.removeEventListener('mousemove', doDrag); document.removeEventListener('mouseup', stopDrag); }

    function startResize(e) { e.preventDefault(); isResizing = true; document.addEventListener('mousemove', doResize); document.addEventListener('mouseup', stopResize); }
    function doResize(e) { if (!isResizing) return; var rect = previewPanel.getBoundingClientRect(); previewPanel.style.width = Math.max(300, e.clientX - rect.left) + 'px'; previewPanel.style.height = Math.max(300, e.clientY - rect.top) + 'px'; }
    function stopResize() { isResizing = false; document.removeEventListener('mousemove', doResize); document.removeEventListener('mouseup', stopResize); }

    function shareFile(id, filename) {
        Swal.fire({ title: 'Share File', html: '<div style="border: 2px solid #000; border-radius: 8px; padding: 16px; background: #fafafa;"><p class="mb-2"><strong>File:</strong> ' + filename + '</p><div class="input-group mb-3"><input type="text" class="form-control" id="shareUrl" value="/filehosting/files/shared/' + id + '" readonly><button class="btn btn-outline-dark" onclick="copyShareLink()"><i class="fas fa-copy"></i></button></div><button class="btn btn-dark w-100" onclick="createShareLink(' + id + ')">Create Share Link</button></div>', icon: 'info', showConfirmButton: false });
    }
    function copyShareLink() { navigator.clipboard.writeText(document.getElementById('shareUrl').value).then(function() { Swal.fire({ title: 'Copied!', text: 'Link copied to clipboard', icon: 'success', timer: 1500, showConfirmButton: false }); }); }
    function createShareLink(id) { fetch('/filehosting/files/' + id + '/share', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }, body: JSON.stringify({ tag_names: '' }) }).then(function(res) { return res.json(); }).then(function(data) { document.getElementById('shareUrl').value = data.share_url; Swal.fire('Success!', 'Share link created!', 'success'); }).catch(function(err) { Swal.fire('Error', err.message, 'error'); }); }
    function shareFolder(id, foldername) { Swal.fire({ title: 'Share Folder', html: '<div style="border: 2px solid #000; border-radius: 8px; padding: 16px; background: #fafafa;"><p class="mb-2"><strong>Folder:</strong> ' + foldername + '</p><p class="text-muted small">Folder sharing coming soon.</p></div>', icon: 'info', showConfirmButton: false }); }

    function reportFile(id, filename) {
        Swal.fire({ title: 'Report File: ' + filename, input: 'select', inputOptions: { 'broken_404': 'Broken Links (404)', 'forbidden_403': 'Forbidden (403)', 'new_version': 'Has New Version', 'broken_tnc': 'Broken T&C', 'other': 'Other' }, inputPlaceholder: 'Select a reason', showCancelButton: true, confirmButtonText: 'Submit Report', cancelButtonText: 'Cancel' }).then(function(result) {
            if (result.isConfirmed) { fetch('/filehosting/files/' + id + '/report', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }, body: JSON.stringify({ reason: result.value }) }).then(function(res) { return res.json(); }).then(function(data) { Swal.fire('Thank You!', data.message, 'success'); }).catch(function(err) { Swal.fire('Error', err.message, 'error'); }); }
        });
    }

    function toggleFavorite(id, btn) {
        fetch('/filehosting/files/' + id + '/favorite', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' } }).then(function(res) { return res.json(); }).then(function(data) {
            if (data.is_favorite) { btn.classList.remove('btn-outline-warning'); btn.classList.add('btn-warning'); btn.querySelector('i').classList.remove('far'); btn.querySelector('i').classList.add('fas'); }
            else { btn.classList.remove('btn-warning'); btn.classList.add('btn-outline-warning'); btn.querySelector('i').classList.remove('fas'); btn.querySelector('i').classList.add('far'); }
            Swal.fire(data.message, '', 'success');
        }).catch(function(err) { Swal.fire('Error', err.message, 'error'); });
    }

    function toggleFavoriteFolder(id, btn) { Swal.fire({ title: 'Coming Soon', text: 'Folder favorites will be available soon.', icon: 'info' }); }
</script>
@endpush
