@extends('adminlte::page')

@section('title', 'FATs')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>FATs</h1>
        @can('create', \App\Models\Fat::class)
            <a href="{{ route('fats.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1"></i> New FAT</a>
        @endcan
    </div>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-body">
            <form method="GET" action="{{ route('fats.index') }}" class="form-inline flex-wrap" style="gap:.5rem">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Code, name, or location..." value="{{ request('search') }}">
                <select name="status" class="form-control form-control-sm">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-secondary"><i class="fas fa-search mr-1"></i>Search</button>
                <a href="{{ route('fats.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-light"><tr><th>Code</th><th>Name</th><th>Lifecycle</th><th>Reachability</th><th>ODF</th><th class="text-right">ONU</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse ($fats as $fat)
                        <tr>
                            <td>{{ $fat->fat_code }}</td>
                            <td><a href="{{ route('fats.show', $fat) }}">{{ $fat->name }}</a></td>
                            <td><span class="badge badge-{{ $fat->status->badgeColor() }}">{{ $fat->status->label() }}</span></td>
                            <td><span class="badge badge-{{ $fat->health_summary['badge'] }}">{{ $fat->health_summary['label'] }}</span></td>
                            <td>{{ $fat->odf?->name ?? '—' }}</td>
                            <td class="text-right">{{ $fat->onus_count }}</td>
                            <td class="text-right">
                                <a class="btn btn-xs btn-info" href="{{ route('fats.show', $fat) }}">View</a>
                                @can('update', $fat)
                                    @if (!$fat->isRetired())
                                        <a class="btn btn-xs btn-warning" href="{{ route('fats.edit', $fat) }}">Edit</a>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No FAT records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($fats->hasPages())
            <div class="card-footer">{{ $fats->links() }}</div>
        @endif
    </div>
@stop
