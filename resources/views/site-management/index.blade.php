@extends('layouts.admin')

@section('title', 'Site Management')

@section('main-content')
<div class="fh-container" style="padding-top: 3.5rem;">

    <div class="fh-header">
        <div class="fh-header__left">
            <h1 class="fh-page-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                Site Management
            </h1>
        </div>
    </div>

    <div class="fh-tabs" id="siteManagementTabs">
        <button type="button" class="fh-tab fh-tab--active" data-tab="general" onclick="switchSiteTab('general')">
            <svg viewBox="0 0 20 20" fill="currentColor" style="width: 1rem; height: 1rem; margin-right: 0.25rem;"><path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/></svg>
            General
        </button>
        <button type="button" class="fh-tab" data-tab="dashboard" onclick="switchSiteTab('dashboard')">
            <svg viewBox="0 0 20 20" fill="currentColor" style="width: 1rem; height: 1rem; margin-right: 0.25rem;"><path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"/><path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"/></svg>
            Dashboard
        </button>
        <button type="button" class="fh-tab" data-tab="db" onclick="switchSiteTab('db')">
            <svg viewBox="0 0 20 20" fill="currentColor" style="width: 1rem; height: 1rem; margin-right: 0.25rem;"><path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm14 1a1 1 0 11-2 0 1 1 0 012 0zM2 13a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2v-2zm14 1a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd"/></svg>
            DB Backup
        </button>
        <button type="button" class="fh-tab" data-tab="site" onclick="switchSiteTab('site')">
            <svg viewBox="0 0 20 20" fill="currentColor" style="width: 1rem; height: 1rem; margin-right: 0.25rem;"><path fill-rule="evenodd" d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z" clip-rule="evenodd"/></svg>
            Site Backup
        </button>
        <button type="button" class="fh-tab" data-tab="update" onclick="switchSiteTab('update')">
            <svg viewBox="0 0 20 20" fill="currentColor" style="width: 1rem; height: 1rem; margin-right: 0.25rem;"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/></svg>
            Update / Rollback
        </button>
    </div>

    <div class="fh-settings-grid" style="margin-top: 1.5rem;">
        <div id="tab-general" class="tab-panel">
            @include('site-management.tabs.general')
        </div>

        <div id="tab-dashboard" class="tab-panel" style="display: none;">
            @include('site-management.tabs.dashboard')
        </div>

        <div id="tab-db" class="tab-panel" style="display: none;">
            @php $backups = $backups ?? []; @endphp
            @include('site-management.tabs.database')
        </div>

        <div id="tab-site" class="tab-panel" style="display: none;">
            @php $websiteBackups = $websiteBackups ?? []; @endphp
            @include('site-management.tabs.backup')
        </div>

        <div id="tab-update" class="tab-panel" style="display: none;">
            @include('site-management.tabs.update')
        </div>
    </div>

</div>

@push('styles')
@include('filehosting::_partials.styles')
@endpush

<style>
.tab-panel {
    animation: fadeIn 0.2s ease;
}
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>

<script>
function switchSiteTab(tabName) {
    var panels = document.querySelectorAll('.tab-panel');
    panels.forEach(function(panel) {
        panel.style.display = 'none';
    });
    
    var selectedPanel = document.getElementById('tab-' + tabName);
    if (selectedPanel) {
        selectedPanel.style.display = 'block';
    }
    
    var tabs = document.querySelectorAll('#siteManagementTabs .fh-tab');
    tabs.forEach(function(tab) {
        if (tab.dataset.tab === tabName) {
            tab.classList.add('fh-tab--active');
        } else {
            tab.classList.remove('fh-tab--active');
        }
    });
    
    localStorage.setItem('siteManagementTab', tabName);
}

document.addEventListener('DOMContentLoaded', function() {
    var savedTab = localStorage.getItem('siteManagementTab') || 'general';
    switchSiteTab(savedTab);
});
</script>

@endsection