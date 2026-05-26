@extends('layouts.admin')

@section('title', 'User Management')

@section('main-content')
<div class="fh-container" style="padding-top: 3.5rem;">

    <div class="fh-header">
        <div class="fh-header__left">
            <h1 class="fh-page-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                User Management
            </h1>
        </div>
        <div>
            <br>
            <a href="{{ route('users.create') }}" class="fh-btn" style="background: #10b981; color: #fff; border: none; text-decoration: none;">
                <svg viewBox="0 0 20 20" fill="currentColor" style="width: 1rem; height: 1rem; margin-right: 0.25rem;"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                Add User
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="fh-toast fh-toast--success">✓ {{ session('success') }}</div>
    @endif
    
    @if(session('error'))
        <div class="fh-toast fh-toast--error">{{ session('error') }}</div>
    @endif

    <div class="fh-stats">
        <div class="fh-stat">
            <div class="fh-stat__value">{{ $users->total() }}</div>
            <div class="fh-stat__label">Total Users</div>
        </div>
        <div class="fh-stat">
            <div class="fh-stat__value">{{ $users->where('status', 'active')->count() }}</div>
            <div class="fh-stat__label">Active</div>
        </div>
        <div class="fh-stat">
            <div class="fh-stat__value">{{ $users->where('status', 'inactive')->count() }}</div>
            <div class="fh-stat__label">Inactive</div>
        </div>
        <div class="fh-stat">
            <div class="fh-stat__value">{{ $users->where('status', 'suspended')->count() }}</div>
            <div class="fh-stat__label">Suspended</div>
        </div>
    </div>

    <div class="fh-settings-grid" style="margin-bottom: 1rem;">
        <form method="GET" action="{{ route('users.index') }}" id="filterForm" style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
            <div style="position: relative; flex: 1; min-width: 200px;">
                <svg viewBox="0 0 20 20" fill="currentColor" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); width: 1rem; height: 1rem; color: #9ca3af;"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, EIN…" style="width: 100%; padding: 0.5rem 0.75rem 0.5rem 2.25rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem;">
                @if(request('search'))
                    <a href="{{ route('users.index', request()->except('search','page')) }}" style="position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); color: #9ca3af;">✕</a>
                @endif
            </div>

            <select name="status" onchange="this.form.submit()" style="padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem;">
                <option value="">All Status</option>
                <option value="active" {{ request('status')==='active' ?'selected':'' }}>Active</option>
                <option value="inactive" {{ request('status')==='inactive' ?'selected':'' }}>Inactive</option>
                <option value="suspended" {{ request('status')==='suspended' ?'selected':'' }}>Suspended</option>
            </select>

            <select name="department_id" onchange="this.form.submit()" style="padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem;">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id')==$dept->id ?'selected':'' }}>{{ $dept->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="fh-btn" style="background: #2563eb; color: #fff; border: none;">Filter</button>

            @if(request()->hasAny(['search','status','department_id']))
                <a href="{{ route('users.index') }}" class="fh-btn fh-btn--ghost">Reset</a>
            @endif
        </form>
    </div>

    @if($users->isEmpty())
        <div class="fh-settings-grid">
            <div class="fh-setting-row" style="text-align: center; justify-content: center; padding: 3rem;">
                <div>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 4rem; height: 4rem; color: #9ca3af; margin-bottom: 1rem;">
                        <path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <p style="color: #6b7280; margin-bottom: 1rem;">No users found</p>
                    @if(request()->hasAny(['search','status','department_id']))
                        <a href="{{ route('users.index') }}" class="fh-btn" style="background: #2563eb; color: #fff; border: none; text-decoration: none;">Clear filters</a>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="fh-tabs" id="viewToggleButtons">
            <button type="button" class="fh-tab" data-view="tile" onclick="switchUserView('tile')">
                <svg viewBox="0 0 20 20" fill="currentColor" style="width: 1rem; height: 1rem; margin-right: 0.25rem;"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                Tile
            </button>
            <button type="button" class="fh-tab fh-tab--active" data-view="list" onclick="switchUserView('list')">
                <svg viewBox="0 0 20 20" fill="currentColor" style="width: 1rem; height: 1rem; margin-right: 0.25rem;"><path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>
                List
            </button>
        </div>

        <div class="fh-settings-grid" id="umUserGrid">
            @foreach($users as $u)
                @php
                    $primaryDept = $u->departments->firstWhere('pivot.is_primary', 1) ?? $u->departments->first();
                    $role = $u->roles->first();
                    $lvl = $role?->level ?? 0;
                    $g = match(true) {
                        $lvl >= 90 => ['#7c3aed','#a855f7'],
                        $lvl >= 70 => ['#1d4ed8','#3b82f6'],
                        $lvl >= 50 => ['#0f766e','#14b8a6'],
                        $lvl >= 35 => ['#b45309','#f59e0b'],
                        default    => ['#4b5563','#9ca3af'],
                    };
                    $dot = match($u->status) {
                        'active'    => '#34C759',
                        'suspended' => '#FF3B30',
                        default     => '#8E8E93',
                    };
                @endphp

                <div class="fh-role-block" 
                     style="{{ $u->status !== 'active' ? 'opacity: 0.75;' : '' }}"
                     data-user-id="{{ $u->id }}"
                     data-name="{{ $u->name }} {{ $u->last_name }}"
                     data-ein="{{ $u->ein ?? 'N/A' }}"
                     data-email="{{ $u->email }}"
                     data-department="{{ $primaryDept?->name ?? 'N/A' }}"
                     data-role="{{ $role?->name ?? 'N/A' }}"
                     data-status="{{ $u->status }}">
                    <div class="tile-view" style="display: none;">
                        <div style="text-align: center; margin-bottom: 1rem;">
                            @if($u->avatar)
                                <img src="{{ asset('storage/'.$u->avatar) }}" alt="{{ $u->name }}" style="width: 4rem; height: 4rem; border-radius: 50%; object-fit: cover; margin: 0 auto;">
                            @else
                                <div style="width: 4rem; height: 4rem; background: linear-gradient(135deg, {{ $g[0] }}, {{ $g[1] }}); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; color: #fff; font-weight: 600; font-size: 1.25rem;">
                                    {{ strtoupper(substr($u->name,0,1).substr($u->last_name ?? '',0,1)) }}
                                </div>
                            @endif
                            <span style="position: absolute; top: 0.5rem; right: 0.5rem; width: 0.75rem; height: 0.75rem; background: {{ $dot }}; border-radius: 50%; border: 2px solid #fff;"></span>
                        </div>
                        <div class="fh-role-block__title" style="text-align: center;">{{ $u->name }} {{ $u->last_name }}</div>
                        <p style="text-align: center; color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">
                            <svg viewBox="0 0 20 20" fill="currentColor" style="width: 0.75rem; height: 0.75rem; margin-right: 0.25rem;"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v2h2a2 2 0 012 2v8a2 2 0 01-2 2H6a2 2 0 01-2-2v-8a2 2 0 012-2h2V4z" clip-rule="evenodd"/></svg>
                            {{ $u->ein ?? 'No EIN' }}
                        </p>
                        @if($role)
                            <div style="text-align: center; margin-bottom: 0.5rem;">
                                <span class="fh-badge fh-badge--blue">{{ $role->name }}</span>
                            </div>
                        @endif
                        <p style="text-align: center; color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">
                            <svg viewBox="0 0 20 20" fill="currentColor" style="width: 0.75rem; height: 0.75rem; margin-right: 0.25rem;"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/></svg>
                            {{ Str::limit($primaryDept?->name ?? '—', 20) }}
                        </p>
                        <p style="text-align: center; color: #6b7280; font-size: 0.875rem; margin-bottom: 1rem;">
                            <svg viewBox="0 0 20 20" fill="currentColor" style="width: 0.75rem; height: 0.75rem; margin-right: 0.25rem;"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg>
                            {{ Str::limit($u->email, 25) }}
                        </p>
                        <div style="display: flex; justify-content: center; gap: 0.5rem;">
                            <a href="javascript:void(0)" onclick="umShowInfo({{ $u->id }})" class="fh-btn fh-btn--ghost" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">Info</a>
                            <a href="{{ route('users.edit', $u->id) }}" class="fh-btn fh-btn--ghost" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">Edit</a>
                            @if($u->status === 'active')
                                <form action="{{ route('users.toggle', $u->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="fh-btn" style="background: #ef4444; color: #fff; border: none; padding: 0.25rem 0.5rem; font-size: 0.75rem;" onclick="return confirm('Suspend {{ $u->name }}?')">Suspend</button>
                                </form>
                            @else
                                <form action="{{ route('users.toggle', $u->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="fh-btn" style="background: #10b981; color: #fff; border: none; padding: 0.25rem 0.5rem; font-size: 0.75rem;">Activate</button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <div class="list-view">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="position: relative;">
                                @if($u->avatar)
                                    <img src="{{ asset('storage/'.$u->avatar) }}" alt="{{ $u->name }}" style="width: 3rem; height: 3rem; border-radius: 50%; object-fit: cover;">
                                @else
                                    <div style="width: 3rem; height: 3rem; background: linear-gradient(135deg, {{ $g[0] }}, {{ $g[1] }}); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 600;">
                                        {{ strtoupper(substr($u->name,0,1).substr($u->last_name ?? '',0,1)) }}
                                    </div>
                                @endif
                                <span style="position: absolute; bottom: 0; right: 0; width: 0.75rem; height: 0.75rem; background: {{ $dot }}; border-radius: 50%; border: 2px solid #fff;"></span>
                            </div>
                            <div style="flex-grow: 1;">
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                    <span style="font-weight: 600; color: #111827;">{{ $u->name }} {{ $u->last_name }}</span>
                                    @if($role)
                                        <span class="fh-badge fh-badge--blue">{{ $role->name }}</span>
                                    @endif
                                </div>
                                <div style="display: flex; gap: 1rem; font-size: 0.875rem; color: #6b7280; flex-wrap: wrap;">
                                    <span><svg viewBox="0 0 20 20" fill="currentColor" style="width: 0.75rem; height: 0.75rem; margin-right: 0.25rem;"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v2h2a2 2 0 012 2v8a2 2 0 01-2 2H6a2 2 0 01-2-2v-8a2 2 0 012-2h2V4z" clip-rule="evenodd"/></svg>{{ $u->ein ?? 'No EIN' }}</span>
                                    <span><svg viewBox="0 0 20 20" fill="currentColor" style="width: 0.75rem; height: 0.75rem; margin-right: 0.25rem;"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg>{{ $u->email }}</span>
                                    <span><svg viewBox="0 0 20 20" fill="currentColor" style="width: 0.75rem; height: 0.75rem; margin-right: 0.25rem;"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/></svg>{{ $primaryDept?->name ?? '—' }}</span>
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.5rem; flex-shrink: 0;">
                                <a href="javascript:void(0)" onclick="umShowInfo({{ $u->id }})" class="fh-btn fh-btn--ghost" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;">Info</a>
                                <a href="{{ route('users.edit', $u->id) }}" class="fh-btn fh-btn--ghost" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;">Edit</a>
                                @if($u->status === 'active')
                                    <form action="{{ route('users.toggle', $u->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="fh-btn" style="background: #ef4444; color: #fff; border: none; padding: 0.25rem 0.5rem; font-size: 0.875rem;" onclick="return confirm('Suspend {{ $u->name }}?')">Suspend</button>
                                    </form>
                                @else
                                    <form action="{{ route('users.toggle', $u->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="fh-btn" style="background: #10b981; color: #fff; border: none; padding: 0.25rem 0.5rem; font-size: 0.875rem;">Activate</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div style="margin-top: 1rem;">
            {{ $users->links() }}
        </div>
    @endif

