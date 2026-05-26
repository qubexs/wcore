<style>
/* =====================================================================
   FileHosting Module — Shared Styles
   ===================================================================== */

:root {
    --fh-primary:   #4f46e5;
    --fh-primary-h: #4338ca;
    --fh-success:   #16a34a;
    --fh-danger:    #dc2626;
    --fh-gray-50:   #f9fafb;
    --fh-gray-100:  #f3f4f6;
    --fh-gray-200:  #e5e7eb;
    --fh-gray-400:  #9ca3af;
    --fh-gray-600:  #4b5563;
    --fh-gray-800:  #1f2937;
    --fh-radius:    0.5rem;
    --fh-shadow:    0 1px 3px rgba(0,0,0,.1), 0 1px 2px rgba(0,0,0,.06);
}

.fh-container { max-width: 1280px; margin: 0 auto; padding: 1.5rem; font-family: system-ui, -apple-system, sans-serif; }

/* Header */
.fh-header { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:1.5rem; flex-wrap:wrap; }
.fh-header__left { display:flex; align-items:center; gap:1rem; }
.fh-header__actions { display:flex; align-items:center; gap:.5rem; }
.fh-page-title { margin:0; font-size:1.375rem; font-weight:700; color:var(--fh-gray-800); display:flex; align-items:center; gap:.5rem; }
.fh-page-title svg { width:1.4rem; height:1.4rem; color:var(--fh-primary); }
.fh-back-link { color:var(--fh-primary); display:flex; align-items:center; gap:.25rem; font-size:.9rem; text-decoration:none; }
.fh-back-link svg { width:1rem; height:1rem; }

