@extends('adminlte::page')

@section('title', 'Routers')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Routers</h1>
        @can('create', \App\Models\WireRouter::class)
            <a href="{{ route('routers.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1"></i> New Router</a>
        @endcan
    </div>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-body">
            <form method="GET" action="{{ route('routers.index') }}" class="form-inline flex-wrap" style="gap:.5rem">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Code, name, or IP..." value="{{ request('search') }}">
                <select name="router_type" class="form-control form-control-sm">
                    <option value="">All Types</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->value }}" @selected(request('router_type') === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-control form-control-sm">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-secondary"><i class="fas fa-search mr-1"></i>Search</button>
                <a href="{{ route('routers.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-light"><tr><th>Code</th><th>Name</th><th>Type</th><th>Status</th><th>IP</th><th class="text-right">Children</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse ($routers as $router)
                        <tr>
                            <td>{{ $router->router_code }}</td>
                            <td><a href="{{ route('routers.show', $router) }}">{{ $router->name }}</a></td>
                            <td><span class="badge badge-{{ $router->router_type->badgeColor() }}">{{ $router->router_type->label() }}</span></td>
                            <td><span class="badge badge-{{ $router->status->badgeColor() }}">{{ $router->status->label() }}</span></td>
                            <td>{{ $router->ip_address }}</td>
                            <td class="text-right">{{ $router->children_count }}</td>
                            <td class="text-right">
                                <a class="btn btn-xs btn-info" href="{{ route('routers.show', $router) }}">View</a>
                                @can('update', $router)
                                    @if (!$router->isRetired())
                                        <a class="btn btn-xs btn-warning" href="{{ route('routers.edit', $router) }}">Edit</a>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No Router records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($routers->hasPages())
            <div class="card-footer">{{ $routers->links() }}</div>
        @endif
    </div>
@stop