</div>

@push('styles')
@include('filehosting::_partials.styles')
@endpush

<style>
.fh-role-block .tile-view,
.fh-role-block .list-view {
    transition: display 0.2s;
}
#umUserGrid.tile-view-active {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1rem;
}
#umUserGrid.tile-view-active .fh-role-block {
    margin-bottom: 0;
}
#umUserGrid.tile-view-active .tile-view {
    display: block !important;
}
#umUserGrid.tile-view-active .list-view {
    display: none !important;
}
</style>

<script>
window.switchUserView = function(view) {
    console.log('[Users] switchUserView called:', view);
    
    var grid = document.getElementById('umUserGrid');
    if (!grid) return;
    
    var buttons = document.querySelectorAll('#viewToggleButtons .fh-tab');
    buttons.forEach(function(btn) {
        if (btn.dataset.view === view) {
            btn.classList.add('fh-tab--active');
        } else {
            btn.classList.remove('fh-tab--active');
        }
    });
    
    var blocks = grid.querySelectorAll('.fh-role-block');
    blocks.forEach(function(block) {
        var tileView = block.querySelector('.tile-view');
        var listView = block.querySelector('.list-view');
        
        if (view === 'list') {
            grid.classList.remove('tile-view-active');
            if (tileView) tileView.style.display = 'none';
            if (listView) listView.style.display = 'block';
        } else {
            grid.classList.add('tile-view-active');
            if (tileView) tileView.style.display = 'block';
            if (listView) listView.style.display = 'none';
        }
    });
    
    localStorage.setItem('umViewMode', view);
};

