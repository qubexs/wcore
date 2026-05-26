{{-- _partials/folder_row.blade.php --}}
{{-- $folder = Folder object, $depth = int --}}
@php
$hasChildren = $folder->children && $folder->children->count() > 0;
$hasFiles = $folder->files && $folder->files->count() > 0;
$canExpand = $hasChildren || $hasFiles;
$indent = $depth * 25;
@endphp

<tr class="folder-row @if($depth > 0) is-child hidden-row @endif" 
    data-folder-id="{{ $folder->id }}" 
    data-parent-id="{{ $folder->parent_id }}"
    data-depth="{{ $depth }}">
    <td class="expand-icon" style="cursor:pointer;">
        @if($canExpand)
            <i class="fas fa-chevron-right"></i>
        @else
            <span style="display:inline-block;width:1em;"></span>
        @endif
    </td>
    <td data-label="Folder">
        <span style="display:inline-block;width:{{ $indent }}px;"></span>
        @if($depth == 0)
            <i class="fas fa-folder text-warning me-2"></i>
        @else
            <i class="fas fa-folder-open text-{{ $depth > 1 ? 'info' : 'warning' }} me-2"></i>
        @endif
        <span class="fw-semibold">{{ $folder->name }}</span>
    </td>
    <td data-label="Uploaded By">{{ optional($folder->owner)->name ?? '—' }}</td>
    <td data-label="Size">{{ $folder->files_count ?? 0 }} files</td>
    <td data-label="Visibility">
        <span class="badge badge-{{ $folder->visibility === 'public' ? 'success' : ($folder->visibility === 'restricted' ? 'warning' : 'secondary') }}">
            {{ ucfirst($folder->visibility ?? 'public') }}
        </span>
    </td>
    <td data-label="Created">{{ $folder->created_at->format('Y-m-d') }}</td>
    <td data-label="Actions">
        <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-warning btn-sm" title="Favorite" onclick="event.stopPropagation(); toggleFavoriteFolder({{ $folder->id }}, this)">
                <i class="far fa-star"></i>
            </button>
            <button class="btn btn-dark btn-sm" title="Share" onclick="event.stopPropagation(); shareFolder({{ $folder->id }}, '{{ addslashes($folder->name) }}')">
                <i class="fas fa-share-alt"></i>
            </button>
        </div>
    </td>
</tr>

{{-- Files under this folder --}}
@if($hasFiles)
@foreach($folder->files as $file)
@php
    $ext = strtolower(pathinfo($file->name, PATHINFO_EXTENSION));
    $fileUrl = url('/uploads/' . str_replace('uploads/', '', $file->path));
    if ($ext == 'pdf') $iconClass = 'fa-file-pdf text-danger';
    elseif (in_array($ext, ['doc', 'docx'])) $iconClass = 'fa-file-word text-primary';
    elseif (in_array($ext, ['xls', 'xlsx'])) $iconClass = 'fa-file-excel text-success';
    elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) $iconClass = 'fa-image text-info';
    else $iconClass = 'fa-file text-secondary';
@endphp
<tr class="file-row hidden-row" data-parent-id="{{ $folder->id }}" data-depth="{{ $depth + 1 }}">
    <td></td>
    <td data-label="File">
        <span style="display:inline-block;width:{{ $indent + 25 }}px;"></span>
        <i class="fas {{ $iconClass }} me-2"></i>
        <a href="{{ $fileUrl }}" target="_blank">{{ $file->name }}</a>
    </td>
    <td data-label="Uploaded By">{{ optional($file->owner)->name ?? '—' }}</td>
    <td data-label="Size">{{ number_format($file->size / 1024, 1) }} KB</td>
    <td data-label="Visibility">
        <span class="badge badge-{{ $file->visibility === 'public' ? 'success' : ($file->visibility === 'restricted' ? 'warning' : 'secondary') }}">
            {{ ucfirst($file->visibility) }}
        </span>
    </td>
    <td data-label="Created">{{ $file->created_at->format('Y-m-d') }}</td>
    <td data-label="Actions">
        <div class="btn-group btn-group-sm">
            <button class="btn btn-primary btn-sm" title="Preview" onclick="previewFile('{{ $fileUrl }}', '{{ $ext }}', {{ $file->id }}, this)">
                <i class="fas fa-eye"></i>
            </button>
            <a href="/filehosting/files/{{ $file->id }}/download" class="btn btn-success btn-sm" title="Download">
                <i class="fas fa-download"></i>
            </a>
            <button class="btn btn-warning btn-sm" title="Report" onclick="reportFile({{ $file->id }}, '{{ addslashes($file->name) }}')">
                <i class="fas fa-flag"></i>
            </button>
            <button class="btn btn-outline-warning btn-sm" title="Favorite" onclick="toggleFavorite({{ $file->id }}, this)">
                <i class="far fa-star"></i>
            </button>
            <button class="btn btn-dark btn-sm" title="Share" onclick="shareFile({{ $file->id }}, '{{ addslashes($file->name) }}')">
                <i class="fas fa-share-alt"></i>
            </button>
        </div>
    </td>
</tr>
@endforeach
@endif

{{-- Recursively render child folders --}}
@if($hasChildren)
@foreach($folder->children->sortBy('name') as $child)
    @include('filehosting::_partials.folder_row', ['folder' => $child, 'depth' => $depth + 1])
@endforeach
@endif
