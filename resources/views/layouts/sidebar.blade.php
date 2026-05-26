@php
/**
 * sidebar.blade.php — macOS Dock (nav_mode = "dock")
 *
 * Two-layer access control:
 *   Layer 1 (Spatie) → $user->can($menu->permission)
 *   Layer 2 (Dept)   → user's dept is in department_menu for this menu
 *
 * SuperAdmin bypasses both layers.
 * Head of Hospital uses normal checks (SuperAdmin grants permissions via UI).
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

// ── RBAC Setup ─────────────────────────────────────────────────────
$isSuperAdmin = $user && ($user->hasRole('superadmin') || $user->hasRole('SuperAdmin'));

$allowedMenuIds = [];
if ($user && !$isSuperAdmin) {
    $userDeptIds = \DB::table('department_user')
        ->where('user_id', $user->id)
        ->pluck('department_id')
        ->toArray();

    if (!empty($userDeptIds)) {
        $allowedMenuIds = \DB::table('department_menu')
            ->whereIn('department_id', $userDeptIds)
            ->pluck('menu_id')
            ->unique()
            ->toArray();
    }
}

$canViewMenu = function (\App\Models\Menu $menu) use ($user, $isSuperAdmin, $allowedMenuIds): bool {
    if (!$menu->is_active) return false;
    if ($isSuperAdmin) return true;
    if (!$user) return false;
    // Layer 1: Spatie permission
    if ($menu->permission && !$user->can($menu->permission)) return false;
    // Layer 2: Department menu access
    if (!in_array($menu->id, $allowedMenuIds)) return false;
    return true;
};

$gradients = [
    'gradient-blue', 'gradient-purple', 'gradient-green', 'gradient-orange',
    'gradient-pink',  'gradient-teal',   'gradient-red',   'gradient-gray',
];

// Safe route helper — returns '#' if route requires parameters or doesn't exist
$safeRoute = function (?string $routeName): string {
    if (!$routeName) return '#';
    try {
        return route($routeName);
    } catch (\Throwable $e) {
        return '#';
    }
};

$primaryDeptName = null;
if ($user && !$isSuperAdmin) {
    $primaryDeptName = \DB::table('department_user')
        ->join('departments', 'departments.id', '=', 'department_user.department_id')
        ->where('department_user.user_id', $user->id)
        ->where('department_user.is_primary', 1)
        ->value('departments.name');
}
@endphp

{{-- ════════════════════════════  DOCK  ════════════════════════════ --}}
<div id="macos-dock" class="macos-dock" role="navigation" aria-label="Main navigation">
    <div class="dock-track">
        @php $gi = 0; @endphp

        @foreach($menus as $menu)
            @php
                $isVisible   = $canViewMenu($menu);
                $isLocked    = (bool) $menu->is_locked;
                $hasChildren = $menu->children->count() > 0;
                $isActive    = ($currentRoute === $menu->route)
                    || ($hasChildren && $menu->children->contains(fn($c) => $c->route === $currentRoute));
                $gradClass   = $gradients[$gi % count($gradients)];
                $gi++;
                $panelId    = 'panel-menu-' . $menu->id;
                $directHref = (!$hasChildren && $menu->route && !$isLocked) ? $safeRoute($menu->route) : '#';
            @endphp

            @if($isVisible)
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
                        @if($isLocked)
                            <span class="dock-lock-badge"><i class="fas fa-lock"></i></span>
                        @endif
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


{{-- ════════════════════  PANELS  ════════════════════════════════ --}}
@foreach($menus as $menu)
    @php
        $isVisible   = $canViewMenu($menu);
        $hasChildren = $menu->children->count() > 0;
        $panelId     = 'panel-menu-' . $menu->id;
    @endphp

    @if($isVisible && $hasChildren)
        <div id="{{ $panelId }}" class="dock-panel" role="dialog" aria-label="{{ $menu->title }} panel">
            <div class="panel-titlebar">
                <div class="panel-traffic-lights">
                    <button class="tl-btn tl-close"    data-action="close"    aria-label="Close"></button>
                    <button class="tl-btn tl-minimize" data-action="minimize" aria-label="Minimise"></button>
                    <button class="tl-btn tl-maximize" data-action="maximize" aria-label="Fullscreen"></button>
                </div>
                <span class="panel-title">
                    <i class="{{ $menu->icon ?? 'fas fa-circle' }}" style="margin-right:5px;font-size:11px;opacity:.5;"></i>
                    {{ $menu->title }}
                </span>
            </div>
            <div class="panel-body">
                <nav class="panel-nav">
                    @foreach($menu->children as $child)
                        @php
                            $childVisible = $canViewMenu($child);
                            $childLocked  = (bool) $child->is_locked;
                            $childActive  = $currentRoute === $child->route;
                            $childHref    = ($child->route && !$childLocked) ? $safeRoute($child->route) : '#';
                        @endphp
                        @if($childVisible)
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


{{-- ════════════════════  PROFILE PANEL  ════════════════════════ --}}
<div id="panel-profile" class="dock-panel" role="dialog" aria-label="Profile panel">
    <div class="panel-titlebar">
        <div class="panel-traffic-lights">
            <button class="tl-btn tl-close"    data-action="close"></button>
            <button class="tl-btn tl-minimize" data-action="minimize"></button>
            <button class="tl-btn tl-maximize" data-action="maximize"></button>
        </div>
        <span class="panel-title">
            <i class="fas fa-user-circle" style="margin-right:5px;font-size:11px;opacity:.5;"></i>
            {{ $user?->name ?? 'Account' }}
        </span>
    </div>
    <div class="panel-body">

        <div class="panel-profile-card">
            <div class="panel-avatar">{{ strtoupper(substr($user?->name ?? 'G', 0, 1)) }}</div>
            <div class="panel-profile-info">
                <div class="panel-profile-name">{{ $user?->name ?? 'Guest' }}</div>
                <div class="panel-profile-email">{{ $user?->email ?? '' }}</div>
                @if($primaryDeptName)
                    <div class="small mt-1" style="color:rgba(255,255,255,0.45);font-size:11px;">
                        <i class="fas fa-building" style="margin-right:3px;"></i>{{ $primaryDeptName }}
                    </div>
                @endif
                @if($user)
                    <div class="mt-1">
                        @foreach($user->roles as $r)
                            <span class="badge badge-secondary" style="font-size:9px;margin-right:2px;">{{ $r->name }}</span>
                        @endforeach
                    </div>
                @endif
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
            @if($user && $user->hasRole('employee'))
                <a class="panel-nav-item" href="#">
                    <i class="fas fa-key panel-nav-icon"></i>
                    <span class="panel-nav-label">Request Access</span>
                </a>
            @endif
            <div class="panel-nav-divider"></div>
            <a class="panel-nav-item danger" href="#" data-toggle="modal" data-target="#logoutModal">
                <i class="fas fa-sign-out-alt panel-nav-icon"></i>
                <span class="panel-nav-label">Logout</span>
            </a>
        </nav>

        @include('partials.nav-mode-switcher', ['currentMode' => 'dock'])
    </div>
</div>

<div id="dock-backdrop" class="dock-backdrop"></div>
<div id="spa-loader" class="spa-loader"></div>
<svg id="genie-svg" xmlns="http://www.w3.org/2000/svg"
     style="position:fixed;inset:0;width:100%;height:100%;pointer-events:none;z-index:1997;overflow:visible;display:none;"
     aria-hidden="true">
    <defs>
        <clipPath id="genie-clip" clipPathUnits="userSpaceOnUse">
            <path id="genie-path" d=""></path>
        </clipPath>
    </defs>
</svg>