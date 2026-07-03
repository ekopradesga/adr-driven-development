@extends('adminlte::page')

@section('title', 'OLT ' . $olt->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div><h1 class="mb-0">{{ $olt->name }}</h1><small class="text-muted">{{ $olt->olt_code }} · {{ $olt->ip_address }}</small></div>
        <div class="d-flex align-items-center" style="gap:.5rem">
            @can('activate', $olt)
                @if (!$olt->isActive() && !$olt->isRetired())
                    <form method="POST" action="{{ route('olts.activate', $olt) }}">@csrf<button class="btn btn-sm btn-success">Activate</button></form>
                @endif
            @endcan
            @can('maintenance', $olt)
                @if ($olt->isActive())
                    <form method="POST" action="{{ route('olts.maintenance', $olt) }}">@csrf<button class="btn btn-sm btn-warning">Maintenance</button></form>
                @endif
            @endcan
            @can('retire', $olt)
                @if (!$olt->isRetired())
                    <form method="POST" action="{{ route('olts.retire', $olt) }}">@csrf<button class="btn btn-sm btn-dark">Retire</button></form>
                @endif
            @endcan
            @can('update', $olt)
                @if (!$olt->isRetired())
                    <a href="{{ route('olts.edit', $olt) }}" class="btn btn-sm btn-primary">Edit</a>
                @endif
            @endcan
            <a href="{{ route('olts.index') }}" class="btn btn-sm btn-secondary">Back</a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary card-outline"><div class="card-body">
                <p><span class="badge badge-{{ $olt->status->badgeColor() }}">{{ $olt->status->label() }}</span></p>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Vendor</dt><dd class="col-sm-8">{{ $olt->vendor ?: '—' }}</dd>
                    <dt class="col-sm-4">Model</dt><dd class="col-sm-8">{{ $olt->model ?: '—' }}</dd>
                    <dt class="col-sm-4">Location</dt><dd class="col-sm-8">{{ $olt->location_name ?: '—' }}</dd>
                    <dt class="col-sm-4">Last Seen</dt><dd class="col-sm-8">{{ $olt->last_seen_at?->format('d M Y H:i') ?? '—' }}</dd>
                    <dt class="col-sm-4">ONUs</dt><dd class="col-sm-8">{{ $olt->onus->count() }}</dd>
                </dl>
            </div></div>
        </div>
        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title mb-0">Recent ONUs</h3></div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="thead-light"><tr><th>Serial</th><th>PON Port</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse ($olt->onus as $onu)
                                <tr>
                                    <td>{{ $onu->onu_sn }}</td>
                                    <td>{{ $onu->pon_port }}</td>
                                    <td>{{ $onu->status }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">No ONU records linked.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
