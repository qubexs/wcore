@extends('layouts.admin')

@section('title', 'Edit Monitor Target')

@section('main-content')
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-edit text-primary"></i> Edit Monitor Target
        </h2>
        <a href="{{ route('websitemonitor.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('websitemonitor.update', $target->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Target Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $target->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="url">URL <span class="text-danger">*</span></label>
                    <input type="url" name="url" id="url" class="form-control @error('url') is-invalid @enderror"
                           value="{{ old('url', $target->url) }}" required>
                    @error('url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="method">HTTP Method</label>
                            <select name="method" id="method" class="form-control">
                                <option value="GET" {{ old('method', $target->method) === 'GET' ? 'selected' : '' }}>GET</option>
                                <option value="HEAD" {{ old('method', $target->method) === 'HEAD' ? 'selected' : '' }}>HEAD</option>
                                <option value="POST" {{ old('method', $target->method) === 'POST' ? 'selected' : '' }}>POST</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="check_interval">Check Interval (minutes)</label>
                            <input type="number" name="check_interval" id="check_interval"
                                   class="form-control @error('check_interval') is-invalid @enderror"
                                   value="{{ old('check_interval', $target->check_interval) }}" min="1" max="1440">
                            @error('check_interval')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="timeout">Timeout (seconds)</label>
                            <input type="number" name="timeout" id="timeout"
                                   class="form-control @error('timeout') is-invalid @enderror"
                                   value="{{ old('timeout', $target->timeout) }}" min="1" max="120">
                            @error('timeout')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="expected_status">Expected HTTP Status</label>
                            <input type="number" name="expected_status" id="expected_status"
                                   class="form-control @error('expected_status') is-invalid @enderror"
                                   value="{{ old('expected_status', $target->expected_status) }}" min="100" max="599">
                            @error('expected_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="check_string">Check String (optional) <span class="text-muted">- text that must exist in the response body</span></label>
                    <input type="text" name="check_string" id="check_string"
                           class="form-control @error('check_string') is-invalid @enderror"
                           value="{{ old('check_string', $target->check_string) }}" placeholder="e.g. Welcome, &lt;title&gt;...">
                    @error('check_string')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="pic_user_id">Person In Charge (PIC)</label>
                            <select name="pic_user_id" id="pic_user_id" class="form-control">
                                <option value="">-- No PIC --</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('pic_user_id', $target->pic_user_id) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="alert_methods">Alert Method</label>
                            <select name="alert_methods" id="alert_methods" class="form-control">
                                <option value="message" {{ old('alert_methods', $target->alert_methods) === 'message' ? 'selected' : '' }}>Internal Message</option>
                                <option value="email" {{ old('alert_methods', $target->alert_methods) === 'email' ? 'selected' : '' }}>Email</option>
                                <option value="message,email" {{ old('alert_methods', $target->alert_methods) === 'message,email' ? 'selected' : '' }}>Message + Email</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group form-check mt-4">
                            <input type="checkbox" name="alert_on_down" id="alert_on_down" class="form-check-input"
                                   value="1" {{ old('alert_on_down', $target->alert_on_down) ? 'checked' : '' }}>
                            <label class="form-check-label" for="alert_on_down">Alert when down</label>
                        </div>
                    </div>
                </div>

                <div class="form-group form-check">
                    <input type="checkbox" name="is_active" id="is_active" class="form-check-input"
                           value="1" {{ old('is_active', $target->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Target
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
