@php
use Illuminate\Support\Str;

$menus = \App\Models\Menu::whereNull('parent_id')
    ->where('is_active', 1)
    ->with(['children' => function($q) {
        $q->where('is_active', 1)->orderBy('order');
    }])
    ->orderBy('order')
    ->get();

$currentRoute = Route::currentRouteName();
$user = auth()->user();
@endphp

<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('home') }}">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-laugh-wink"></i>
        </div>
        <div class="sidebar-brand-text mx-3">wCore HTPN</div>
    </a>

    @foreach($menus as $menu)
        @php
            $canView = $menu->is_active && (!$menu->permission || ($user && $user->can($menu->permission)));
            $isDisabled = $menu->is_locked;
        @endphp

        @if($canView)
            @if($menu->children->count())
                @php
                    $isParentActive = $menu->children->contains(fn($child) => $child->route === $currentRoute);
                @endphp

                <li class="nav-item">
                    <a class="nav-link collapsed {{ $isDisabled ? 'disabled' : '' }}" 
                       href="#" 
                       data-toggle="collapse" 
                       data-target="#menu-{{ $menu->id }}">
                        <i class="{{ $menu->icon }}"></i> 
                        <span>{{ $menu->title }}</span>
                        @if($isDisabled)<i class="fas fa-lock ml-1"></i>@endif
                    </a>

                    <div id="menu-{{ $menu->id }}" class="collapse {{ $isParentActive ? 'show' : '' }}">
                        <div class="bg-white py-2 collapse-inner rounded">
                            @foreach($menu->children as $child)
                                @php
                                    $childCanView = $child->is_active && (!$child->permission || ($user && $user->can($child->permission)));
                                    $childDisabled = $child->is_locked;
                                    $isChildActive = $currentRoute === $child->route;
                                @endphp
                                @if($childCanView)
                                    <a class="collapse-item {{ $childDisabled ? 'disabled' : '' }} {{ $isChildActive ? 'active' : '' }}" 
                                       href="{{ $child->route ? route($child->route) : '#' }}">
                                        {{ $child->title }}
                                        @if($childDisabled)<i class="fas fa-lock ml-1"></i>@endif
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </li>
            @else
                <li class="nav-item">
                    <a class="nav-link {{ $isDisabled ? 'disabled' : '' }} {{ $currentRoute === $menu->route ? 'active' : '' }}" 
                       href="{{ $menu->route ? route($menu->route) : '#' }}">
                        <i class="{{ $menu->icon }}"></i> 
                        <span>{{ $menu->title }}</span>
                        @if($isDisabled)<i class="fas fa-lock ml-1"></i>@endif
                    </a>
                </li>
            @endif
        @endif
    @endforeach

</ul>

<!-- Smooth Sidebar Collapse Script -->
<script>
$(document).ready(function () {

    $('.nav-link.collapsed').on('click', function (e) {
        e.preventDefault();

        let targetSelector = $(this).data('target');
        let $targetMenu = $(targetSelector);

        // If already open → close it
        if ($targetMenu.hasClass('show')) {
            $targetMenu.collapse('hide');
            return;
        }

        // Close other open menus
        $('.collapse.show').not($targetMenu).collapse('hide');

        // Open clicked menu
        $targetMenu.collapse('show');
    });

});
</script>