document.addEventListener('DOMContentLoaded', function() {
    var savedView = localStorage.getItem('umViewMode') || 'list';
    switchUserView(savedView);
    
    var searchInput = document.getElementById('umSearchInput');
    if (searchInput) {
        var _t;
        searchInput.addEventListener('input', function() {
            clearTimeout(_t);
            _t = setTimeout(function() { 
                var form = document.getElementById('filterForm');
                if (form) form.submit(); 
            }, 500);
        });
    }
});

function umShowInfo(userId) {
    var userBlock = document.querySelector('.fh-role-block[data-user-id="' + userId + '"]');
    
    if (!userBlock) {
        Swal.fire('Error', 'User not found', 'error');
        return;
    }
    
    var userData = {
        name: userBlock.dataset.name || 'N/A',
        ein: userBlock.dataset.ein || 'N/A',
        email: userBlock.dataset.email || 'N/A',
        department: userBlock.dataset.department || 'N/A',
        role: userBlock.dataset.role || 'N/A',
        status: userBlock.dataset.status || 'N/A'
    };
    
    var statusColor = userData.status === 'active' ? '#10b981' : '#6b7280';
    var statusText = userData.status === 'active' ? 'Active' : (userData.status === 'suspended' ? 'Suspended' : 'Inactive');
    
    var content = `
        <div style="text-align: left; min-width: 300px;">
            <div style="margin-bottom: 1rem; padding: 0.75rem; background: #f9fafb; border-radius: 0.5rem;">
                <strong>Name:</strong><br>
                <span style="font-size: 1.125rem;">${userData.name}</span>
            </div>
            <div style="margin-bottom: 1rem;">
                <strong style="color: #6b7280; font-size: 0.75rem;">EIN</strong><br>
                <span>${userData.ein}</span>
            </div>
            <div style="margin-bottom: 1rem;">
                <strong style="color: #6b7280; font-size: 0.75rem;">Email</strong><br>
                <span>${userData.email}</span>
            </div>
            <div style="margin-bottom: 1rem;">
                <strong style="color: #6b7280; font-size: 0.75rem;">Department</strong><br>
                <span>${userData.department}</span>
            </div>
            <div style="margin-bottom: 1rem;">
                <strong style="color: #6b7280; font-size: 0.75rem;">Role</strong><br>
                <span class="fh-badge fh-badge--blue">${userData.role}</span>
            </div>
            <div style="margin-bottom: 1.5rem;">
                <strong style="color: #6b7280; font-size: 0.75rem;">Status</strong><br>
                <span style="color: ${statusColor}; font-weight: 600;">● ${statusText}</span>
            </div>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; border-top: 1px solid #e5e7eb; padding-top: 1rem;">
                <button type="button" class="swal2-styled" style="background: #2563eb; color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 0.25rem; cursor: pointer;" onclick="sendMessage(${userId}, '${userData.name}')">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width: 1rem; height: 1rem; vertical-align: middle; margin-right: 0.25rem;"><path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9z" clip-rule="evenodd"/></svg>
                    Message
                </button>
                <button type="button" class="swal2-styled" style="background: #f59e0b; color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 0.25rem; cursor: pointer;" onclick="sendNotification(${userId}, '${userData.name}')">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width: 1rem; height: 1rem; vertical-align: middle; margin-right: 0.25rem;"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/></svg>
                    Notify
                </button>
            </div>
        </div>
    `;
    
    Swal.fire({
        title: 'User Information',
        html: content,
        icon: 'info',
        confirmButtonText: 'Close',
        width: '400px'
    });
}

