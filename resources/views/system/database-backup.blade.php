@extends('layouts.admin')
{{-- resources\views\system\database-backup.blade.php --}}
@section('main-content')
<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800">
        <i class="fas fa-database mr-2"></i> Database Backup & Restore
    </h1>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    {{-- Backup --}}
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="POST" action="{{ route('database.backup.run') }}">
                @csrf
                <button class="btn btn-primary">
                    <i class="fas fa-play mr-1"></i> Create Database Backup
                </button>
            </form>
        </div>
    </div>

    {{-- Restore --}}
    <div class="card shadow mb-4">
        <div class="card-header">Restore Database</div>
        <div class="card-body">
            <form method="POST" action="{{ route('database.restore') }}" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <input type="file" name="backup_file" class="form-control" required>
                </div>

                <button class="btn btn-danger"
                        onclick="return confirm('This will overwrite the database. Continue?')">
                    <i class="fas fa-undo"></i> Restore
                </button>
            </form>
        </div>
    </div>

    {{-- History --}}
    <div class="card shadow">
        <div class="card-header">Backup History</div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>File</th>
                        <th>Size</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($backups as $backup)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $backup['name'] }}</td>
                        <td>{{ $backup['size'] }}</td>
                        <td>{{ $backup['time'] }}</td>
                        <td>
                            <a href="{{ route('database.backup.download', $backup['name']) }}"
                               class="btn btn-sm btn-success">
                                <i class="fas fa-download"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No backups</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
