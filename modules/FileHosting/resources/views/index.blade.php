@extends('layouts.admin')


{{-- modules\filehosting\resources\views\index.blade.php --}}
@section('title', isset($folder) ? $folder->name : 'File Hosting')

@section('main-content')
<div class="fh-container" x-data="fileHosting()" x-init="init()" style="padding-top: 3.5rem;">

    {{-- ================================================================
         Header Bar
    ================================================================ --}}
    <div class="fh-header">
        <div class="fh-header__left">
            @if(isset($breadcrumb) && $breadcrumb->count() > 1)
                <nav class="fh-breadcrumb" aria-label="breadcrumb">
                    <a href="{{ route('filehosting.index') }}" class="fh-breadcrumb__home">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7A1 1 0 003 11h1v6a1 1 0 001 1h4v-4h2v4h4a1 1 0 001-1v-6h1a1 1 0 00.707-1.707l-7-7z"/></svg>
                    </a>
                    @foreach($breadcrumb as $crumb)
                        <span class="fh-breadcrumb__sep">/</span>
                        @if($loop->last)
                            <span class="fh-breadcrumb__current">{{ $crumb->name }}</span>
                        @else
                            <a href="{{ route('filehosting.folders.show', $crumb->id) }}" class="fh-breadcrumb__link">{{ $crumb->name }}</a>
                        @endif
                    @endforeach
                </nav>
            @else
                <h1 class="fh-page-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
                    File Hosting
                </h1>
            @endif
        </div>

        <div class="fh-header__actions">
            <div class="fh-search-wrap">
                <input type="search" placeholder="Search files…" x-model="searchQuery"
                       @input.debounce.400ms="doSearch()" class="fh-search-input">
                <svg class="fh-search-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/></svg>
            </div>

            @can('filehosting.upload')
            <a href="{{ route('filehosting.files.upload') }}" class="fh-btn fh-btn--primary">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                Upload
            </a>
            @endcan

            @can('filehosting.create')
            <button @click="showNewFolder = true" class="fh-btn fh-btn--secondary">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                New Folder
            </button>
            @endcan
        </div>
    </div>

    {{-- ================================================================
         Stats Bar (dashboard only)
    ================================================================ --}}
    @if(isset($stats))
    <div class="fh-stats">
        <div class="fh-stat">
            <div class="fh-stat__value">{{ number_format($stats['total_files']) }}</div>
            <div class="fh-stat__label">Files</div>
        </div>
        <div class="fh-stat">
            <div class="fh-stat__value">{{ number_format($stats['total_folders']) }}</div>
            <div class="fh-stat__label">Folders</div>
        </div>
        <div class="fh-stat">
            <div class="fh-stat__value">{{ round($stats['total_size'] / 1048576, 1) }} MB</div>
            <div class="fh-stat__label">Total Size</div>
        </div>
        <div class="fh-stat">
            <div class="fh-stat__value">{{ number_format($stats['total_downloads']) }}</div>
            <div class="fh-stat__label">Downloads</div>
        </div>
    </div>
    @endif

    <div class="fh-body">

        {{-- ============================================================
             Sidebar: Folder Tree
        ============================================================ --}}
        <aside class="fh-sidebar">
            <div class="fh-sidebar__title">Folders</div>
            @foreach($rootFolders ?? [] as $rootFolder)
                @include('filehosting::_partials.folder_node', ['f' => $rootFolder, 'depth' => 0])
            @endforeach
        </aside>

        {{-- ============================================================
             Main Content
        ============================================================ --}}
        <main class="fh-main">

            {{-- Search Results --}}
            <template x-if="searchResults !== null">
                <div>
                    <div class="fh-section-title">Search Results</div>
                    <div class="fh-file-grid" x-show="searchResults.length > 0">
                        <template x-for="file in searchResults" :key="file.id">
                            @include('filehosting::_partials.file_card_alpine')
                        </template>
                    </div>
                    <p x-show="searchResults.length === 0" class="fh-empty">No files found.</p>
                </div>
            </template>

            {{-- Normal Browse --}}
            <template x-if="searchResults === null">
                <div>
                    {{-- Sub-folders --}}
                    @if(isset($folder) && $folder->children->count())
                    <div class="fh-section-title">Folders</div>
                    <div class="fh-folder-grid">
                        @foreach($folder->children->sortBy('name') as $child)
                        <a href="{{ route('filehosting.folders.show', $child->id) }}" class="fh-folder-card">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
                            <span>{{ $child->name }}</span>
                            <small>{{ $child->files_count ?? 0 }} files</small>
                        </a>
                        @endforeach
                    </div>
                    @endif

                    @if(!isset($folder) && isset($rootFolders) && $rootFolders->count())
                    <div class="fh-section-title">Folders</div>
                    <div class="fh-folder-grid">
                        @foreach($rootFolders as $rf)
                        <a href="{{ route('filehosting.folders.show', $rf->id) }}" class="fh-folder-card">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
                            <span>{{ $rf->name }}</span>
                            <small>{{ $rf->files_count ?? 0 }} files</small>
                        </a>
                        @endforeach
                    </div>
                    @endif

                    {{-- Files --}}
                    @if(isset($files) && $files->count())
                    <div class="fh-section-title">Files</div>
                    <div class="fh-file-grid">
                        @foreach($files as $file)
                        @include('filehosting::_partials.file_card', ['file' => $file])
                        @endforeach
                    </div>
                    {{ $files->links() }}
                    @elseif(isset($recentFiles) && $recentFiles->count())
                    <div class="fh-section-title">Recent Files</div>
                    <div class="fh-file-grid">
                        @foreach($recentFiles as $file)
                        @include('filehosting::_partials.file_card', ['file' => $file])
                        @endforeach
                    </div>
                    @else
                    <div class="fh-empty">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                        <p>No files here yet. Upload your first file!</p>
                    </div>
                    @endif
                </div>
            </template>

        </main>
    </div>

    {{-- ================================================================
         New Folder Modal
    ================================================================ --}}
    <div class="fh-modal-backdrop" x-show="showNewFolder" x-cloak @click.self="showNewFolder = false">
        <div class="fh-modal">
            <div class="fh-modal__header">
                <h3>New Folder</h3>
                <button @click="showNewFolder = false" class="fh-modal__close">&times;</button>
            </div>
            <div class="fh-modal__body">
                <label class="fh-label">Folder Name</label>
                <input type="text" x-model="newFolderName" @keyup.enter="createFolder()"
                       class="fh-input" placeholder="e.g. HR Documents" autofocus>
                <label class="fh-label">Visibility</label>
                <select x-model="newFolderVisibility" class="fh-input">
                    <option value="private">Private</option>
                    <option value="public">Public</option>
                    <option value="restricted">Restricted</option>
                </select>
                <p x-show="folderError" x-text="folderError" class="fh-error"></p>
            </div>
            <div class="fh-modal__footer">
                <button @click="showNewFolder = false" class="fh-btn fh-btn--ghost">Cancel</button>
                <button @click="createFolder()" :disabled="!newFolderName" class="fh-btn fh-btn--primary">Create</button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
@include('filehosting::_partials.styles')
@endpush

@push('scripts')
<script>
function fileHosting() {
    return {
        showNewFolder: false,
        newFolderName: '',
        newFolderVisibility: 'private',
        folderError: '',
        searchQuery: '',
        searchResults: null,

        init() {},

        async createFolder() {
            this.folderError = '';
            try {
                const res = await fetch('{{ route('filehosting.folders.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        name: this.newFolderName,
                        visibility: this.newFolderVisibility,
                        parent_id: {{ isset($folder) ? $folder->id : 'null' }},
                    }),
                });
                const json = await res.json();
                if (!res.ok) {
                    this.folderError = json.message || 'Failed to create folder.';
                    return;
                }
                window.location.reload();
            } catch (e) {
                this.folderError = 'Network error. Please try again.';
            }
        },

        async doSearch() {
            if (!this.searchQuery.trim()) { this.searchResults = null; return; }
            const params = new URLSearchParams({ q: this.searchQuery });
            const res = await fetch('{{ route('filehosting.files.search') }}?' + params, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
            });
            this.searchResults = await res.json();
        },
    };
}
</script>
@endpush