@php
/**
 * macOS Dock Sidebar — SPA Edition
 * ─────────────────────────────────────────────────────────────────────────────
 * The dock is position:fixed so it always stays visible.
 * Navigation uses fetch() to swap only #content — no full page reload.
 * Old content slides/fades out while new content slides in simultaneously.
 */

use Illuminate\Support\Str;

$menus = \App\Models\Menu::whereNull('parent_id')
    ->where('is_active', 1)
    ->with(['children' => function ($q) {
        $q->where('is_active', 1)->orderBy('order');
    }])
    ->orderBy('order')
    ->get();

$currentRoute = Route::currentRouteName();
$user         = auth()->user();

$gradients = [
    'gradient-blue', 'gradient-purple', 'gradient-green', 'gradient-orange',
    'gradient-pink',  'gradient-teal',   'gradient-red',   'gradient-gray',
];
@endphp

{{-- ═══════════════════════════════════════════════════════════════════════
     DOCK  (position:fixed — always on screen)
     ═══════════════════════════════════════════════════════════════════════ --}}
<div id="macos-dock" class="macos-dock" role="navigation" aria-label="Main navigation">
    <div class="dock-track">

        @php $gi = 0; @endphp

        @foreach($menus as $menu)
            @php
                $canView     = $menu->is_active && (!$menu->permission || ($user && $user->can($menu->permission)));
                $isLocked    = (bool) $menu->is_locked;
                $hasChildren = $menu->children->count() > 0;
                $isActive    = ($currentRoute === $menu->route)
                    || ($hasChildren && $menu->children->contains(fn($c) => $c->route === $currentRoute));
                $gradClass   = $gradients[$gi % count($gradients)];
                $gi++;
                $panelId     = 'panel-menu-' . $menu->id;
                $directHref  = (!$hasChildren && $menu->route && !$isLocked) ? route($menu->route) : '#';
            @endphp

            @if($canView)
                <div class="dock-item {{ $isActive ? 'active' : '' }} {{ $isLocked ? 'dock-locked' : '' }}"
                     @if($hasChildren && !$isLocked) data-panel="{{ $panelId }}"
                     @else data-href="{{ $directHref }}" @endif
                     data-label="{{ $menu->title }}"
                     role="button" tabindex="0"
                     aria-label="{{ $menu->title }}{{ $isLocked ? ' (locked)' : '' }}">
                    <div class="dock-icon">
                        <div class="dock-icon-bg {{ $gradClass }}">
                            <i class="{{ $menu->icon ?? 'fas fa-circle' }}"></i>
                        </div>
                        @if($isLocked)<span class="dock-lock-badge"><i class="fas fa-lock"></i></span>@endif
                    </div>
                    <span class="dock-label">{{ $menu->title }}</span>
                    <div class="dock-dot {{ $isActive ? 'visible' : '' }}"></div>
                </div>
            @endif
        @endforeach

        <div class="dock-separator"></div>

        <div class="dock-item" data-panel="panel-profile"
             data-label="{{ Str::limit($user?->name ?? 'Account', 12) }}"
             role="button" tabindex="0" aria-label="User profile">
            <div class="dock-icon">
                <div class="dock-icon-bg gradient-pink dock-avatar-icon">
                    {{ strtoupper(substr($user?->name ?? 'G', 0, 1)) }}
                </div>
            </div>
            <span class="dock-label">{{ Str::limit($user?->name ?? 'Guest', 12) }}</span>
            <div class="dock-dot"></div>
        </div>

    </div>
</div>


{{-- ═══════════════════════════════════════════════════════════════════════
     PANEL WINDOWS
     ═══════════════════════════════════════════════════════════════════════ --}}

