@extends('layouts.admin')

@section('main-content')
<div class="container mt-4">
    <div class="row justify-content-start">
        <div class="col-md-6">
            <h1>wCORE Settings</h1>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="site_name" class="form-label">wCORE Name</label>
                    <input type="text" name="site_name" class="form-control" value="{{ $settings['site_name'] ?? '' }}">
                </div>

                <div class="mb-3">
                    <label for="site_email" class="form-label">wCORE Email</label>
                    <input type="email" name="site_email" class="form-control" value="{{ $settings['site_email'] ?? '' }}">
                </div>

                 <div class="mb-3">
                    <label for="patch_ver" class="form-label">patch ver</label>
                    <input type="text" name="patch_ver" class="form-control" value="{{ $settings['patch_ver'] ?? '' }}">
                </div>

                <div class="mb-3">
                    <label for="site_logo" class="form-label">wCORE Logo</label>
                    <input type="file" name="site_logo" class="form-control">
                    @if(!empty($settings['site_logo']))
                        <img src="{{ asset('storage/' . $settings['site_logo']) }}" alt="Logo" height="50" class="mt-2">
                    @endif
                </div>

                <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
        </div>
    </div>
</div>
@endsection
