{{-- ═══════════════════════════════════════════════════════════════════════════
     COMPONENT 2: User Card with Online Status
     Usage: <x-user-card :user="$user" />
     ═════════════════════════════════════════════════════════════════════════ --}}

{{-- File: resources/views/components/user-card.blade.php --}}

@props(['user'])

<div class="card user-card">
    <div class="card-body">
        <div class="d-flex align-items-start justify-content-between mb-3">
            <div>
                <h6 class="card-title mb-1">{{ $user->fullName ?? $user->name }}</h6>
                <small class="text-muted">{{ $user->email }}</small>
            </div>
            
            {{-- Online Status Indicator --}}
            <div class="online-indicator">
                <i class="fas {{ $user->getPresenceIcon() }} fa-lg" 
                   title="{{ $user->getFormattedStatus() }}"
                   style="color: {{ match($user->presence_status) {
                       'online' => '#00b894',
                       'away' => '#fdcb6e',
                       'busy' => '#e17055',
                       default => '#b2bec3'
                   } }}">
                </i>
            </div>
        </div>
        
        {{-- Status Information --}}
        <div class="mb-3">
            <small class="text-muted" data-formatted-status>
                {{ $user->getFormattedStatus() }}
            </small>
        </div>
        
        {{-- Device Information --}}
        @if($user->current_device)
        <div class="mb-2">
            <small class="text-muted">
                <i class="fas {{ $user->getDeviceIcon() }} mr-1"></i>
                {{ ucfirst($user->current_device) }}
                @if($user->getBrowserName())
                via {{ $user->getBrowserName() }}
                @endif
            </small>
        </div>
        @endif
        
        {{-- Last Activity --}}
        <div>
            <small class="text-muted">
                @if($user->is_online)
                    <i class="fas fa-circle text-success"></i> Online now
                @else
                    <i class="fas fa-circle text-secondary"></i> {{ $user->last_seen?->diffForHumans() ?? 'Never' }}
                @endif
            </small>
        </div>
    </div>
</div>

<style>
    .user-card {
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 8px;
    }
    
    .online-indicator {
        position: relative;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        border-radius: 50%;
    }
</style>