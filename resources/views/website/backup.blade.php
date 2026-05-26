@extends('layouts.admin')

@section('main-content')
<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800">
        <i class="fas fa-database mr-2"></i> Website Backup
    </h1>

    <div class="card shadow mb-4">
        <div class="card-body">

            <p class="text-muted">
                Create a full system backup including database and uploaded files.
            </p>

            <form method="POST" action="{{ route('website.backup.run') }}">
                @csrf

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-play mr-1"></i> Run Backup
                </button>
            </form>

        </div>
    </div>

    {{-- Backup History --}}
    <div class="card shadow">
        <div class="card-header">
            <strong>Backup History</strong>
        </div>

        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>File</th>
                        <th>Size</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($backups ?? [] as $backup)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $backup['name'] }}</td>
                            <td>{{ $backup['size'] }}</td>
                            <td>{{ $backup['time'] }}</td>
                            <td>
                                <a href="{{ route('website.backup.download', $backup['name']) }}"
                                   class="btn btn-sm btn-success">
                                    <i class="fas fa-download"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                No backups found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
