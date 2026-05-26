<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ url('/home') }}">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-laugh-wink"></i>
        </div>
        <div class="sidebar-brand-text mx-3">wCore HTPN</div>
    </a>

    <hr class="sidebar-divider my-0">

    <!-- Dashboard (all authenticated users) -->
    <li class="nav-item {{ Nav::isRoute('home') }}">
        <a class="nav-link" href="{{ route('home') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <!-- Admin / Settings Section -->
    @php
        $user = auth()->user();
    @endphp

    <!-- Settings menu (only for SuperAdmin or if permission exists) -->
   
@auth
    @can('manage settings')
    <li class="nav-item {{ Nav::isRoute('settings.index') }}">
        <a class="nav-link" href="{{ route('settings.index') }}">
            <i class="fas fa-cogs"></i>
            <span>Settings</span>
        </a>
    </li>
    @endcan
@endauth



    <!-- Module Management -->
    @can('manage settings')
        <li class="nav-item">
            <a class="nav-link" href="{{ route('modules.index') }}">
                <i class="fas fa-th-large"></i>
                <span>Module Management</span>
            </a>
        </li>
    @endcan

    <!-- Role Management -->
    @can('manage settings')
        <li class="nav-item">
            <a class="nav-link" href="{{ route('roles.index') }}">
                <i class="fas fa-user-shield"></i>
                <span>Role Management</span>
            </a>
        </li>
    @endcan

    <!-- User Management -->
    @can('manage settings')
        <li class="nav-item">
            <a class="nav-link" href="{{ route('users.index') }}">
                <i class="fas fa-users"></i>
                <span>User Management</span>
            </a>
        </li>
    @endcan

    <hr class="sidebar-divider">

    <!-- Settings Heading -->
    <div class="sidebar-heading">
        Profile & Info
    </div>

    <!-- Profile -->
    <li class="nav-item {{ Nav::isRoute('profile') }}">
        <a class="nav-link" href="{{ route('profile') }}">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
    </li>

    <!-- About -->
    <li class="nav-item {{ Nav::isRoute('about') }}">
        <a class="nav-link" href="{{ route('about') }}">
            <i class="fas fa-info-circle"></i>
            <span>About</span>
        </a>
    </li>

    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
<!-- End of Sidebar -->
