@php
    /*
     * Resolve nav_mode for the current user.
     * Safely falls back to "dock" if:
     *   - user is a guest (not logged in)
     *   - user_settings table doesn't exist yet
     *   - UserSetting model doesn't exist yet
     * No fatal errors in any case.
     */
    $navMode = 'dock'; // safe default

    try {
        if (auth()->check() && class_exists(\App\Models\UserSetting::class)) {
            $navMode = \App\Models\UserSetting::getValue(
                auth()->id(),
                'nav_mode',
                'dock'
            );
        }
    } catch (\Throwable $e) {
        // Table not migrated yet, or model missing — stay on dock
        $navMode = 'dock';
    }

    $siteName = $settings['site_name'] ?? 'wCore HTPN';
    $metaTitle = $settings['meta_title'] ?? $siteName;
    $metaDescription = $settings['meta_description'] ?? '';
    $siteFavicon = $settings['site_favicon'] ?? '';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, viewport-fit=cover">
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="author" content="UTM HTPN">

    <!-- CSRF Token (used by uiglass.js for nav-mode AJAX) -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $metaTitle }}</title>

    <!-- Fonts -->
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 4 CSS -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Liquid Glass Theme CSS -->
    <link href="{{ asset('css/uiglass.css') }}" rel="stylesheet">
    <link href="{{ asset('css/gridstack.min.css') }}" rel="stylesheet">

    <!-- Favicon -->
    @if(!empty($siteFavicon))
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('storage/' . $siteFavicon) }}">
    @else
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/favicon-32x32.png') }}">
    @endif
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('img/favicon-16x16.png') }}">

    <!-- jQuery — load in <head> so Bootstrap modal works (your original order) -->
    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
    <!-- Bootstrap 4 JS Bundle -->
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <!-- jQuery Easing -->
    <script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('js/gridstack-all.js') }}"></script>
    <script src="{{ asset('js/online-status.js') }}"></script>
    <script src="{{ asset('js/sweetalert2@11.js') }}"></script>


    @stack('styles')
</head>

{{--
    body class controls layout:
      (none)         → dock mode  — .macos-dock shown, #content-wrapper full width
      body-sidebar2  → sidebar mode — #sidebar2 shown, dock hidden, content indented
--}}
<body id="page-top" class="{{ $navMode === 'sidebar' ? 'body-sidebar2' : '' }}">

    <!-- Glass Background Blobs -->
    <div class="glass-blob blob-1" aria-hidden="true"></div>
    <div class="glass-blob blob-2" aria-hidden="true"></div>
    <div class="glass-blob blob-3" aria-hidden="true"></div>

<!-- Topbar -->
@include('layouts.header')               
<!-- End of Topbar -->
    
    

<!-- Page Wrapper -->
    <div id="wrapper">

        {{-- ── Conditional sidebar ──────────────────────────────────────────
             "dock"    → layouts/sidebar.blade.php    (macOS dock + panels + genie)
             "sidebar" → layouts/sidebar2.blade.php   (traditional left sidebar)
             Both partials contain the nav-mode-switcher pill.
        ──────────────────────────────────────────────────────────────────── --}}
        @if($navMode === 'sidebar')
            @include('layouts.sidebar2')
            
        @else
            @include('layouts.sidebar')
            
        @endif


        {{-- Fixed footer - always visible at bottom left --}}
        @include('layouts.footer')

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content — #content is the SPA swap target -->
            <div id="content">

               
                <!-- Begin Page Content -->
                <div class="container-fluid">
                    @yield('main-content')
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content (#content) -->



        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ __('Ready to Leave?') }}</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-link" type="button" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <a class="btn btn-danger" href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        {{ __('Logout') }}
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- uiglass.js — all UI logic: dock, genie, sidebar2, nav-mode, SPA -->
      
    <script src="{{ asset('js/uiglass.js') }}?v={{ filemtime(public_path('js/uiglass.js')) }}"></script>
    <script src="{{ asset('js/cdn.alpinejs.js') }}" defer></script>

    <!-- Preview Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.6.0/mammoth.browser.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    @stack('scripts')

</body>
</html>