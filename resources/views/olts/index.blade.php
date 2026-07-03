@extends('adminlte::page')

@section('title', 'OLTs')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>OLTs</h1>
        @can('create', \App\Models\Olt::class)
            <a href="{{ route('olts.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1"></i> New OLT</a>
        @endcan
    </div>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-body">
            <form method="GET" action="{{ route('olts.index') }}" class="form-inline flex-wrap" style="gap:.5rem">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Code, name, or IP..." value="{{ request('search') }}">
                <select name="status" class="form-control form-control-sm">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-secondary"><i class="fas fa-search mr-1"></i>Search</button>
                <a href="{{ route('olts.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-light"><tr><th>Code</th><th>Name</th><th>IP</th><th>Status</th><th>Vendor</th><th class="text-right">ONUs</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse ($olts as $olt)
                        <tr>
                            <td>{{ $olt->olt_code }}</td>
                            <td><a href="{{ route('olts.show', $olt) }}">{{ $olt->name }}</a></td>
                            <td>{{ $olt->ip_address }}</td>
                            <td><span class="badge badge-{{ $olt->status->badgeColor() }}">{{ $olt->status->label() }}</span></td>
                            <td>{{ $olt->vendor ?: '—' }}</td>
                            <td class="text-right">{{ $olt->onus_count }}</td>
                            <td class="text-right">
                                <a class="btn btn-xs btn-info" href="{{ route('olts.show', $olt) }}">View</a>
                                @can('update', $olt)
                                    @if (!$olt->isRetired())
                                        <a class="btn btn-xs btn-warning" href="{{ route('olts.edit', $olt) }}">Edit</a>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No OLT records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($olts->hasPages())
            <div class="card-footer">{{ $olts->links() }}</div>
        @endif
    </div>
@stop
