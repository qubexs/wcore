@extends('layouts.admin')

@section('title', 'Department Menu Access')
@php
use Illuminate\Support\Str;
$selectedDept = request('department_id', 'all');
@endphp

@section('main-content')
<style>
.dept-checkbox { transform: scale(1.2); cursor: pointer; }
</style>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Department Menu Access</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Assign Menus to Departments</h6>
            
            <form method="GET" class="form-inline">
                <label class="mr-2">Select Department:</label>
                <select name="department_id" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="all" {{ $selectedDept == 'all' ? 'selected' : '' }}>All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $selectedDept == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('settings.department-menus.update') }}">
                @csrf
                <input type="hidden" name="department_id" value="{{ $selectedDept }}">
                
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th style="width: 200px;">Menu</th>
                                @if($selectedDept == 'all')
                                    @foreach($departments as $dept)
                                        <th style="text-align: center; min-width: 80px;">
                                            <small>{{ Str::limit($dept->name, 8) }}</small>
                                        </th>
                                    @endforeach
                                @else
                                    @php $selectedDeptObj = $departments->firstWhere('id', $selectedDept); @endphp
                                    <th style="text-align: center; min-width: 80px;">
                                        <small>{{ $selectedDeptObj ? Str::limit($selectedDeptObj->name, 8) : 'Selected' }}</small>
                                    </th>
                                @endif
                                <th style="width: 80px; text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $displayDepts = $selectedDept == 'all' ? $departments : $departments->where('id', $selectedDept);
                            @endphp
                            @foreach($menus as $menu)
                                <tr>
                                    <td>
                                        <i class="{{ $menu->icon ?? 'fas fa-circle' }}"></i>
                                        {{ $menu->title }}
                                        @if($menu->children->count() > 0)
                                            <small class="text-muted">({{ $menu->children->count() }} submenus)</small>
                                        @endif
                                    </td>
                                    @foreach($displayDepts as $dept)
                                        <td style="text-align: center;">
                                            <input type="checkbox" class="dept-checkbox"
                                                   name="menus[{{ $menu->id }}][]" 
                                                   value="{{ $dept->id }}"
                                                   {{ in_array($dept->id, $existingMappings[$menu->id] ?? []) ? 'checked' : '' }}>
                                        </td>
                                    @endforeach
                                    <td style="text-align: center;">
                                        <button type="button" class="btn btn-sm btn-link toggle-all" data-menu-id="{{ $menu->id }}" title="Toggle all">
                                            <i class="fas fa-check-double"></i>
                                        </button>
                                    </td>
                                </tr>
                                @foreach($menu->children as $child)
                                    <tr style="background-color: #f8f9fa;">
                                        <td style="padding-left: 30px;">
                                            <i class="{{ $child->icon ?? 'fas fa-circle' }}"></i>
                                            ↳ {{ $child->title }}
                                        </td>
                                        @foreach($displayDepts as $dept)
                                            <td style="text-align: center;">
                                            <input type="checkbox" class="dept-checkbox"
                                                       name="menus[{{ $child->id }}][]" 
                                                       value="{{ $dept->id }}"
                                                       {{ in_array($dept->id, $existingMappings[$child->id] ?? []) ? 'checked' : '' }}>
                                            </td>
                                        @endforeach
                                        <td style="text-align: center;">
                                            <button type="button" class="btn btn-sm btn-link toggle-all" data-menu-id="{{ $child->id }}" title="Toggle all">
                                                <i class="fas fa-check-double"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.toggle-all').forEach(btn => {
    btn.addEventListener('click', function() {
        const menuId = this.dataset.menuId;
        const row = this.closest('tr');
        const checkboxes = row.querySelectorAll('input[type="checkbox"]');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        
        checkboxes.forEach(cb => {
            cb.checked = !allChecked;
        });
    });
});
</script>
@endsection
