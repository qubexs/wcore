{{-- resources/views/users/partials/activity-section.blade.php --}}

<div class="card mt-4">
    <div class="card-body" style="padding: 1.25rem;">
        <p class="uf-section-label">
            <i class="fas fa-history"></i> Activity History
        </p>

        @if($activityLogs && count($activityLogs) > 0)
            <div class="activity-timeline">
                @foreach($activityLogs as $log)
                    <div class="activity-item" style="
                        display: flex;
                        gap: 12px;
                        padding: 12px;
                        border-radius: 8px;
                        background: rgba(0,0,0,.02);
                        margin-bottom: 10px;
                        border-left: 3px solid {{ $log->getActionColor() }};
                    ">
                        <!-- Action Badge -->
                        <div class="activity-badge" style="
                            flex-shrink: 0;
                            width: 40px;
                            height: 40px;
                            border-radius: 50%;
                            background-color: {{ $log->getActionColor() }};
                            opacity: 0.1;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            color: {{ $log->getActionColor() }};
                            font-size: 1rem;
                        ">
                            <i class="{{ $log->getActionIcon() }}"></i>
                        </div>

                        <!-- Activity Details -->
                        <div style="flex: 1; min-width: 0;">
                            <div style="
                                display: flex;
                                gap: 8px;
                                align-items: baseline;
                                flex-wrap: wrap;
                                margin-bottom: 4px;
                            ">
                                <span style="
                                    font-weight: 600;
                                    color: #1c1c1e;
                                    font-size: 0.9rem;
                                ">
                                    {{ $log->user?->name }} {{ $log->user?->last_name }}
                                </span>
                                <span style="
                                    color: {{ $log->getActionColor() }};
                                    font-weight: 500;
                                    font-size: 0.85rem;
                                ">
                                    {{ $log->getActionLabel() }}
                                </span>
                            </div>

                            <p style="
                                margin: 0 0 6px;
                                color: #555;
                                font-size: 0.85rem;
                                line-height: 1.4;
                            ">
                                {{ $log->description }}
                            </p>

                            <!-- Show Changes if Update -->
                            @if($log->action_type === 'update' && $log->metadata)
                                <details style="margin-top: 6px;">
                                    <summary style="
                                        cursor: pointer;
                                        font-size: 0.8rem;
                                        color: var(--ios-blue);
                                        user-select: none;
                                    ">
                                        <i class="fas fa-chevron-right" style="margin-right: 4px; font-size: 0.7rem;"></i>
                                        View Changes
                                    </summary>

                                    <div style="
                                        margin-top: 8px;
                                        padding: 10px;
                                        background: white;
                                        border-radius: 4px;
                                        font-size: 0.8rem;
                                        max-height: 250px;
                                        overflow-y: auto;
                                        border: 1px solid rgba(0,0,0,.05);
                                    ">
                                        @php
                                            $changedFields = $log->metadata['changed_fields'] ?? [];
                                            $oldValues = $log->metadata['old_values'] ?? [];
                                            $newValues = $log->metadata['new_values'] ?? [];
                                        @endphp

                                        @if(count($changedFields) > 0)
                                            @foreach($changedFields as $field)
                                                <div style="
                                                    margin-bottom: 8px;
                                                    padding-bottom: 8px;
                                                    border-bottom: 1px solid rgba(0,0,0,.05);
                                                ">
                                                    <div style="
                                                        font-weight: 600;
                                                        color: #1c1c1e;
                                                        margin-bottom: 3px;
                                                        text-transform: capitalize;
                                                    ">
                                                        {{ str_replace('_', ' ', $field) }}
                                                    </div>
                                                    <div style="display: flex; gap: 8px; align-items: center; font-size: 0.75rem;">
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
                                                        <i class="fas fa-arrow-right" style="color: #888; flex-shrink: 0;"></i>
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
                                        @endif
                                    </div>
                                </details>
                            @endif

                            <!-- Metadata -->
                            <div style="
                                margin-top: 6px;
                                display: flex;
                                gap: 12px;
                                font-size: 0.75rem;
                                color: var(--ios-gray);
                                flex-wrap: wrap;
                            ">
                                <span title="{{ $log->getFormattedDate() }}">
                                    <i class="fas fa-clock"></i>
                                    {{ $log->getTimeAgo() }}
                                </span>
                                @if($log->ip_address)
                                    <span>
                                        <i class="fas fa-globe"></i>
                                        {{ $log->ip_address }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($activityLogs instanceof \Illuminate\Pagination\Paginator)
                <div style="margin-top: 16px;">
                    {{ $activityLogs->links() }}
                </div>
            @endif
        @else
            <div style="
                text-align: center;
                padding: 20px;
                color: var(--ios-gray);
            ">
                <i class="fas fa-inbox" style="
                    font-size: 2rem;
                    opacity: 0.3;
                    display: block;
                    margin-bottom: 8px;
                "></i>
                <p style="margin: 0;">No activity recorded for this user yet</p>
            </div>
        @endif
    </div>
</div>

<style>
.activity-timeline {
    display: flex;
    flex-direction: column;
    gap: 0;
}

.activity-item {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

details summary::-webkit-details-marker {
    display: none;
}

details[open] summary i {
    transform: rotate(90deg);
    transition: transform 0.2s;
}
</style>