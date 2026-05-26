{{-- resources/views/users/global-activity.blade.php --}}

@extends('layouts.admin')

@section('main-content')

<div style="padding-top: 5.5rem;">
<div class="container-fluid" style="max-width: 1200px;">

    {{-- ══ Page Header ══════════════════════════════════════════ --}}
    <div class="d-flex align-items-center mb-4" style="gap: 14px;">
        <a href="{{ route('users.index') }}" class="uf-back-btn">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h4 style="font-size: 1.3rem; font-weight: 700; color: #1c1c1e; margin: 0 0 2px;">
                <i class="fas fa-history" style="color: var(--ios-blue); margin-right: 8px;"></i>
                System Activity Log
            </h4>
            <p style="color: var(--ios-gray); font-size: .82rem; margin: 0;">
                All user management activities across the system
            </p>
        </div>
    </div>

    {{-- ══ Filters Card ═════════════════════════════════════════ --}}
    <div class="card mb-4">
        <div class="card-body" style="padding: 1rem 1.5rem;">
            <form method="GET" action="{{ route('users.global-activity') }}" class="d-flex flex-wrap" style="gap: 10px; align-items: flex-end;">
                
                {{-- Action Type Filter --}}
                <div style="flex: 1; min-width: 150px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 4px; color: #1c1c1e;">
                        Action Type
                    </label>
                    <select name="action" class="um-select" style="width: 100%;">
                        <option value="">All Actions</option>
                        <option value="create" {{ request('action') === 'create' ? 'selected' : '' }}>Create</option>
                        <option value="update" {{ request('action') === 'update' ? 'selected' : '' }}>Update</option>
                        <option value="delete" {{ request('action') === 'delete' ? 'selected' : '' }}>Delete</option>
                        <option value="toggle" {{ request('action') === 'toggle' ? 'selected' : '' }}>Status Change</option>
                        <option value="login" {{ request('action') === 'login' ? 'selected' : '' }}>Login</option>
                        <option value="logout" {{ request('action') === 'logout' ? 'selected' : '' }}>Logout</option>
                    </select>
                </div>

                {{-- User Filter --}}
                <div style="flex: 1; min-width: 150px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 4px; color: #1c1c1e;">
                        Performed By
                    </label>
                    <select name="user_id" class="um-select" style="width: 100%;">
                        <option value="">All Users</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }} {{ $u->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Date Filter --}}
                <div style="flex: 1; min-width: 150px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 4px; color: #1c1c1e;">
                        Date
                    </label>
                    <input type="date" name="date" value="{{ request('date') }}" class="um-select" style="width: 100%;">
                </div>

                <button type="submit" class="um-btn-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>

                @if(request()->hasAny(['action', 'user_id', 'date']))
                    <a href="{{ route('users.global-activity') }}" class="um-btn-ghost">
                        <i class="fas fa-redo-alt"></i> Reset
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- ══ Activity List ════════════════════════════════════════ --}}
    <div class="card">
        <div class="card-body" style="padding: 0;">
            @if($logs && count($logs) > 0)
                <div class="activity-list">
                    @foreach($logs as $log)
                        <div class="activity-row" style="
                            display: flex;
                            gap: 12px;
                            padding: 16px;
                            border-bottom: 1px solid rgba(0,0,0,.05);
                            align-items: flex-start;
                        ">
                            <!-- Icon -->
                            <div style="
                                flex-shrink: 0;
                                width: 40px;
                                height: 40px;
                                border-radius: 50%;
                                background-color: {{ $log->getActionColor() }};
                                opacity: 0.15;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                color: {{ $log->getActionColor() }};
                                font-size: 0.9rem;
                            ">
                                <i class="{{ $log->getActionIcon() }}"></i>
                            </div>

                            <!-- Content -->
                            <div style="flex: 1; min-width: 0;">
                                <div style="display: flex; gap: 8px; align-items: baseline; margin-bottom: 4px; flex-wrap: wrap;">
                                    <span style="font-weight: 600; color: #1c1c1e;">
                                        {{ $log->user?->name }} {{ $log->user?->last_name }}
                                    </span>
                                    <span style="
                                        color: {{ $log->getActionColor() }};
                                        font-weight: 500;
                                        font-size: 0.85rem;
                                        padding: 2px 6px;
                                        background: rgba(0, 0, 0, 0.05);
                                        border-radius: 3px;
                                    ">
                                        {{ $log->getActionLabel() }}
                                    </span>
                                </div>

                                <p style="margin: 0 0 4px; color: #555; font-size: 0.9rem;">
                                    {{ $log->description }}
                                </p>

                                <div style="display: flex; gap: 16px; font-size: 0.75rem; color: var(--ios-gray);">
                                    <span title="{{ $log->getFormattedDate() }}">
                                        <i class="fas fa-clock"></i> {{ $log->getTimeAgo() }}
                                    </span>
                                    @if($log->ip_address)
                                        <span>
                                            <i class="fas fa-globe"></i> {{ $log->ip_address }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Action: View Details --}}
                            @if($log->action_type === 'update' && $log->metadata)
                                <div style="flex-shrink: 0;">
                                    <button onclick="toggleDetails(this)" class="um-btn-ghost" style="padding: 4px 8px;">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                </div>

                                <details style="width: 100%;" class="activity-details">
                                    <summary style="display: none;"></summary>
                                    <div style="
                                        margin-top: 12px;
                                        padding: 12px;
                                        background: rgba(0,0,0,.02);
                                        border-radius: 6px;
                                        font-size: 0.8rem;
                                    ">
                                        <div style="font-weight: 600; margin-bottom: 8px; color: #1c1c1e;">
                                            Changes Made:
                                        </div>

                                        @php
                                            $changedFields = $log->metadata['changed_fields'] ?? [];
                                            $oldValues = $log->metadata['old_values'] ?? [];
                                            $newValues = $log->metadata['new_values'] ?? [];
                                        @endphp

                                        @foreach($changedFields as $field)
                                            <div style="margin-bottom: 8px;">
                                                <div style="
                                                    font-weight: 600;
                                                    text-transform: capitalize;
                                                    margin-bottom: 3px;
                                                    color: #1c1c1e;
                                                ">
                                                    {{ str_replace('_', ' ', $field) }}
                                                </div>
                                                <div style="display: flex; gap: 8px;">
                                                    <span style="
                                                        padding: 3px 6px;
                                                        background: rgba(255,59,48,.1);
                                                        color: #c0392b;
                                                        border-radius: 3px;
                                                        flex: 1;
                                                        word-break: break-all;
                                                    ">
                                                        {{ $oldValues[$field] ?? '(empty)' }}
                                                    </span>
                                                    <span style="color: #888;">→</span>
                                                    <span style="
                                                        padding: 3px 6px;
                                                        background: rgba(52,199,89,.1);
                                                        color: #27ae60;
                                                        border-radius: 3px;
                                                        flex: 1;
                                                        word-break: break-all;
                                                    ">
                                                        {{ $newValues[$field] ?? '(empty)' }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </details>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div style="padding: 16px; border-top: 1px solid rgba(0,0,0,.05);">
                    {{ $logs->links() }}
                </div>
            @else
                <div style="
                    text-align: center;
                    padding: 40px 20px;
                    color: var(--ios-gray);
                ">
                    <i class="fas fa-inbox" style="
                        font-size: 3rem;
                        opacity: 0.2;
                        display: block;
                        margin-bottom: 12px;
                    "></i>
                    <p style="margin: 0; font-size: 1rem;">No activities found</p>
                </div>
            @endif
        </div>
    </div>

</div>
</div>

<script>
function toggleDetails(btn) {
    const details = btn.closest('.activity-row').querySelector('details');
    if (details) {
        details.open = !details.open;
        btn.classList.toggle('active');
    }
}
</script>

<style>
.activity-list .activity-row:last-child {
    border-bottom: none;
}

.activity-row:hover {
    background: rgba(0, 122, 255, 0.02);
}
</style>

@endsection