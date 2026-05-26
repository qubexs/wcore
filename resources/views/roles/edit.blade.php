@extends('layouts.admin')

@section('main-content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Edit Role: <strong>{{ $role->name }}</strong></h2>
                <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
            <p class="text-muted">Manage permissions and department access for this role</p>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-exclamation-circle"></i> Validation Errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('roles.update', $role->id) }}" method="POST" class="needs-validation" novalidate>
        @csrf
        @method('PUT')

        {{-- Role Basic Info --}}
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Role Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Role Name <span class="text-danger">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        id="name" 
                        class="form-control @error('name') is-invalid @enderror" 
                        value="{{ old('name', $role->name) }}"
                        required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea 
                        name="description" 
                        id="description" 
                        class="form-control" 
                        rows="3">{{ old('description', $role->description) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Permissions (Layer 1) --}}
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-lock"></i> Permissions (Layer 1)
                </h5>
                <small>Select what actions users with this role can perform</small>
            </div>
            <div class="card-body">
                @forelse($groupedPermissions as $module => $modulePermissions)
                    @php 
                        $slug = trim(\Illuminate\Support\Str::slug($module ?? 'uncategorized'));
                    @endphp
                    <div class="permission-module mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="form-check form-switch me-2">
                                <input 
                                    type="checkbox" 
                                    class="form-check-input master-toggle" 
                                    data-module="{{ $slug }}"
                                    id="module_{{ $slug }}">
                                <label class="form-check-label fw-bold" for="module_{{ $slug }}">
                                    {{ $module ?? 'Uncategorized' }}
                                    <span class="badge bg-info">{{ $modulePermissions->count() }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="row ms-3">
                            @foreach($modulePermissions as $permission)
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-check">
                                        <input 
                                            type="checkbox" 
                                            class="form-check-input child-checkbox" 
                                            name="permissions[]" 
                                            value="{{ $permission->id }}"
                                            data-module="{{ $slug }}"
                                            id="permission_{{ $permission->id }}"
                                            @if(in_array($permission->id, $rolePermissionIds ?? [])) checked @endif>
                                        <label class="form-check-label" for="permission_{{ $permission->id }}">
                                            {{ $permission->name }}
                                            @if($permission->description)
                                                <br><small class="text-muted">{{ $permission->description }}</small>
                                            @endif
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <hr>
                    </div>
                @empty
                    <div class="alert alert-info">No permissions available.</div>
                @endforelse

                {{-- Quick Actions --}}
                <div class="mt-3">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="select-all-perms">
                        <i class="fas fa-check-double"></i> Select All
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="deselect-all-perms">
                        <i class="fas fa-times"></i> Deselect All
                    </button>
                </div>
            </div>
        </div>

        {{-- Department Menu Access (Layer 2) --}}
        @if($departments && $departments->count() > 0)
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="fas fa-sitemap"></i> Department Menu Access (Layer 2)
                </h5>
                <small>Choose which departments this role can access menus from</small>
            </div>
            <div class="card-body">
                @if($departments->isEmpty())
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> No departments available. 
                        Create departments first to configure menu access.
                    </div>
                @else
                    <div class="row">
                        @foreach($departments as $department)
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input 
                                        type="checkbox" 
                                        class="form-check-input dept-checkbox" 
                                        name="departments[]" 
                                        value="{{ $department->id }}"
                                        id="dept_{{ $department->id }}"
                                        @if(in_array($department->id, old('departments', $roleDepartments->pluck('id')->toArray() ?? []))) checked @endif>
                                    <label class="form-check-label" for="dept_{{ $department->id }}">
                                        <strong>{{ $department->name }}</strong>
                                        @if($department->description)
                                            <br><small class="text-muted">{{ $department->description }}</small>
                                        @endif
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Quick Actions --}}
                    <div class="mt-3">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="select-all-depts">
                            <i class="fas fa-check-double"></i> Select All Departments
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" id="deselect-all-depts">
                            <i class="fas fa-times"></i> Deselect All Departments
                        </button>
                    </div>

                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle"></i>
                        <strong>What is this?</strong> Layer 2 controls which departments' menus are accessible. 
                        Combined with Layer 1 permissions, this provides complete access control.
                    </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Summary --}}
        <div class="card mb-4 bg-light">
            <div class="card-body">
                <h6 class="card-title">Summary</h6>
                <p class="mb-1">
                    <i class="fas fa-lock"></i> 
                    <strong>Permissions:</strong> 
                    <span id="perm-count">0</span> selected
                </p>
                <p class="mb-0">
                    <i class="fas fa-sitemap"></i> 
                    <strong>Departments:</strong> 
                    <span id="dept-count">0</span> selected
                </p>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="row mb-4">
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Update Role
                </button>
                <a href="{{ route('roles.index') }}" class="btn btn-secondary btn-lg">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </div>
    </form>
</div>

{{-- JavaScript --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ========================================
    // Master Toggle for Permissions
    // ========================================
    document.querySelectorAll('.master-toggle').forEach(masterToggle => {
        const moduleSlug = masterToggle.dataset.module;
        const children = document.querySelectorAll(`.child-checkbox[data-module="${moduleSlug}"]`);

        // Initialize master toggle based on children
        updateMasterToggle();

        function updateMasterToggle() {
            masterToggle.checked = Array.from(children).every(c => c.checked);
        }

        // When master toggle changes
        masterToggle.addEventListener('change', function() {
            children.forEach(child => {
                child.checked = this.checked;
            });
            updateCounter();
        });

        // When any child checkbox changes
        children.forEach(child => {
            child.addEventListener('change', function() {
                updateMasterToggle();
                updateCounter();
            });
        });
    });

    // ========================================
    // Select/Deselect All for Permissions
    // ========================================
    document.getElementById('select-all-perms')?.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('.child-checkbox').forEach(cb => cb.checked = true);
        document.querySelectorAll('.master-toggle').forEach(cb => cb.checked = true);
        updateCounter();
    });

    document.getElementById('deselect-all-perms')?.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('.child-checkbox').forEach(cb => cb.checked = false);
        document.querySelectorAll('.master-toggle').forEach(cb => cb.checked = false);
        updateCounter();
    });

    // ========================================
    // Select/Deselect All for Departments
    // ========================================
    document.getElementById('select-all-depts')?.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('.dept-checkbox').forEach(cb => cb.checked = true);
        updateCounter();
    });

    document.getElementById('deselect-all-depts')?.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('.dept-checkbox').forEach(cb => cb.checked = false);
        updateCounter();
    });

    // ========================================
    // Update Counter Display
    // ========================================
    function updateCounter() {
        const permCount = document.querySelectorAll('.child-checkbox:checked').length;
        const deptCount = document.querySelectorAll('.dept-checkbox:checked').length;
        document.getElementById('perm-count').textContent = permCount;
        document.getElementById('dept-count').textContent = deptCount;
    }

    // Initial count
    updateCounter();

    // ========================================
    // Bootstrap Validation
    // ========================================
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
});
</script>

<style>
    .permission-module {
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 5px;
        border-left: 4px solid #0d6efd;
    }
    
    .form-check-label {
        cursor: pointer;
        padding-top: 2px;
    }
    
    .badge {
        font-size: 0.75rem;
        margin-left: 5px;
    }
    
    .dept-checkbox:checked + label {
        color: #0d6efd;
        font-weight: 500;
    }
    
    .card-header {
        border-bottom: 2px solid rgba(255, 255, 255, 0.1);
    }
    
    .card-header small {
        display: block;
        opacity: 0.9;
        margin-top: 3px;
    }
</style>
@endsection
