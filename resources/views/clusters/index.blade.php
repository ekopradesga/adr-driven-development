@extends('adminlte::page')

@section('title', 'Clusters')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Clusters</h1>
        @can('create', \App\Models\Cluster::class)
            <a href="{{ route('clusters.create') }}" class="btn btn-primary btn-sm">New Cluster</a>
        @endcan
    </div>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-body">
            <form method="GET" action="{{ route('clusters.index') }}" class="form-inline flex-wrap" style="gap:.5rem">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Name or code..." value="{{ request('search') }}">
                <select name="status" class="form-control form-control-sm">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-secondary">Search</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-light"><tr><th>Code</th><th>Name</th><th>Status</th><th class="text-right">Areas</th><th class="text-right">Customers</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse ($clusters as $cluster)
                        <tr>
                            <td>{{ $cluster->code }}</td>
                            <td><a href="{{ route('clusters.show', $cluster) }}">{{ $cluster->name }}</a></td>
                            <td><span class="badge badge-{{ $cluster->status->badgeColor() }}">{{ $cluster->status->label() }}</span></td>
                            <td class="text-right">{{ $cluster->service_areas_count }}</td>
                            <td class="text-right">{{ $cluster->customers_count }}</td>
                            <td class="text-right"><a class="btn btn-xs btn-info" href="{{ route('clusters.show', $cluster) }}">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No clusters found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-end">{{ $clusters->links() }}</div>
@stop