/* Buttons */
.fh-btn { display:inline-flex; align-items:center; gap:.375rem; padding:.45rem 1rem; border-radius:var(--fh-radius); font-size:.875rem; font-weight:500; cursor:pointer; border:none; text-decoration:none; transition:.15s; }
.fh-btn--primary { background:var(--fh-primary); color:#fff; }
.fh-btn--primary:hover { background:var(--fh-primary-h); }
.fh-btn--secondary { background:var(--fh-gray-100); color:var(--fh-gray-800); border:1px solid var(--fh-gray-200); }
.fh-btn--secondary:hover { background:var(--fh-gray-200); }
.fh-btn--ghost { background:transparent; color:var(--fh-gray-600); border:1px solid var(--fh-gray-200); }
.fh-btn--ghost:hover { background:var(--fh-gray-50); }
.fh-btn--sm { padding:.3rem .7rem; font-size:.8rem; }
.fh-btn svg { width:1rem; height:1rem; }
.fh-btn:disabled { opacity:.5; cursor:not-allowed; }

/* Stats */
.fh-stats { display:grid; grid-template-columns:repeat(auto-fill, minmax(160px,1fr)); gap:1rem; margin-bottom:1.5rem; }
.fh-stat { background:#fff; border:1px solid var(--fh-gray-200); border-radius:var(--fh-radius); padding:1rem 1.25rem; box-shadow:var(--fh-shadow); }
.fh-stat__value { font-size:1.5rem; font-weight:700; color:var(--fh-primary); }
.fh-stat__label { font-size:.8rem; color:var(--fh-gray-600); margin-top:.2rem; }

/* Layout */
.fh-body { display:grid; grid-template-columns:220px 1fr; gap:1.5rem; }
@media (max-width:768px) { .fh-body { grid-template-columns:1fr; } }

/* Sidebar */
.fh-sidebar { background:#fff; border:1px solid var(--fh-gray-200); border-radius:var(--fh-radius); padding:1rem; height:fit-content; }
.fh-sidebar__title { font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:var(--fh-gray-400); margin-bottom:.75rem; }
.fh-folder-node { display:flex; align-items:center; gap:.375rem; padding:.35rem .5rem; border-radius:.375rem; font-size:.875rem; color:var(--fh-gray-700); text-decoration:none; cursor:pointer; }
.fh-folder-node:hover { background:var(--fh-gray-100); }
.fh-folder-node svg { width:1rem; height:1rem; color:#f59e0b; flex-shrink:0; }
.fh-folder-node--active { background:var(--fh-gray-100); font-weight:600; }
.fh-folder-children { padding-left:1.25rem; }

/* Section title */
.fh-section-title { font-size:.85rem; font-weight:600; color:var(--fh-gray-600); margin:1rem 0 .5rem; display:flex; align-items:center; gap:.5rem; }

/* Folder grid */
.fh-folder-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(160px,1fr)); gap:.75rem; margin-bottom:1rem; }
.fh-folder-card { background:#fff; border:1px solid var(--fh-gray-200); border-radius:var(--fh-radius); padding:1rem; display:flex; flex-direction:column; align-items:flex-start; gap:.35rem; text-decoration:none; color:var(--fh-gray-800); transition:.15s; }
.fh-folder-card:hover { border-color:var(--fh-primary); box-shadow:0 0 0 2px rgba(79,70,229,.12); }
.fh-folder-card svg { width:2rem; height:2rem; color:#f59e0b; }
.fh-folder-card span { font-size:.875rem; font-weight:500; word-break:break-word; }
.fh-folder-card small { font-size:.75rem; color:var(--fh-gray-400); }

/* File grid */
.fh-file-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(200px,1fr)); gap:.75rem; margin-bottom:1rem; }
.fh-file-card { background:#fff; border:1px solid var(--fh-gray-200); border-radius:var(--fh-radius); overflow:hidden; display:flex; flex-direction:column; transition:.15s; }
.fh-file-card:hover { box-shadow:var(--fh-shadow); }
.fh-file-card__thumb { aspect-ratio:16/10; background:var(--fh-gray-100); display:flex; align-items:center; justify-content:center; overflow:hidden; }
.fh-file-card__thumb img { width:100%; height:100%; object-fit:cover; }
.fh-file-card__thumb svg { width:2.5rem; height:2.5rem; color:var(--fh-gray-400); }
.fh-file-card__body { padding:.75rem; flex:1; }
.fh-file-card__name { font-size:.875rem; font-weight:500; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.fh-file-card__meta { font-size:.75rem; color:var(--fh-gray-400); margin-top:.25rem; }
.fh-file-card__actions { display:flex; gap:.25rem; padding:.5rem .75rem; border-top:1px solid var(--fh-gray-100); }
.fh-file-card__actions a, .fh-file-card__actions button { font-size:.75rem; color:var(--fh-primary); text-decoration:none; background:none; border:none; cursor:pointer; padding:.15rem .4rem; border-radius:.25rem; }
.fh-file-card__actions a:hover, .fh-file-card__actions button:hover { background:var(--fh-gray-100); }

/* Breadcrumb */
.fh-breadcrumb { display:flex; align-items:center; gap:.35rem; font-size:.875rem; }
.fh-breadcrumb__home svg { width:1.1rem; height:1.1rem; }
.fh-breadcrumb__home, .fh-breadcrumb__link { color:var(--fh-primary); text-decoration:none; }
.fh-breadcrumb__sep { color:var(--fh-gray-400); }
.fh-breadcrumb__current { color:var(--fh-gray-600); font-weight:500; }

/* Search */
.fh-search-wrap { position:relative; }
.fh-search-input { padding:.45rem .75rem .45rem 2.25rem; border:1px solid var(--fh-gray-200); border-radius:var(--fh-radius); font-size:.875rem; width:220px; }
.fh-search-icon { position:absolute; left:.6rem; top:50%; transform:translateY(-50%); width:1rem; height:1rem; color:var(--fh-gray-400); pointer-events:none; }

/* Modal */
.fh-modal-backdrop { position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:50; display:flex; align-items:center; justify-content:center; }
.fh-modal { background:#fff; border-radius:.75rem; width:100%; max-width:440px; box-shadow:0 20px 60px rgba(0,0,0,.2); }
.fh-modal__header { padding:1.25rem 1.5rem .75rem; display:flex; align-items:center; justify-content:space-between; }
.fh-modal__header h3 { margin:0; font-size:1.1rem; }
.fh-modal__close { background:none; border:none; font-size:1.5rem; cursor:pointer; color:var(--fh-gray-400); line-height:1; }
.fh-modal__body { padding:.75rem 1.5rem; }
.fh-modal__footer { padding:.75rem 1.5rem 1.25rem; display:flex; justify-content:flex-end; gap:.5rem; }

/* Forms */
.fh-label { display:block; font-size:.8rem; font-weight:500; color:var(--fh-gray-600); margin-bottom:.35rem; margin-top:.75rem; }
.fh-input { width:100%; padding:.5rem .75rem; border:1px solid var(--fh-gray-200); border-radius:var(--fh-radius); font-size:.875rem; box-sizing:border-box; }
.fh-input:focus { outline:none; border-color:var(--fh-primary); box-shadow:0 0 0 3px rgba(79,70,229,.12); }
.fh-form-group { margin-bottom:1rem; }
.fh-error { color:var(--fh-danger); font-size:.8rem; margin-top:.35rem; }

/* Upload */
.fh-upload-layout { display:grid; grid-template-columns:1fr 340px; gap:1.5rem; }
@media (max-width:768px) { .fh-upload-layout { grid-template-columns:1fr; } }
.fh-dropzone { background:#fff; border:2px dashed var(--fh-gray-200); border-radius:var(--fh-radius); padding:3rem 2rem; text-align:center; transition:.2s; }
.fh-dropzone--active { border-color:var(--fh-primary); background:rgba(79,70,229,.04); }
.fh-dropzone__input { display:none; }
.fh-dropzone__icon svg { width:3rem; height:3rem; color:var(--fh-gray-400); margin-bottom:1rem; }
.fh-dropzone__text { color:var(--fh-gray-600); margin-bottom:.75rem; }
.fh-dropzone__hint { font-size:.75rem; color:var(--fh-gray-400); margin-top:.75rem; }
.fh-upload-options { background:#fff; border:1px solid var(--fh-gray-200); border-radius:var(--fh-radius); padding:1.25rem; }
.fh-queue { background:#fff; border:1px solid var(--fh-gray-200); border-radius:var(--fh-radius); margin-top:1.5rem; overflow:hidden; }
.fh-queue__list { list-style:none; margin:0; padding:0; }
.fh-queue__item { display:flex; align-items:center; gap:.75rem; padding:.75rem 1.25rem; border-bottom:1px solid var(--fh-gray-100); }
.fh-queue__item:last-child { border-bottom:0; }
.fh-queue__info { flex:1; min-width:0; }
.fh-queue__name { font-size:.875rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; display:block; }
.fh-queue__size { font-size:.75rem; color:var(--fh-gray-400); }
.fh-queue__status { width:140px; }
.fh-queue__remove { background:none; border:none; font-size:1.25rem; color:var(--fh-gray-400); cursor:pointer; }
.fh-upload-done { background:#f0fdf4; border:1px solid #bbf7d0; border-radius:var(--fh-radius); padding:1.25rem; margin-top:1.5rem; display:flex; align-items:center; justify-content:space-between; }
.fh-progress { background:var(--fh-gray-100); border-radius:9999px; height:6px; overflow:hidden; }
.fh-progress__bar { height:100%; background:var(--fh-primary); transition:width .2s; }

/* Badges */
.fh-badge { display:inline-flex; align-items:center; padding:.15rem .55rem; border-radius:9999px; font-size:.7rem; font-weight:500; }
.fh-badge--gray  { background:var(--fh-gray-100); color:var(--fh-gray-600); }
.fh-badge--green { background:#dcfce7; color:#16a34a; }
.fh-badge--red   { background:#fee2e2; color:#dc2626; }
.fh-badge--blue  { background:#dbeafe; color:#1d4ed8; }

/* Settings */
.fh-tabs { display:flex; gap:.25rem; border-bottom:2px solid var(--fh-gray-200); margin-bottom:1.5rem; }
.fh-tab { background:none; border:none; padding:.6rem 1rem; font-size:.875rem; color:var(--fh-gray-600); cursor:pointer; border-bottom:2px solid transparent; margin-bottom:-2px; }
.fh-tab--active { color:var(--fh-primary); border-bottom-color:var(--fh-primary); font-weight:600; }
.fh-tab-content { min-height:200px; }
.fh-settings-grid { display:grid; gap:.75rem; }
.fh-setting-row { background:#fff; border:1px solid var(--fh-gray-200); border-radius:var(--fh-radius); padding:1rem 1.25rem; display:flex; align-items:center; gap:1rem; }
.fh-setting-row__label { flex:1; }
.fh-setting-row__label strong { display:block; font-size:.875rem; }
.fh-setting-row__label small { font-size:.75rem; color:var(--fh-gray-400); }
.fh-setting-row__control { width:200px; }
.fh-role-block { background:#fff; border:1px solid var(--fh-gray-200); border-radius:var(--fh-radius); padding:1rem 1.25rem; margin-bottom:.75rem; }
.fh-role-block__title { font-weight:700; font-size:.95rem; margin-bottom:.5rem; }
.fh-role-perm-group { display:flex; align-items:center; gap:.35rem; flex-wrap:wrap; margin-bottom:.35rem; }
.fh-role-perm-type { font-size:.75rem; font-weight:600; text-transform:uppercase; color:var(--fh-gray-400); min-width:50px; }

/* Toast */
.fh-toast { position:fixed; bottom:1.5rem; right:1.5rem; padding:.75rem 1.25rem; border-radius:var(--fh-radius); font-size:.875rem; z-index:100; box-shadow:0 4px 16px rgba(0,0,0,.15); }
.fh-toast--success { background:#16a34a; color:#fff; }
.fh-toast--error   { background:#dc2626; color:#fff; }

/* Empty state */
.fh-empty { display:flex; flex-direction:column; align-items:center; padding:3rem; color:var(--fh-gray-400); }
.fh-empty svg { width:3rem; height:3rem; margin-bottom:1rem; }
.fh-muted { color:var(--fh-gray-400); font-size:.85rem; }

[x-cloak] { display: none !important; }




/* ============================================================
   File Hosting Styles – Modern & Animated
============================================================ */

.fh-container {
    background: #f9fafb;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 6px 18px rgba(0,0,0,0.06);
    transition: all 0.3s ease;
}

.fh-header {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
}

.fh-back-link {
    display: flex;
    align-items: center;
    font-weight: 500;
    color: #4a5568;
    text-decoration: none;
    margin-right: 1rem;
    transition: color 0.2s ease;
}
.fh-back-link:hover { color: #3182ce; }
.fh-back-link svg { width: 1rem; height: 1rem; margin-right: 0.25rem; }

.fh-page-title {
    font-size: 1.75rem;
    font-weight: 600;
    color: #1a202c;
}

.fh-dropzone {
    border: 2px dashed #cbd5e0;
    border-radius: 12px;
    background: #ffffff;
    padding: 2.5rem;
    text-align: center;
    transition: border-color 0.3s, background 0.3s, transform 0.2s;
    cursor: pointer;
}
.fh-dropzone:hover { transform: translateY(-2px); }
.fh-dropzone--active {
    border-color: #3182ce;
    background: #ebf8ff;
}
.fh-dropzone__icon svg {
    width: 3rem;
    height: 3rem;
    color: #3182ce;
    margin-bottom: 0.5rem;
    transition: transform 0.3s;
}
.fh-dropzone--active .fh-dropzone__icon svg { transform: scale(1.1); }
.fh-dropzone__text {
    font-weight: 500;
    color: #4a5568;
    margin-bottom: 0.5rem;
}
.fh-dropzone__hint {
    font-size: 0.875rem;
    color: #718096;
}

.fh-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: all 0.2s ease;
}
.fh-btn--primary {
    background: #3182ce;
    color: #fff;
}
.fh-btn--primary:hover {
    background: #2b6cb0;
    transform: translateY(-1px);
}
.fh-btn--sm { padding: 0.25rem 0.75rem; font-size: 0.875rem; }

.fh-upload-options {
    margin-top: 2rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.fh-input, .fh-label {
    width: 100%;
    display: block;
}
.fh-input {
    padding: 0.5rem;
    border: 1px solid #cbd5e0;
    border-radius: 6px;
    font-size: 0.875rem;
    color: #1a202c;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.fh-input:focus {
    border-color: #3182ce;
    box-shadow: 0 0 0 2px rgba(49,130,206,0.2);
    outline: none;
}

.fh-queue {
    margin-top: 2rem;
}
.fh-queue__list {
    list-style: none;
    padding: 0;
}
.fh-queue__item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    margin-bottom: 0.5rem;
    background: #fff;
    transition: transform 0.2s, box-shadow 0.2s;
}
.fh-queue__item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.fh-queue__info {
    display: flex;
    flex-direction: column;
}
.fh-queue__name {
    font-weight: 500;
    color: #2d3748;
}
.fh-queue__size {
    font-size: 0.75rem;
    color: #718096;
}

.fh-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
    text-align: center;
    transition: all 0.2s;
}
.fh-badge--gray { background: #edf2f7; color: #4a5568; }
.fh-badge--green { background: #9ae6b4; color: #22543d; }
.fh-badge--red { background: #feb2b2; color: #742a2a; }

.fh-progress {
    width: 140px;
    height: 8px;
    background: #edf2f7;
    border-radius: 4px;
    overflow: hidden;
    margin-top: 0.25rem;
}
.fh-progress__bar {
    height: 100%;
    background: #3182ce;
    width: 0;
    transition: width 0.4s ease;
}

.fh-queue__remove {
    background: transparent;
    border: none;
    font-size: 1.25rem;
    color: #e53e3e;
    cursor: pointer;
    transition: color 0.2s;
}
.fh-queue__remove:hover { color: #c53030; }

.fh-upload-done {
    margin-top: 1.5rem;
    padding: 1rem 1.5rem;
    background: #f0fff4;
    border: 1px solid #c6f6d5;
    border-radius: 10px;
    color: #22543d;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: transform 0.2s;
}
.fh-upload-done:hover { transform: translateY(-1px); }

</style>