function sendMessage(userId, userName) {
    Swal.fire({
        title: 'Send Message to ' + userName,
        input: 'textarea',
        inputLabel: 'Message',
        inputPlaceholder: 'Type your message here...',
        showCancelButton: true,
        confirmButtonText: 'Send',
        cancelButtonText: 'Cancel'
    }).then(function(result) {
        if (result.isConfirmed && result.value) {
            fetch('/api/users/' + userId + '/message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ message: result.value })
            }).then(function() {
                Swal.fire('Sent!', 'Message has been sent.', 'success');
            }).catch(function() {
                Swal.fire('Demo', 'Message feature requires API endpoint.', 'info');
            });
        }
    });
}

function sendNotification(userId, userName) {
    Swal.fire({
        title: 'Send Notification to ' + userName,
        input: 'textarea',
        inputLabel: 'Notification',
        inputPlaceholder: 'Type your notification here...',
        showCancelButton: true,
        confirmButtonText: 'Send',
        cancelButtonText: 'Cancel'
    }).then(function(result) {
        if (result.isConfirmed && result.value) {
            fetch('/api/users/' + userId + '/notify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ notification: result.value })
            }).then(function() {
                Swal.fire('Sent!', 'Notification has been sent.', 'success');
            }).catch(function() {
                Swal.fire('Demo', 'Notification feature requires API endpoint.', 'info');
            });
        }
    });
}
</script>

@endsection