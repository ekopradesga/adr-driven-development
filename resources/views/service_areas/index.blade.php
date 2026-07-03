@extends('adminlte::page')

@section('title', 'Service Areas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Service Areas</h1>
        @can('create', \App\Models\ServiceArea::class)
            <a href="{{ route('service-areas.create') }}" class="btn btn-primary btn-sm">New Service Area</a>
        @endcan
    </div>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-body">
            <form method="GET" action="{{ route('service-areas.index') }}" class="form-inline flex-wrap" style="gap:.5rem">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Name or code..." value="{{ request('search') }}">
                <select name="cluster_id" class="form-control form-control-sm"><option value="">All Clusters</option>@foreach ($clusters as $cluster)<option value="{{ $cluster->id }}" @selected((string) request('cluster_id') === (string) $cluster->id)>{{ $cluster->name }}</option>@endforeach</select>
                <select name="level" class="form-control form-control-sm"><option value="">All Levels</option>@foreach ($levels as $level)<option value="{{ $level->value }}" @selected(request('level') === $level->value)>{{ $level->label() }}</option>@endforeach</select>
                <select name="status" class="form-control form-control-sm"><option value="">All Statuses</option>@foreach ($statuses as $status)<option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>@endforeach</select>
                <button class="btn btn-sm btn-secondary">Search</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-light"><tr><th>Code</th><th>Name</th><th>Cluster</th><th>Level</th><th>Status</th><th class="text-right">Employees</th><th class="text-right">Customers</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse ($serviceAreas as $serviceArea)
                        <tr>
                            <td>{{ $serviceArea->code }}</td>
                            <td><a href="{{ route('service-areas.show', $serviceArea) }}">{{ $serviceArea->name }}</a></td>
                            <td>{{ $serviceArea->cluster?->name }}</td>
                            <td>{{ $serviceArea->level->label() }}</td>
                            <td><span class="badge badge-{{ $serviceArea->status->badgeColor() }}">{{ $serviceArea->status->label() }}</span></td>
                            <td class="text-right">{{ $serviceArea->employees_count }}</td>
                            <td class="text-right">{{ $serviceArea->customers_count }}</td>
                            <td class="text-right"><a class="btn btn-xs btn-info" href="{{ route('service-areas.show', $serviceArea) }}">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No service areas found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-end">{{ $serviceAreas->links() }}</div>
@stop