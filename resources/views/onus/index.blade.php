@extends('adminlte::page')

@section('title', 'ONUs')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>ONUs</h1>
        @can('create', \App\Models\Onu::class)
            <a href="{{ route('onus.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1"></i> New ONU</a>
        @endcan
    </div>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-body">
            <form method="GET" action="{{ route('onus.index') }}" class="form-inline flex-wrap" style="gap:.5rem">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Serial, label, or port..." value="{{ request('search') }}">
                <select name="olt_id" class="form-control form-control-sm">
                    <option value="">All OLTs</option>
                    @foreach ($olts as $olt)
                        <option value="{{ $olt->id }}" @selected((string) request('olt_id') === (string) $olt->id)>{{ $olt->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-control form-control-sm">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-secondary"><i class="fas fa-search mr-1"></i>Search</button>
                <a href="{{ route('onus.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-light"><tr><th>Serial</th><th>OLT</th><th>FAT</th><th>Status</th><th>Port</th><th class="text-right">Subscriptions</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse ($onus as $onu)
                        <tr>
                            <td><a href="{{ route('onus.show', $onu) }}">{{ $onu->onu_sn }}</a></td>
                            <td>{{ $onu->olt?->name ?? 'None' }}</td>
                            <td>{{ $onu->fat?->name ?? 'None' }}</td>
                            <td><span class="badge badge-{{ $onu->status->badgeColor() }}">{{ $onu->status->label() }}</span></td>
                            <td>{{ $onu->pon_port }}</td>
                            <td class="text-right">{{ $onu->subscriptions_count }}</td>
                            <td class="text-right">
                                <a class="btn btn-xs btn-info" href="{{ route('onus.show', $onu) }}">View</a>
                                @can('update', $onu)
                                    @if (!$onu->isRetired())
                                        <a class="btn btn-xs btn-warning" href="{{ route('onus.edit', $onu) }}">Edit</a>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No ONU records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($onus->hasPages())
            <div class="card-footer">{{ $onus->links() }}</div>
        @endif
    </div>
@stop