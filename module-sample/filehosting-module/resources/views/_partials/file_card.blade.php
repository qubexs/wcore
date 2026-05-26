{{-- _partials/file_card.blade.php --}}
<div class="fh-file-card">
    <div class="fh-file-card__thumb">
        @if($file->isImage() && $file->getThumbnail('medium'))
            <img src="{{ $file->getThumbnail('medium')->getUrl() }}" alt="{{ $file->original_name }}" loading="lazy">
        @else
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        @endif
    </div>
    <div class="fh-file-card__body">
        <div class="fh-file-card__name" title="{{ $file->original_name }}">{{ $file->original_name }}</div>
        <div class="fh-file-card__meta">
            {{ $file->getFormattedSize() }} &middot; {{ strtoupper($file->extension) }}
            @if($file->isExpired()) &middot; <span style="color:#dc2626">Expired</span> @endif
        </div>
    </div>
    <div class="fh-file-card__actions">
        @can('filehosting.download')
        <a href="{{ route('filehosting.files.download', $file->id) }}">Download</a>
        @endcan
        @can('filehosting.edit')
        <a href="{{ route('filehosting.files.show', $file->id) }}">Details</a>
        @endcan
        @can('filehosting.delete')
        <button onclick="if(confirm('Delete this file?')) fetch('{{ route('filehosting.files.destroy', $file->id) }}', {method:'DELETE',headers:{'X-CSRF-TOKEN':document.querySelector('[name=csrf-token]').content}}).then(()=>location.reload())">Delete</button>
        @endcan
    </div>
</div>
