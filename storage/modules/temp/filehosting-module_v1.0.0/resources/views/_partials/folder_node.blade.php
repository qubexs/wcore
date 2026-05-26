{{-- _partials/folder_node.blade.php --}}
{{-- $f = Folder, $depth = int --}}
<a href="{{ route('filehosting.folders.show', $f->id) }}"
   class="fh-folder-node {{ isset($folder) && $folder->id === $f->id ? 'fh-folder-node--active' : '' }}"
   style="padding-left: {{ 0.5 + $depth * 0.75 }}rem">
    <svg viewBox="0 0 24 24" fill="currentColor">
        <path d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
    </svg>
    {{ $f->name }}
</a>
@if($f->children && $f->children->count())
<div class="fh-folder-children">
    @foreach($f->children->sortBy('name') as $child)
        @include('filehosting::_partials.folder_node', ['f' => $child, 'depth' => $depth + 1])
    @endforeach
</div>
@endif
