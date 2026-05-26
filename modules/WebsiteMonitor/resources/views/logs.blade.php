@extends('layouts.admin')

@section('title', 'Monitor Logs - ' . $target->name)

@section('main-content')
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-history text-primary"></i> Logs: {{ $target->name }}
        </h2>
        <a href="{{ route('websitemonitor.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <strong>URL:</strong>
                    <a href="{{ $target->url }}" target="_blank">{{ $target->url }}</a>
                </div>
                <div class="col-md-3">
                    <strong>Status:</strong>
                    @if($target->last_checked_at === null)
                        <span class="badge badge-secondary">Pending</span>
                    @elseif($target->isUp())
                        <span class="badge badge-success">Healthy</span>
                    @else
                        <span class="badge badge-danger">Down</span>
                    @endif
                </div>
                <div class="col-md-3">
                    <strong>Last Checked:</strong>
                    {{ $target->last_checked_at ? $target->last_checked_at->diffForHumans() : 'Never' }}
                </div>
                <div class="col-md-3">
                    <strong>Response:</strong>
                    {{ $target->last_response_time ? number_format($target->last_response_time, 3) . 's' : '-' }}
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
                            <th>#</th>
                            <th>Status Code</th>
                            <th>Response Time</th>
                            <th>Error</th>
                            <th>Checked By</th>
                            <th>Checked At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->id }}</td>
                            <td>
                                @if($log->status_code === $target->expected_status)
                                    <span class="badge badge-success">{{ $log->status_code }}</span>
                                @else
                                    <span class="badge badge-danger">{{ $log->status_code ?: 'N/A' }}</span>
                                @endif
                            </td>
                            <td>{{ number_format($log->response_time, 3) }}s</td>
                            <td>
                                @if($log->error_message)
                                    <span class="text-danger" title="{{ $log->error_message }}">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        {{ \Illuminate\Support\Str::limit($log->error_message, 50) }}
                                    </span>
                                @else
                                    <span class="text-success"><i class="fas fa-check"></i> OK</span>
                                @endif
                            </td>
                            <td>{{ optional($log->checker)->name ?? 'System' }}</td>
                            <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-history fa-3x mb-3 d-block"></i>
                                No logs recorded yet.
                                <a href="{{ route('websitemonitor.check', $target->id) }}"
                                   onclick="event.preventDefault(); document.getElementById('check-form-{{ $target->id }}').submit();"
                                   class="btn btn-sm btn-success mt-2 d-block">
                                    <i class="fas fa-play"></i> Check Now
                                </a>
                                <form id="check-form-{{ $target->id }}" action="{{ route('websitemonitor.check', $target->id) }}" method="POST" style="display:none;">@csrf</form>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
