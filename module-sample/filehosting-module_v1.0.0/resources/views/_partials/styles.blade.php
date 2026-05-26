{{-- modules/FileHosting/resources/views/_partials/styles.blade.php --}}
<style>
/* Container */
.fh-container {
    max-width: 100%;
    margin: 0 auto;
    padding: 1.5rem;
}

/* Header */
.fh-header {
    margin-bottom: 1.5rem;
}
.fh-header__left {
    display: flex;
    align-items: center;
    gap: 1rem;
}
.fh-back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    color: #6b7280;
    text-decoration: none;
    font-size: 0.875rem;
}
.fh-back-link:hover { color: #374151; }
.fh-back-link svg { width: 1rem; height: 1rem; }
.fh-page-title {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1.5rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
}
.fh-page-title svg { width: 1.5rem; height: 1.5rem; }

/* Stats */
.fh-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.fh-stat {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 1rem;
    text-align: center;
}
.fh-stat__value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
}
.fh-stat__label {
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

/* Tabs */
.fh-tabs {
    display: flex;
    gap: 0.25rem;
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 1.5rem;
}
.fh-tab {
    padding: 0.75rem 1rem;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 0.875rem;
    color: #6b7280;
    border-bottom: 2px solid transparent;
    margin-bottom: -1px;
    transition: color 0.15s, border-color 0.15s;
}
.fh-tab:hover { color: #374151; }
.fh-tab--active {
    color: #2563eb;
    border-bottom-color: #2563eb;
    font-weight: 500;
}

/* Settings Grid */
.fh-settings-grid {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.fh-setting-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
}
.fh-setting-row__label {
    font-size: 0.875rem;
    color: #374151;
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}
.fh-setting-row__hint {
    font-size: 0.75rem;
    color: #9ca3af;
}
.fh-setting-row__input {
    width: 200px;
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
}
.fh-setting-row__input:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
}

/* Toggle */
.fh-toggle {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
}
.fh-toggle__track {
    display: block;
    width: 44px;
    height: 24px;
    background: #d1d5db;
    border-radius: 12px;
    position: relative;
    transition: background 0.2s;
}
.fh-toggle--active .fh-toggle__track,
.fh-toggle__track:has(.fh-toggle__thumb--on) {
    background: #2563eb;
}
.fh-toggle__thumb {
    position: absolute;
    top: 2px;
    left: 2px;
    width: 20px;
    height: 20px;
    background: #fff;
    border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    transition: transform 0.2s;
}
.fh-toggle__thumb--on {
    transform: translateX(20px);
}

/* Roles */
.fh-role-block {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1rem;
}
.fh-role-block__title {
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.75rem;
}
.fh-role-block__perms {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.fh-role-perm-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.fh-role-perm-type {
    font-size: 0.75rem;
    color: #6b7280;
    min-width: 60px;
}
.fh-badge {
    display: inline-block;
    padding: 0.125rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}
.fh-badge--blue {
    background: #dbeafe;
    color: #1e40af;
}
.fh-badge--gray {
    background: #f3f4f6;
    color: #6b7280;
}

/* Toast */
.fh-toast {
    position: fixed;
    bottom: 1.5rem;
    right: 1.5rem;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 1000;
}
.fh-toast--success {
    background: #d1fae5;
    color: #065f46;
}
.fh-toast--error {
    background: #fee2e2;
    color: #991b1b;
}

/* Button */
.fh-btn {
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.15s, border-color 0.15s;
}
.fh-btn--ghost {
    background: none;
    border: 1px solid #d1d5db;
    color: #374151;
}
.fh-btn--ghost:hover {
    background: #f3f4f6;
}

/* Muted text */
.fh-muted {
    font-size: 0.875rem;
    color: #6b7280;
}
</style>
