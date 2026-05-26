@extends('layouts.admin')

@section('title', 'FileHosting Permissions')

@section('main-content')
<style>
.perm-checkbox { transform: scale(1.2); cursor: pointer; }
.perm-section { margin-bottom: 2rem; }
.perm-table { width: 100%; border-collapse: collapse; }
.perm-table th, .perm-table td { 
    padding: 8px 12px; 
    border: 1px solid #dee2e6; 
    text-align: center;
}
.perm-table th { background: #f8f9fa; font-weight: 600; }
.perm-table td:first-child { text-align: left; font-weight: 500; }
.perm-category { 
    background: #e9ecef; 
    font-weight: 700; 
    text-align: left !important;
}
</style>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">FileHosting Permissions</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('filehosting.settings.permissions.update') }}">
        @csrf

        {{-- Role Permissions --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Role Permissions</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="perm-table">
                        <thead>
                            <tr>
                                <th>Permission</th>
                                @foreach($roles as $role)
                                    <th>{{ $role->name }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $categories = [
                                'system' => ['index' => 'Access FileHosting'],
                                'folder' => [
                                    'create' => 'Create folders',
                                    'rename' => 'Rename folders', 
                                    'move' => 'Move folders',
                                    'delete' => 'Delete any folder',
                                    'delete_own' => 'Delete own folders',
                                    'view_all' => 'View all folders',
                                    'view_department' => 'View department folders',
                                ],
                                'file' => [
                                    'upload' => 'Upload files',
                                    'edit' => 'Edit any file',
                                    'edit_own' => 'Edit own files',
                                    'delete' => 'Delete any file',
                                    'delete_own' => 'Delete own files',
                                    'view' => 'View files',
                                    'download' => 'Download files',
                                    'move' => 'Move files',
                                    'share' => 'Share files',
                                ]
                            ];
                            @endphp
                            
                            @foreach($categories as $cat => $perms)
                                <tr>
                                    <td colspan="{{ count($roles) + 1 }}" class="perm-category">
                                        {{ strtoupper($cat) }}
                                    </td>
                                </tr>
                                @foreach($perms as $action => $label)
                                    <tr>
                                        <td>{{ $label }}</td>
                                        @foreach($roles as $role)
                                            @php $permName = 'filehosting.' . $cat . '.' . $action; @endphp
                                            <td>
                                                <input type="checkbox" class="perm-checkbox" 
                                                       name="role_permissions[{{ $role->name }}][]" 
                                                       value="{{ $permName }}"
                                                       {{ isset($configPerms[$role->name][$cat]) && in_array($action, $configPerms[$role->name][$cat]) ? 'checked' : '' }}>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Department Menu Access --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Department Menu Access</h6>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Select which departments can see each FileHosting menu.</p>
                
                <div class="table-responsive">
                    <table class="perm-table">
                        <thead>
                            <tr>
                                <th>Menu</th>
                                @foreach($departments as $dept)
                                    <th>{{ Str::limit($dept->name, 8) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($menus[''] ?? [] as $menu)
                                <tr>
                                    <td><i class="{{ $menu->icon }}"></i> {{ $menu->title }}</td>
                                    @foreach($departments as $dept)
                                        <td>
                                            <input type="checkbox" class="perm-checkbox"
                                                   name="menu_departments[{{ $menu->id }}][]"
                                                   value="{{ $dept->id }}"
                                                   {{ isset($menuDepts[$menu->id]) && in_array($dept->id, $menuDepts[$menu->id]) ? 'checked' : '' }}>
                                        </td>
                                    @endforeach
                                </tr>
                                @foreach($menus[$menu->id] ?? [] as $child)
                                    <tr style="background: #f8f9fa;">
                                        <td style="padding-left: 30px;">↳ {{ $child->title }}</td>
                                        @foreach($departments as $dept)
                                            <td>
                                                <input type="checkbox" class="perm-checkbox"
                                                       name="menu_departments[{{ $child->id }}][]"
                                                       value="{{ $dept->id }}"
                                                       {{ isset($menuDepts[$child->id]) && in_array($dept->id, $menuDepts[$child->id]) ? 'checked' : '' }}>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Save Permissions
        </button>
    </form>
</div>
@endsection
