@extends('layouts.admin')

@section('title', 'Create Role')

@section('main-content')
<div class="fh-container" style="padding-top: 3.5rem;">

    <div class="fh-header">
        <div class="fh-header__left">
            <a href="{{ route('roles.index') }}" class="fh-back-link">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/></svg>
                Back
            </a>
            <h1 class="fh-page-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Create New Role
            </h1>
        </div>
    </div>

    @if($errors->any())
        <div class="fh-toast fh-toast--error">
            <strong>Validation Errors:</strong>
            <ul class="mb-0 mt-2" style="padding-left: 1rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('roles.store') }}" method="POST" class="needs-validation" novalidate>
        @csrf

        <div class="fh-settings-grid">
            <div class="fh-setting-row" style="display: block;">
                <div style="margin-bottom: 0.5rem;">
                    <label style="font-weight: 600; color: #374151;">Role Name <span style="color: #ef4444;">*</span></label>
                </div>
                <input 
                    type="text" 
                    name="name" 
                    id="name" 
                    class="fh-setting-row__input" 
                    value="{{ old('name') }}"
                    placeholder="e.g., Manager, Supervisor, Reviewer"
                    required
                    style="width: 100%;">
                @error('name')
                    <div style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="fh-setting-row" style="display: block;">
                <div style="margin-bottom: 0.5rem;">
                    <label style="font-weight: 600; color: #374151;">Description</label>
                </div>
                <textarea 
                    name="description" 
                    id="description" 
                    class="fh-setting-row__input" 
                    rows="3"
                    placeholder="Describe the purpose of this role"
                    style="width: 100%;">{{ old('description') }}</textarea>
                <small style="color: #9ca3af; font-size: 0.75rem;">Optional: Help explain what this role is for</small>
            </div>
        </div>

        <div style="margin-top: 1.5rem;">
            <h3 style="font-size: 1.125rem; font-weight: 600; color: #111827; margin-bottom: 1rem;">
                <svg viewBox="0 0 20 20" fill="currentColor" style="width: 1.25rem; height: 1.25rem; margin-right: 0.5rem; vertical-align: middle;"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                Permissions (Layer 1)
            </h3>
            <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 1rem;">Select what actions users with this role can perform</p>
        </div>

        <div class="fh-tabs" id="permissionTabs">
            @php $firstModule = true; @endphp
            @forelse($groupedPermissions as $module => $modulePermissions)
                @php 
                    $slug = trim(\Illuminate\Support\Str::slug($module ?? 'uncategorized'));
                @endphp
                <button type="button" class="fh-tab {{ $firstModule ? 'fh-tab--active' : '' }}" data-slug="{{ $slug }}" onclick="showPermissionSection('{{ $slug }}', this)">
                    {{ $module ?? 'Uncategorized' }} ({{ $modulePermissions->count() }})
                </button>
                @php $firstModule = false; @endphp
            @empty
            @endforelse
        </div>

        @php $firstSection = true; @endphp
        @forelse($groupedPermissions as $module => $modulePermissions)
            @php 
                $slug = trim(\Illuminate\Support\Str::slug($module ?? 'uncategorized'));
                $sectionStyle = $firstSection ? '' : 'display: none;';
            @endphp
            <div id="section_{{ $slug }}" class="permission-section" style="{{ $sectionStyle }}">
                <div class="fh-settings-grid">
                    <div class="fh-setting-row" style="justify-content: flex-start; gap: 1rem;">
                        <div style="margin: 0;">
                            <input 
                                type="checkbox" 
                                class="master-toggle" 
                                data-module="{{ $slug }}"
                                id="module_{{ $slug }}"
                                style="width: 1.5rem; height: 1.5rem; cursor: pointer;">
                            <label class="form-check-label fw-bold" for="module_{{ $slug }}" style="margin-left: 0.5rem;">
                                Select All {{ $module ?? 'Uncategorized' }}
                            </label>
                            <span class="fh-badge fh-badge--blue" style="margin-left: 0.5rem;">{{ $modulePermissions->count() }}</span>
                        </div>
                    </div>

                    <div class="fh-setting-row" style="display: block;">
                        @php
                            $groupedPermissions = $modulePermissions->groupBy(function($p) {
                                $parts = explode('.', $p->name);
                                return count($parts) > 1 ? $parts[0] : 'general';
                            });
                        @endphp
                        
                        @foreach($groupedPermissions as $groupName => $groupPermissions)
                            <div style="margin-bottom: 1.5rem;">
                                <div style="font-weight: 600; color: #374151; margin-bottom: 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb;">
                                    {{ ucfirst($groupName) }}
                                </div>
                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 0.75rem;">
                                    @foreach($groupPermissions as $permission)
                                        <div style="padding: 0.5rem; background: #f9fafb; border-radius: 0.375rem; border: 1px solid #e5e7eb;">
                                            <input 
                                                type="checkbox" 
                                                class="child-checkbox" 
                                                name="permissions[]" 
                                                value="{{ $permission->id }}"
                                                data-module="{{ $slug }}"
                                                id="permission_{{ $permission->id }}"
                                                @if(in_array($permission->id, old('permissions', []))) checked @endif
                                                style="margin-top: 0.125rem; cursor: pointer;">
                                            <label for="permission_{{ $permission->id }}" style="margin-left: 0.5rem; cursor: pointer;">
                                                {{ $permission->name }}
                                                @if($permission->description)
                                                    <br><small style="color: #9ca3af;">{{ $permission->description }}</small>
                                                @endif
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @php $firstSection = false; @endphp
        @empty
            <div class="fh-settings-grid">
                <div class="fh-setting-row" style="text-align: center; justify-content: center; padding: 2rem;">
                    <p style="color: #6b7280;">No permissions available. Please create some first.</p>
                </div>
            </div>
        @endforelse

        <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
            <button type="submit" class="fh-btn" style="background: #2563eb; color: #fff; border: none;">
                <svg viewBox="0 0 20 20" fill="currentColor" style="width: 1rem; height: 1rem; margin-right: 0.25rem;"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                Create Role
            </button>
            <a href="{{ route('roles.index') }}" class="fh-btn fh-btn--ghost">
                Cancel
            </a>
        </div>
    </form>

