{{-- ═══════════════════════════════════════════════════════════════════════════
     COMPONENT 1: Online Status Badge
     Usage: <x-online-status :user="$user" />
     ═════════════════════════════════════════════════════════════════════════ --}}

{{-- File: resources/views/components/online-status.blade.php --}}

@props(['user'])

<div class="online-status-badge" data-online-status data-user-id="{{ $user->id }}">
    <i class="fas {{ $user->getPresenceIcon() }}"></i>
    <span class="ml-1">{{ ucfirst($user->presence_status) }}</span>
</div>

<style>
    .online-status-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .badge-success {
        background-color: #d4edda;
        color: #155724;
    }
    
    .badge-warning {
        background-color: #fff3cd;
        color: #856404;
    }
    
    .badge-danger {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    .badge-secondary {
        background-color: #e2e3e5;
        color: #383d41;
    }
</style>
