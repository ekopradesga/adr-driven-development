@extends('adminlte::page')

@section('title', 'FAT ' . $fat->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div><h1 class="mb-0">{{ $fat->name }}</h1><small class="text-muted">{{ $fat->fat_code }}</small></div>
        <div class="d-flex align-items-center" style="gap:.5rem">
            @can('activate', $fat)
                @if (!$fat->isActive() && !$fat->isRetired())
                    <form method="POST" action="{{ route('fats.activate', $fat) }}">@csrf<button class="btn btn-sm btn-success">Activate</button></form>
                @endif
            @endcan
            @can('maintenance', $fat)
                @if ($fat->isActive())
                    <form method="POST" action="{{ route('fats.maintenance', $fat) }}">@csrf<button class="btn btn-sm btn-warning">Maintenance</button></form>
                @endif
            @endcan
            @can('retire', $fat)
                @if (!$fat->isRetired())
                    <form method="POST" action="{{ route('fats.retire', $fat) }}">@csrf<button class="btn btn-sm btn-dark">Retire</button></form>
                @endif
            @endcan
            @can('update', $fat)
                @if (!$fat->isRetired())
                    <a href="{{ route('fats.edit', $fat) }}" class="btn btn-sm btn-primary">Edit</a>
                @endif
            @endcan
            <a href="{{ route('fats.index') }}" class="btn btn-sm btn-secondary">Back</a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary card-outline"><div class="card-body">
                <p><span class="badge badge-{{ $fat->status->badgeColor() }}">{{ $fat->status->label() }}</span></p>
                <p><span class="badge badge-{{ $healthSummary['badge'] }}">{{ $healthSummary['label'] }}</span></p>
                <p class="text-muted small">{{ $healthSummary['reason'] }}</p>
                <dl class="row mb-0">
                    <dt class="col-sm-5">ODF</dt><dd class="col-sm-7">{{ $fat->odf?->name ?? '—' }}</dd>
                    <dt class="col-sm-5">OLT</dt><dd class="col-sm-7">{{ $fat->odf?->olt?->name ?? '—' }}</dd>
                    <dt class="col-sm-5">Service Area</dt><dd class="col-sm-7">{{ $fat->serviceArea?->name ?? '—' }}</dd>
                    <dt class="col-sm-5">Capacity</dt><dd class="col-sm-7">{{ $fat->used_ports }} / {{ $fat->capacity_ports }}</dd>
                    <dt class="col-sm-5">Last Ping</dt><dd class="col-sm-7">{{ $fat->last_onu_ping_at?->format('d M Y H:i') ?? '—' }}</dd>
                </dl>
            </div></div>
        </div>
        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title mb-0">Downstream ONUs</h3></div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="thead-light"><tr><th>Serial</th><th>PON Port</th><th>Status</th><th>OLT</th></tr></thead>
                        <tbody>
                            @forelse ($fat->onus as $onu)
                                <tr>
                                    <td>{{ $onu->onu_sn }}</td>
                                    <td>{{ $onu->pon_port }}</td>
                                    <td>{{ ucfirst($onu->status) }}</td>
                                    <td>{{ $onu->olt?->name ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">No downstream ONU mapped.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