@foreach($menus as $menu)
    @php
        $canView     = $menu->is_active && (!$menu->permission || ($user && $user->can($menu->permission)));
        $hasChildren = $menu->children->count() > 0;
        $panelId     = 'panel-menu-' . $menu->id;
    @endphp
    @if($canView && $hasChildren)
        <div id="{{ $panelId }}" class="dock-panel" role="dialog" aria-label="{{ $menu->title }} panel">
            <div class="panel-titlebar">
                <div class="panel-traffic-lights">
                    <button class="tl-btn tl-close"    data-action="close"    aria-label="Close"></button>
                    <button class="tl-btn tl-minimize" data-action="minimize" aria-label="Minimise"></button>
                    <button class="tl-btn tl-maximize" data-action="maximize" aria-label="Fullscreen"></button>
                </div>
                <span class="panel-title">
                    <i class="{{ $menu->icon ?? 'fas fa-circle' }}" style="margin-right:5px;font-size:11px;opacity:0.5;"></i>
                    {{ $menu->title }}
                </span>
            </div>
            <div class="panel-body">
                <nav class="panel-nav">
                    @foreach($menu->children as $child)
                        @php
                            $childCanView = $child->is_active && (!$child->permission || ($user && $user->can($child->permission)));
                            $childLocked  = (bool) $child->is_locked;
                            $childActive  = $currentRoute === $child->route;
                            $childHref    = ($child->route && !$childLocked) ? route($child->route) : '#';
                        @endphp
                        @if($childCanView)
                            <a class="panel-nav-item {{ $childActive ? 'active' : '' }} {{ $childLocked ? 'locked' : '' }}"
                               href="{{ $childHref }}"
                               @if($childLocked) aria-disabled="true" tabindex="-1" @endif>
                                <i class="{{ $child->icon ?? 'fas fa-angle-right' }} panel-nav-icon"></i>
                                <span class="panel-nav-label">{{ $child->title }}</span>
                                @if($childLocked)
                                    <i class="fas fa-lock panel-nav-lock"></i>
                                @elseif($childActive)
                                    <i class="fas fa-chevron-right panel-nav-active-arrow"></i>
                                @endif
                            </a>
                        @endif
                    @endforeach
                </nav>
            </div>
        </div>
    @endif
@endforeach

{{-- Profile panel --}}
<div id="panel-profile" class="dock-panel" role="dialog" aria-label="Profile panel">
    <div class="panel-titlebar">
        <div class="panel-traffic-lights">
            <button class="tl-btn tl-close"    data-action="close"></button>
            <button class="tl-btn tl-minimize" data-action="minimize"></button>
            <button class="tl-btn tl-maximize" data-action="maximize"></button>
        </div>
        <span class="panel-title">
            <i class="fas fa-user-circle" style="margin-right:5px;font-size:11px;opacity:0.5;"></i>
            {{ $user?->name ?? 'Account' }}
        </span>
    </div>
    <div class="panel-body">
        <div class="panel-profile-card">
            <div class="panel-avatar">{{ strtoupper(substr($user?->name ?? 'G', 0, 1)) }}</div>
            <div class="panel-profile-info">
                <div class="panel-profile-name">{{ $user?->name ?? 'Guest' }}</div>
                <div class="panel-profile-email">{{ $user?->email ?? '' }}</div>
            </div>
        </div>
        <div class="panel-nav-divider"></div>
        <nav class="panel-nav">
            <a class="panel-nav-item {{ $currentRoute === 'profile' ? 'active' : '' }}" href="{{ route('profile') }}">
                <i class="fas fa-user panel-nav-icon"></i>
                <span class="panel-nav-label">My Profile</span>
            </a>
            <a class="panel-nav-item" href="#">
                <i class="fas fa-cog panel-nav-icon"></i>
                <span class="panel-nav-label">Account Settings</span>
            </a>
            <a class="panel-nav-item" href="#">
                <i class="fas fa-list-alt panel-nav-icon"></i>
                <span class="panel-nav-label">Activity Log</span>
            </a>
            <div class="panel-nav-divider"></div>
            <a class="panel-nav-item danger" href="#" data-toggle="modal" data-target="#logoutModal">
                <i class="fas fa-sign-out-alt panel-nav-icon"></i>
                <span class="panel-nav-label">Logout</span>
            </a>
        </nav>
    </div>
</div>

{{-- Backdrop --}}
<div id="dock-backdrop" class="dock-backdrop"></div>

{{-- Loading bar — thin stripe across top during SPA fetch --}}
<div id="spa-loader" class="spa-loader"></div>


