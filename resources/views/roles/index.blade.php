@extends('layouts.admin')

@section('title', 'Roles Management')

@section('main-content')
<div class="fh-container" style="padding-top:3.5rem;">

    <div class="fh-header">
        <div class="fh-header__left">
            <h1 class="fh-page-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Roles Management
            </h1>
        </div>
        <div>
            @can('manage roles')
            <br>
                <a href="{{ route('roles.create') }}" class="fh-btn" style="background: #10b981; color: #fff; border: none; text-decoration: none;">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width: 1rem; height: 1rem; margin-right: 0.25rem;"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                    Add New Role
                </a>
            @endcan
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
            <div class="fh-stat__value">{{ $roles->count() }}</div>
            <div class="fh-stat__label">Total Roles</div>
        </div>
        <div class="fh-stat">
            <div class="fh-stat__value">{{ $roles->sum(fn($r) => $r->permissions->count()) }}</div>
            <div class="fh-stat__label">Permissions</div>
        </div>
        <div class="fh-stat">
            <div class="fh-stat__value">{{ $roles->sum(fn($r) => $r->departments?->count() ?? 0) }}</div>
            <div class="fh-stat__label">Departments</div>
        </div>
        <div class="fh-stat">
            <div class="fh-stat__value">{{ $roles->sum(fn($r) => $r->users?->count() ?? 0) }}</div>
            <div class="fh-stat__label">Assigned Users</div>
        </div>
    </div>

    @if($roles->isEmpty())
        <div class="fh-settings-grid">
            <div class="fh-setting-row" style="text-align: center; justify-content: center; padding: 3rem;">
                <div>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 4rem; height: 4rem; color: #9ca3af; margin-bottom: 1rem;">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <p style="color: #6b7280; margin-bottom: 1rem;">No roles found</p>
                    @can('manage roles')
                        <a href="{{ route('roles.create') }}" class="fh-btn" style="background: #10b981; color: #fff; border: none; text-decoration: none;">Create First Role</a>
                    @endcan
                </div>
            </div>
        </div>
    @else
        <div class="fh-tabs" id="viewToggleButtons">
            <button type="button" class="fh-tab view-toggle" data-view="tile" onclick="switchViewToggle('tile')">
                <svg viewBox="0 0 20 20" fill="currentColor" style="width: 1rem; height: 1rem; margin-right: 0.25rem;"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                Tile
            </button>
            <button type="button" class="fh-tab view-toggle active" data-view="list" onclick="switchViewToggle('list')">
                <svg viewBox="0 0 20 20" fill="currentColor" style="width: 1rem; height: 1rem; margin-right: 0.25rem;"><path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>
                List
            </button>
        </div>

        <div class="fh-settings-grid" id="rolesGrid">
            @foreach($roles as $role)
                @php
                    $g = match(true) {
                        $role->level >= 90 => ['#7c3aed','#a855f7'],
                        $role->level >= 70 => ['#1d4ed8','#3b82f6'],
                        $role->level >= 50 => ['#0f766e','#14b8a6'],
                        $role->level >= 35 => ['#b45309','#f59e0b'],
                        default    => ['#4b5563','#9ca3af'],
                    };
                @endphp

                <div class="fh-role-block role-tile">
                    <div class="tile-view" style="display: none;">
                        <div style="text-align: center; margin-bottom: 1rem;">
                            <div style="width: 4rem; height: 4rem; background: linear-gradient(135deg, {{ $g[0] }}, {{ $g[1] }}); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                <svg viewBox="0 0 20 20" fill="currentColor" style="width: 2rem; height: 2rem; color: #fff;"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            </div>
                        </div>
                        <div class="fh-role-block__title" style="text-align: center;">{{ $role->name }}</div>
                        <p style="text-align: center; color: #6b7280; font-size: 0.875rem; margin-bottom: 0.75rem;">
                            @if($role->description)
                                {{ Str::limit($role->description, 40) }}
                            @else
                                <span style="font-style: italic;">No description</span>
                            @endif
                        </p>
                        <div style="display: flex; justify-content: center; gap: 0.5rem; margin-bottom: 0.75rem; flex-wrap: wrap;">
                            <span class="fh-badge fh-badge--blue"><svg viewBox="0 0 20 20" fill="currentColor" style="width: 0.75rem; height: 0.75rem; margin-right: 0.25rem;"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>{{ $role->permissions->count() }}</span>
                            <span class="fh-badge fh-badge--blue"><svg viewBox="0 0 20 20" fill="currentColor" style="width: 0.75rem; height: 0.75rem; margin-right: 0.25rem;"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/></svg>{{ $role->departments?->count() ?? 0 }}</span>
                            <span class="fh-badge fh-badge--blue"><svg viewBox="0 0 20 20" fill="currentColor" style="width: 0.75rem; height: 0.75rem; margin-right: 0.25rem;"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>{{ $role->users?->count() ?? 0 }}</span>
                        </div>
                        <p style="text-align: center; margin-bottom: 1rem;">
                            <span class="fh-badge fh-badge--gray">{{ $role->guard_name }}</span>
                        </p>
                        <div style="display: flex; justify-content: center; gap: 0.5rem;">
                            <a href="{{ route('roles.edit', $role->id) }}" class="fh-btn fh-btn--ghost" style="padding: 0.25rem 0.75rem; font-size: 0.875rem;">Edit</a>
                            @can('manage roles')
                                <form action="{{ route('roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('Delete {{ $role->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="fh-btn" style="background: #ef4444; color: #fff; border: none; padding: 0.25rem 0.75rem; font-size: 0.875rem;">Delete</button>
                                </form>
                            @endcan
                        </div>
                    </div>

                    <div class="list-view">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 3rem; height: 3rem; background: linear-gradient(135deg, {{ $g[0] }}, {{ $g[1] }}); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg viewBox="0 0 20 20" fill="currentColor" style="width: 1.5rem; height: 1.5rem; color: #fff;"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            </div>
                            <div style="flex-grow: 1;">
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                    <span style="font-weight: 600; color: #111827;">{{ $role->name }}</span>
                                    <span class="fh-badge fh-badge--gray">{{ $role->guard_name }}</span>
                                </div>
                                <div style="display: flex; gap: 1rem; font-size: 0.875rem; color: #6b7280; flex-wrap: wrap;">
                                    <span><svg viewBox="0 0 20 20" fill="currentColor" style="width: 0.75rem; height: 0.75rem; color: #10b981; margin-right: 0.25rem;"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>{{ $role->permissions->count() }} Permissions</span>
                                    <span><svg viewBox="0 0 20 20" fill="currentColor" style="width: 0.75rem; height: 0.75rem; color: #2563eb; margin-right: 0.25rem;"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/></svg>{{ $role->departments?->count() ?? 0 }} Departments</span>
                                    <span><svg viewBox="0 0 20 20" fill="currentColor" style="width: 0.75rem; height: 0.75rem; color: #f59e0b; margin-right: 0.25rem;"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>{{ $role->users?->count() ?? 0 }} Users</span>
                                    @if($role->description)
                                        <span style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><svg viewBox="0 0 20 20" fill="currentColor" style="width: 0.75rem; height: 0.75rem; margin-right: 0.25rem;"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>{{ $role->description }}</span>
                                    @endif
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.5rem; flex-shrink: 0;">
                                <a href="{{ route('roles.edit', $role->id) }}" class="fh-btn fh-btn--ghost" style="padding: 0.25rem 0.75rem; font-size: 0.875rem;">Edit</a>
                                @can('manage roles')
                                    <form action="{{ route('roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('Delete {{ $role->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="fh-btn" style="background: #ef4444; color: #fff; border: none; padding: 0.25rem 0.75rem; font-size: 0.875rem;">Delete</button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
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
</style>

@endsection