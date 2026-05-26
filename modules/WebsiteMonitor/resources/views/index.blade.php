@extends('layouts.admin')

@section('title', 'Website Monitor')

@section('main-content')
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-heartbeat text-primary"></i> Website Monitor
        </h2>
        <div>
            @can('websitemonitor.manage_settings')
            <a href="{{ route('websitemonitor.settings') }}" class="btn btn-outline-secondary mr-2">
                <i class="fas fa-cog"></i> Settings
            </a>
            @endcan
            @can('websitemonitor.create')
            <a href="{{ route('websitemonitor.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Target
            </a>
            @endcan
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $stats['total'] }}</h3>
                    <small>Total Targets</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $stats['healthy'] }}</h3>
                    <small>Healthy</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $stats['down'] }}</h3>
                    <small>Down</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                    <small>Pending</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="thead-light">
                        <tr>
                            <th>Name</th>
                            <th>URL</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Response Time</th>
                            <th>PIC</th>
                            <th>Last Checked</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($targets as $target)
                        <tr>
                            <td><strong>{{ $target->name }}</strong></td>
                            <td>
                                <a href="{{ $target->url }}" target="_blank" class="text-truncate d-inline-block" style="max-width: 250px;">
                                    <i class="fas fa-external-link-alt fa-xs"></i> {{ $target->url }}
                                </a>
                            </td>
                            <td><span class="badge badge-light">{{ $target->method ?? 'GET' }}</span></td>
                            <td>
                                @if($target->last_checked_at === null)
                                    <span class="badge badge-secondary">Pending</span>
                                @elseif($target->isUp())
                                    <span class="badge badge-success"><i class="fas fa-check-circle"></i> Healthy ({{ $target->last_status }})</span>
                                @else
                                    <span class="badge badge-danger"><i class="fas fa-times-circle"></i> Down ({{ $target->last_status }})</span>
                                    @if($target->last_error)
                                        <br><small class="text-muted" title="{{ $target->last_error }}">{{ \Illuminate\Support\Str::limit($target->last_error, 30) }}</small>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if($target->last_response_time !== null)
                                    {{ number_format($target->last_response_time, 3) }}s
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($target->pic)
                                    <span class="badge badge-info">{{ $target->pic->name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($target->last_checked_at)
                                    {{ $target->last_checked_at->diffForHumans() }}
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @can('websitemonitor.check')
                                    <form action="{{ route('websitemonitor.check', $target->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Check Now">
                                            <i class="fas fa-play"></i>
                                        </button>
                                    </form>
                                    @endcan
                                    @can('websitemonitor.view')
                                    <a href="{{ route('websitemonitor.logs', $target->id) }}" class="btn btn-outline-info" title="View Logs">
                                        <i class="fas fa-history"></i>
                                    </a>
                                    @endcan
                                    @can('websitemonitor.edit')
                                    <a href="{{ route('websitemonitor.edit', $target->id) }}" class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('websitemonitor.delete')
                                    <form action="{{ route('websitemonitor.destroy', $target->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this target?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-heartbeat fa-3x mb-3 d-block"></i>
                                No monitor targets yet.
                                @can('websitemonitor.create')
                                <a href="{{ route('websitemonitor.create') }}">Add your first target</a>.
                                @endcan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @can('websitemonitor.manage_settings')
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-clock"></i> Auto-Check Schedule</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">
                To enable automatic checking, add this cron entry to your server:
            </p>
            <pre class="bg-dark text-light p-3 rounded"><code>* * * * * cd {{ base_path() }} && php artisan monitor:check >> /dev/null 2>&1</code></pre>
            <p class="text-muted mb-0">
                <i class="fas fa-info-circle"></i> Runs every minute. Each target is checked based on its configured interval.
            </p>
        </div>
    </div>
    @endcan
</div>
@endsection
