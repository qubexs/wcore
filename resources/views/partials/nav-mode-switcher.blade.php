{{--
    partials/nav-mode-switcher.blade.php
    ─────────────────────────────────────────────────────────────────────────────
    A small pill widget that lets the user switch between Dock and Sidebar.
    Stored in resources/views/partials/nav-mode-switcher.blade.php.

    Props (passed via @include):
      $currentMode  — "dock" | "sidebar"   (current active mode)
--}}

@php $currentMode = $currentMode ?? 'dock'; @endphp

<div class="nav-mode-switcher" data-current="{{ $currentMode }}" role="group" aria-label="Navigation style">
    <span class="nav-mode-label">Nav style</span>
    <div class="nav-mode-pills">
        <button class="nav-mode-btn {{ $currentMode === 'dock' ? 'active' : '' }}"
                data-mode="dock"
                title="macOS Dock (bottom bar)"
                aria-pressed="{{ $currentMode === 'dock' ? 'true' : 'false' }}">
            <i class="fas fa-border-bottom"></i>
            <span>Dock</span>
        </button>
        <button class="nav-mode-btn {{ $currentMode === 'sidebar' ? 'active' : '' }}"
                data-mode="sidebar"
                title="Traditional left sidebar"
                aria-pressed="{{ $currentMode === 'sidebar' ? 'true' : 'false' }}">
            <i class="fas fa-bars"></i>
            <span>Sidebar</span>
        </button>
    </div>
</div>