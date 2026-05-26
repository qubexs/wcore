@php
/**
 * sidebar2.blade.php — Floating Liquid Glass Sidebar
 * ─────────────────────────────────────────────────────────────────────────────
 * Rendered when the user's nav_mode setting = "sidebar".
 * Features:
 * - Floating glass panel positioned under header
 * - Smooth accordion animations for parent menus
 * - Auto-closes other parent menus when one is opened
 * - Remembers open/close state using localStorage
 * - Responsive mobile support with backdrop
 */

use Illuminate\Support\Str;

$siteName = $settings['site_name'] ?? 'wCore HTPN';

$menus = \App\Models\Menu::whereNull('parent_id')
    ->where('is_active', 1)
    ->with(['children' => function ($q) {
        $q->where('is_active', 1)->orderBy('order');
    }])
    ->orderBy('order')
    ->get();

$currentRoute = Route::currentRouteName();
$user         = auth()->user();

// ── RBAC Setup ──────────────────────────────────────────────────────────────
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
@endphp

{{-- Floating Liquid Glass Sidebar --}}
<div id="sidebar2" class="sidebar2" role="navigation" aria-label="Main navigation">

    {{-- Brand Section --}}
    <div class="sb2-brand">
        <div class="sb2-brand-icon">
            <i class="fas fa-layer-group"></i>
        </div>
        <span class="sb2-brand-text">{{ $siteName }}</span>
    </div>

    {{-- Scrollable Navigation Body --}}
    <div class="sb2-body">
        @php $gradientIndex = 0; @endphp

        @foreach($menus as $menu)
            @php
                $canView     = $canViewMenu($menu);
                $isLocked    = (bool) $menu->is_locked;
                $hasChildren = $menu->children->count() > 0;
                $isActive    = ($currentRoute === $menu->route) || ($hasChildren && $menu->children->contains(fn($c) => $c->route === $currentRoute));
                $gradClass   = $gradients[$gradientIndex % count($gradients)];
                $gradientIndex++;
                $directHref  = (!$hasChildren && $menu->route && !$isLocked) ? $safeRoute($menu->route) : '#';
            @endphp

            @if($canView)
                @if($hasChildren)
                    {{-- Parent Menu with Children (Accordion) --}}
                    <div class="sb2-group {{ $isActive ? 'open' : '' }}">
                        <button class="sb2-group-trigger {{ $isActive ? 'active' : '' }} {{ $isLocked ? 'locked' : '' }}"
                                type="button"
                                @if(!$isLocked) aria-expanded="{{ $isActive ? 'true' : 'false' }}" @endif>
                            <span class="sb2-icon {{ $gradClass }}">
                                <i class="{{ $menu->icon ?? 'fas fa-circle' }}"></i>
                            </span>
                            <span class="sb2-label">{{ $menu->title }}</span>
                            @if($isLocked)
                                <i class="fas fa-lock sb2-lock"></i>
                            @else
                                <i class="fas fa-chevron-right sb2-chevron"></i>
                            @endif
                        </button>

                        {{-- Children Container --}}
                        <div class="sb2-children">
                            @foreach($menu->children as $child)
                                @php
                                    $childCanView = $canViewMenu($child);
                                    $childLocked  = (bool) $child->is_locked;
                                    $childActive  = $currentRoute === $child->route;
                                    $childHref    = ($child->route && !$childLocked) ? $safeRoute($child->route) : '#';
                                @endphp

                                @if($childCanView)
                                    <a class="sb2-child {{ $childActive ? 'active' : '' }} {{ $childLocked ? 'locked' : '' }}"
                                       href="{{ $childHref }}"
                                       @if($childLocked) aria-disabled="true" tabindex="-1" @endif>
                                        <span class="sb2-child-bullet"></span>
                                        <span class="sb2-child-label">{{ $child->title }}</span>
                                        @if($childLocked)
                                            <i class="fas fa-lock sb2-child-lock"></i>
                                        @elseif($childActive)
                                            <i class="fas fa-chevron-right sb2-child-arrow"></i>
                                        @endif
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @else
                    {{-- Direct Menu Item (No Children) --}}
                    <a class="sb2-item {{ $isActive ? 'active' : '' }} {{ $isLocked ? 'locked' : '' }}"
                       href="{{ $directHref }}"
                       @if($isLocked) aria-disabled="true" tabindex="-1" @endif>
                        <span class="sb2-icon {{ $gradClass }}">
                            <i class="{{ $menu->icon ?? 'fas fa-circle' }}"></i>
                        </span>
                        <span class="sb2-label">{{ $menu->title }}</span>
                        @if($isLocked)
                            <i class="fas fa-lock sb2-lock"></i>
                        @endif
                    </a>
                @endif
            @endif
        @endforeach
    </div>

    {{-- Footer: User Info + Nav Mode Switcher --}}
    <div class="sb2-footer">
        <div class="sb2-user">
            <div class="sb2-avatar gradient-pink">
                {{ strtoupper(substr($user?->name ?? 'G', 0, 1)) }}
            </div>
            <div class="sb2-user-info">
                <div class="sb2-user-name">{{ Str::limit($user?->name ?? 'Guest', 16) }}</div>
                <div class="sb2-user-email">{{ Str::limit($user?->email ?? 'guest@example.com', 22) }}</div>
            </div>
        </div>

        {{-- Nav Mode Switcher Widget --}}
        @include('partials.nav-mode-switcher', ['currentMode' => 'sidebar'])
    </div>
</div>

{{-- Mobile Toggle Button --}}
<button id="sb2-toggle" class="sb2-toggle-btn" aria-label="Toggle sidebar">
    <i class="fas fa-bars"></i>
</button>

{{-- Mobile Backdrop --}}
<div id="sb2-backdrop" class="sb2-backdrop"></div>

{{-- SPA Loading Bar --}}
<div id="spa-loader" class="spa-loader"></div>