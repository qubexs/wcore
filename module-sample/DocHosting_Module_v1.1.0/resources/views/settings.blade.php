@extends('layouts.admin')

@section('title', 'Settings - Document Hosting')

@section('main-content')
<div class="container py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Settings</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="#general" class="list-group-item list-group-item-action active" data-bs-toggle="tab">
                        <i class="fas fa-cog me-2"></i>General
                    </a>
                    <a href="#permissions" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                        <i class="fas fa-shield-alt me-2"></i>Permissions
                    </a>
                    <a href="#storage" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                        <i class="fas fa-hdd me-2"></i>Storage
                    </a>
                    <a href="#notifications" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                        <i class="fas fa-bell me-2"></i>Notifications
                    </a>
                    <a href="#security" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                        <i class="fas fa-lock me-2"></i>Security
                    </a>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="col-md-9">
            <form action="{{ route('filehosting.settings.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="tab-content">
                    <!-- General Settings -->
                    <div class="tab-pane fade show active" id="general">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">General Settings</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Module Name</label>
                                    <input type="text" name="module_name" class="form-control" value="{{ $settings['module_name'] ?? 'Document Hosting' }}">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Default Visibility</label>
                                    <select name="default_visibility" class="form-select">
                                        <option value="private" {{ ($settings['default_visibility'] ?? '') == 'private' ? 'selected' : '' }}>Private</option>
                                        <option value="public" {{ ($settings['default_visibility'] ?? '') == 'public' ? 'selected' : '' }}>Public</option>
                                        <option value="restricted" {{ ($settings['default_visibility'] ?? '') == 'restricted' ? 'selected' : '' }}>Restricted</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Items Per Page</label>
                                    <input type="number" name="items_per_page" class="form-control" value="{{ $settings['items_per_page'] ?? 20 }}" min="10" max="100">
                                </div>

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="enable_thumbnails" value="1" {{ ($settings['enable_thumbnails'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label">Enable Thumbnail Generation</label>
                                </div>

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="enable_versioning" value="1" {{ ($settings['enable_versioning'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label">Enable File Versioning</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Permissions Settings -->
                    <div class="tab-pane fade" id="permissions">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Role Permissions</h5>
                            </div>
                            <div class="card-body">
                                @foreach($roles as $role)
                                <div class="mb-4">
                                    <h6 class="border-bottom pb-2">{{ $role->name }}</h6>
                                    <div class="row">
                                        @foreach($permissionTypes as $type => $label)
                                        <div class="col-md-4 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                    name="permissions[{{ $role->id }}][]" 
                                                    value="{{ $type }}"
                                                    {{ isset($rolePermissions[$role->id]) && in_array($type, $rolePermissions[$role->id]) ? 'checked' : '' }}>
                                                <label class="form-check-label">{{ $label }}</label>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Storage Settings -->
                    <div class="tab-pane fade" id="storage">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Storage Configuration</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Storage Disk</label>
                                    <select name="storage_disk" class="form-select">
                                        <option value="local" {{ ($settings['storage_disk'] ?? 'local') == 'local' ? 'selected' : '' }}>Local Storage</option>
                                        <option value="s3" {{ ($settings['storage_disk'] ?? '') == 's3' ? 'selected' : '' }}>Amazon S3</option>
                                        <option value="wasabi" {{ ($settings['storage_disk'] ?? '') == 'wasabi' ? 'selected' : '' }}>Wasabi</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Max File Size (MB)</label>
                                    <input type="number" name="max_file_size" class="form-control" value="{{ $settings['max_file_size'] ?? 100 }}" min="1" max="500">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Allowed File Extensions</label>
                                    <input type="text" name="allowed_extensions" class="form-control" value="{{ $settings['allowed_extensions'] ?? 'pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,png,gif,zip' }}">
                                    <div class="form-text">Comma separated list</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Max Folder Depth</label>
                                    <input type="number" name="max_folder_depth" class="form-control" value="{{ $settings['max_folder_depth'] ?? 10 }}" min="1" max="50">
                                </div>

                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle me-2"></i>Storage Usage</h6>
                                    <div class="progress mb-2">
                                        <div class="progress-bar" role="progressbar" style="width: {{ $storageStats['percentage'] ?? 0 }}%">
                                            {{ $storageStats['percentage'] ?? 0 }}%
                                        </div>
                                    </div>
                                    <small>{{ $storageStats['used'] ?? '0 GB' }} of {{ $storageStats['total'] ?? 'Unlimited' }} used</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notifications -->
                    <div class="tab-pane fade" id="notifications">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Notification Settings</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="notify_upload" value="1" {{ ($settings['notify_upload'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label">Notify on file upload</label>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="notify_download" value="1" {{ ($settings['notify_download'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label">Notify on file download</label>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="notify_share" value="1" {{ ($settings['notify_share'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label">Notify when file is shared</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security -->
                    <div class="tab-pane fade" id="security">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Security Settings</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="require_password" value="1" {{ ($settings['require_password'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label">Require password for public files</label>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Auto-delete files after (days)</label>
                                    <input type="number" name="auto_delete_days" class="form-control" value="{{ $settings['auto_delete_days'] ?? 0 }}" min="0">
                                    <div class="form-text">0 = never auto-delete</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Allowed IP Addresses (optional)</label>
                                    <textarea name="allowed_ips" class="form-control" rows="3" placeholder="192.168.1.1, 10.0.0.0/24">{{ $settings['allowed_ips'] ?? '' }}</textarea>
                                    <div class="form-text">Leave empty to allow all IPs</div>
                                </div>

                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Changes to security settings take effect immediately.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="mt-4 text-end">
                    <button type="reset" class="btn btn-outline-secondary me-2">Reset</button>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Tab persistence
document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
    tab.addEventListener('shown.bs.tab', function (e) {
        localStorage.setItem('filehosting_settings_tab', e.target.getAttribute('href'));
    });
});

// Restore active tab
const activeTab = localStorage.getItem('filehosting_settings_tab');
if (activeTab) {
    const tab = document.querySelector(`[href="${activeTab}"]`);
    if (tab) {
        tab.click();
    }
}
</script>
@endpush
@endsection