</div>

@push('styles')
@include('filehosting::_partials.styles')
@endpush

<script>
function showPermissionSection(slug, btn) {
    console.log('showPermissionSection called:', slug);
    
    var sections = document.querySelectorAll('.permission-section');
    sections.forEach(function(section) {
        section.style.display = 'none';
    });
    
    var selectedSection = document.getElementById('section_' + slug);
    console.log('Selected section:', selectedSection);
    if (selectedSection) {
        selectedSection.style.display = 'block';
    }
    
    var tabs = document.querySelectorAll('.fh-tabs .fh-tab');
    tabs.forEach(function(tab) {
        tab.classList.remove('fh-tab--active');
    });
    if (btn) {
        btn.classList.add('fh-tab--active');
    }
}

// Initialize immediately
(function() {
    var toggles = document.querySelectorAll('.master-toggle');
    console.log('Found master toggles:', toggles.length);
    toggles.forEach(function(masterToggle) {
        var moduleSlug = masterToggle.getAttribute('data-module');
        console.log('Module slug:', moduleSlug);
        
        var children = document.querySelectorAll('.child-checkbox[data-module="' + moduleSlug + '"]');
        console.log('Children for', moduleSlug, ':', children.length);

        masterToggle.onchange = function() {
            console.log('Master toggle changed:', moduleSlug, this.checked);
            children.forEach(function(child) {
                child.checked = masterToggle.checked;
            });
        };

        children.forEach(function(child) {
            child.onchange = function() {
                var allChecked = Array.from(children).every(function(c) {
                    return c.checked;
                });
                masterToggle.checked = allChecked;
            };
        });
    });

    var forms = document.querySelectorAll('.needs-validation');
    forms.forEach(function(form) {
        form.onsubmit = function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        };
    });
})();
</script>

@endsection