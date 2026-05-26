@php
    // Load all variables that settings.index needs.
    // If the parent controller already passed them, reuse — otherwise fetch here.
    $settings = $settings
        ?? \App\Models\Setting::where('is_active', 1)
                               ->whereNull('deleted_at')
                               ->pluck('value', 'key')
                               ->toArray();

    $stats          = $stats          ?? [];
    $recentChanges  = $recentChanges  ?? collect();
    $backups        = $backups        ?? [];
    $websiteBackups = $websiteBackups ?? [];
@endphp

@include('settings.index')
