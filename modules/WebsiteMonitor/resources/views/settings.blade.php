@extends('layouts.admin')

@section('title', 'Website Monitor Settings')

@section('main-content')
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-cog text-primary"></i> Website Monitor Settings
        </h2>
        <a href="{{ route('websitemonitor.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('websitemonitor.settings.update') }}" method="POST">
                @csrf

                <h5 class="mb-3"><i class="fas fa-sliders-h"></i> Default Checking Options</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="default_check_interval">Default Check Interval (minutes)</label>
                            <input type="number" name="default_check_interval" id="default_check_interval"
                                   class="form-control" value="{{ old('default_check_interval', $settings['default_check_interval']) }}" min="1" max="1440">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="default_timeout">Default Timeout (seconds)</label>
                            <input type="number" name="default_timeout" id="default_timeout"
                                   class="form-control" value="{{ old('default_timeout', $settings['default_timeout']) }}" min="1" max="120">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="retain_log_days">Retain Logs (days)</label>
                            <input type="number" name="retain_log_days" id="retain_log_days"
                                   class="form-control" value="{{ old('retain_log_days', $settings['retain_log_days']) }}" min="1" max="365">
                        </div>
                    </div>
                </div>

                <hr>
                <h5 class="mb-3"><i class="fas fa-bell"></i> Alert Configuration</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="default_alert_methods">Default Alert Method</label>
                            <select name="default_alert_methods" id="default_alert_methods" class="form-control">
                                <option value="message" {{ $settings['default_alert_methods'] === 'message' ? 'selected' : '' }}>Internal Message</option>
                                <option value="email" {{ $settings['default_alert_methods'] === 'email' ? 'selected' : '' }}>Email</option>
                                <option value="message,email" {{ $settings['default_alert_methods'] === 'message,email' ? 'selected' : '' }}>Message + Email</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="notify_admin_ids">Notify Admin User IDs</label>
                            <input type="text" name="notify_admin_ids" id="notify_admin_ids"
                                   class="form-control" value="{{ old('notify_admin_ids', $settings['notify_admin_ids']) }}"
                                   placeholder="e.g. 1,2,3">
                            <small class="text-muted">Comma-separated user IDs to always notify when a target goes down</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="auto_resolve_after">Auto-Resolve After (minutes)</label>
                            <input type="number" name="auto_resolve_after" id="auto_resolve_after"
                                   class="form-control" value="{{ old('auto_resolve_after', $settings['auto_resolve_after']) }}" min="0" max="10080">
                            <small class="text-muted">Set to 0 to disable auto-resolve</small>
                        </div>
                    </div>
                </div>

                <div class="form-group form-check">
                    <input type="checkbox" name="alert_global_enabled" id="alert_global_enabled" class="form-check-input"
                           value="1" {{ $settings['alert_global_enabled'] == '1' ? 'checked' : '' }}>
                    <label class="form-check-label" for="alert_global_enabled">Enable alerts globally</label>
                </div>

                <hr>
                <h5 class="mb-3"><i class="fas fa-database"></i> Data Management</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="mail_enabled">Email Transport</label>
                            <select name="mail_enabled" id="mail_enabled" class="form-control">
                                <option value="0" {{ $settings['mail_enabled'] == '0' ? 'selected' : '' }}>Disabled</option>
                                <option value="1" {{ $settings['mail_enabled'] == '1' ? 'selected' : '' }}>Enabled</option>
                            </select>
                            <small class="text-muted">Requires Laravel mail configuration in .env</small>
                        </div>
                    </div>
                </div>

                <hr>
                <h5 class="mb-3"><i class="fas fa-terminal"></i> Cronjob Setup</h5>
                <div class="alert alert-info">
                    <strong>For automatic scheduled checks, add this to your crontab:</strong>
                    <pre class="bg-dark text-light p-3 rounded mt-2 mb-0"><code>* * * * * cd {{ base_path() }} && php artisan monitor:check >> /dev/null 2>&1</code></pre>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Settings
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
