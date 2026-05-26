@extends('layouts.admin')

@section('main-content')
<h1><i class="fas fa-folder-open me-2"></i>File Hosting Settings</h1>

<form method="POST" action="#">
    @csrf

    <div class="mb-3">
        <label class="form-label">Max Upload Size (MB)</label>
        <input type="number" class="form-control" value="10">
    </div>

    <div class="mb-3">
        <label class="form-label">Enable Thumbnails</label>
        <select class="form-select">
            <option value="1">Yes</option>
            <option value="0">No</option>
        </select>
    </div>

    <button class="btn btn-primary">
        <i class="fas fa-save me-1"></i> Save Settings
    </button>
</form>
@endsection
