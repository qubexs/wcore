{{--
╔══════════════════════════════════════════════════════════════════════════════╗
║  resources/views/layouts/app.blade.php                                      ║
║  ─────────────────────────────────────────────────────────────────────────  ║
║  Master layout.  Handles:                                                    ║
║    • nav_mode lookup from user_settings (dock vs sidebar)                   ║
║    • body class so CSS knows which layout is active                          ║
║    • conditional sidebar @include                                            ║
║    • #content SPA swap target                                                ║
║    • correct asset load order                                                ║
╚══════════════════════════════════════════════════════════════════════════════╝
--}}

@php
    /*
    ┌─────────────────────────────────────────────────────────────────────────┐
    │  STEP 1 — Resolve nav_mode for the authenticated user                   │
    │  Fallback = "dock" for guests / new users.                              │
    └─────────────────────────────────────────────────────────────────────────┘
    */
    $navMode = \App\Models\UserSetting::getValue(
        auth()->id() ?? 0,
        'nav_mode',
        'dock'           // ← default mode
    );
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'App'))</title>

    {{-- ── Fonts ─────────────────────────────────────────────────────────── --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- ── Icons ─────────────────────────────────────────────────────────── --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- ── Bootstrap (keep for existing components) ───────────────────────── --}}
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">

    {{-- ── Glass design system ────────────────────────────────────────────── --}}
    {{-- uiglass.css  contains all base glass styles + dock + sidebar2 styles --}}
    <link rel="stylesheet" href="{{ asset('css/uiglass.css') }}">

    @stack('styles')
</head>

{{--
┌─────────────────────────────────────────────────────────────────────────────┐
│  STEP 2 — Add body class so CSS knows which nav mode is active              │
│                                                                              │
│  .body-sidebar2  → hides the dock, enables sidebar2 layout                 │
│  (no class)      → dock mode, default layout                                │
└─────────────────────────────────────────────────────────────────────────────┘
--}}
<body class="@if($navMode === 'sidebar') body-sidebar2 @endif">

    {{-- Glass background blobs (decorative, behind everything) --}}
    <div class="glass-blob blob-1" aria-hidden="true"></div>
    <div class="glass-blob blob-2" aria-hidden="true"></div>
    <div class="glass-blob blob-3" aria-hidden="true"></div>

    <div id="wrapper">

        {{--
        ┌─────────────────────────────────────────────────────────────────────┐
        │  STEP 3 — Include the correct sidebar partial based on nav_mode     │
        │                                                                      │
        │  "dock"    → sidebar.blade.php   (macOS dock + floating panels)     │
        │  "sidebar" → sidebar2.blade.php  (traditional left sidebar)         │
        │                                                                      │
        │  Both partials include the nav-mode-switcher widget automatically.  │
        │  Both partials output #spa-loader and (dock only) #genie-svg.       │
        └─────────────────────────────────────────────────────────────────────┘
        --}}
        @if($navMode === 'sidebar')
            @include('layouts.sidebar2')
        @else
            @include('layouts.sidebar')
        @endif

        {{-- ── Content wrapper ────────────────────────────────────────────── --}}
        <div id="content-wrapper">

            {{-- Topbar (keep your existing topbar partial here) --}}
            @include('layouts.header')

            {{--
            ┌─────────────────────────────────────────────────────────────────┐
            │  STEP 4 — #content is the SPA swap target                       │
            │                                                                  │
            │  uiglass.js fetches pages and replaces innerHTML of #content.   │
            │  Every child view must be wrapped in <div id="content"> too     │
            │  so the parser can extract just the inner content.              │
            │                                                                  │
            │  In each child blade:                                            │
            │    @section('content')                                           │
            │        <div id="content" class="container-fluid">               │
            │            ... page content ...                                  │
            │        </div>                                                    │
            │    @endsection                                                   │
            └─────────────────────────────────────────────────────────────────┘
            --}}
            <div id="content" class="container-fluid py-4">
                @yield('content')
            </div>

        </div>{{-- /#content-wrapper --}}

    </div>{{-- /#wrapper --}}


    {{-- ── Logout modal (shared, used by profile panel logout link) ──────── --}}
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content glass-panel">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="logoutModalLabel">Ready to Leave?</h5>
                </div>
                <div class="modal-body">
                    Select "Logout" below if you are ready to end your current session.
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-glass" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-danger" href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>


    {{-- ══════════════════════════════════════════════════════════════════════
         SCRIPTS — load order matters
         1. jQuery          (required by uiglass.js)
         2. Bootstrap JS    (modals, tooltips)
         3. uiglass.js      (all dock / genie / sidebar2 / nav-mode / SPA logic)
         4. Page-level scripts via @stack
         ══════════════════════════════════════════════════════════════════════ --}}
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    {{--
    ┌─────────────────────────────────────────────────────────────────────────┐
    │  STEP 5 — Load uiglass.js                                               │
    │                                                                          │
    │  This single file now contains ALL UI logic:                            │
    │    • Dock panel open/close                                               │
    │    • Genie effect (SVG clip-path morph)                                 │
    │    • Dock magnification                                                  │
    │    • SPA navigation engine                                               │
    │    • Traffic light buttons & panel drag                                  │
    │    • Sidebar2 accordion & mobile toggle                                 │
    │    • Nav-mode switcher AJAX                                              │
    │    • Liquid glass background generator                                   │
    │                                                                          │
    │  No <script> blocks needed in any blade partial.                        │
    └─────────────────────────────────────────────────────────────────────────┘
    --}}
    <script src="{{ asset('js/uiglass.js') }}"></script>

    @stack('scripts')

</body>
</html>