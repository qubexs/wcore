<div class="fh-file-card" x-text="file.name">
    <div class="fh-file-card__icon">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z"/></svg>
    </div>
    <div class="fh-file-card__name" x-text="file.name"></div>
    <div class="fh-file-card__size" x-text="(file.size/1024).toFixed(2)+' KB'"></div>
    <div class="fh-file-card__actions">
        <a :href="`/filehosting/${file.id}/download`" title="Download">
            <i class="fas fa-download"></i>
        </a>
    </div>
</div>