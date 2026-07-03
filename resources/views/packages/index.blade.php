@extends('adminlte::page')

@section('title', 'Packages')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Packages</h1>
        @can('create', \App\Models\Package::class)
            <a href="{{ route('packages.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> New Package
            </a>
        @endcan
    </div>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-body">
            <form method="GET" action="{{ route('packages.index') }}" class="form-inline flex-wrap" style="gap:.5rem">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Code or name..." value="{{ request('search') }}">
                <select name="status" class="form-control form-control-sm">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-secondary"><i class="fas fa-search mr-1"></i>Search</button>
                <a href="{{ route('packages.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-light"><tr><th>Code</th><th>Name</th><th>Status</th><th class="text-right">Price</th><th class="text-right">Speed</th><th class="text-right">Active Subs</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse ($packages as $package)
                        <tr>
                            <td>{{ $package->package_code }}</td>
                            <td><a href="{{ route('packages.show', $package) }}">{{ $package->name }}</a></td>
                            <td><span class="badge badge-{{ $package->status->badgeColor() }}">{{ $package->status->label() }}</span></td>
                            <td class="text-right">Rp {{ number_format($package->monthly_price, 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($package->downstream_kbps) }} / {{ number_format($package->upstream_kbps) }} Kbps</td>
                            <td class="text-right">{{ $package->active_subscriptions_count }}</td>
                            <td class="text-right">
                                <a class="btn btn-xs btn-info" href="{{ route('packages.show', $package) }}">View</a>
                                @can('update', $package)
                                    @if (!$package->isRetired())
                                        <a class="btn btn-xs btn-warning" href="{{ route('packages.edit', $package) }}">Edit</a>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No packages found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($packages->hasPages())
            <div class="card-footer">{{ $packages->links() }}</div>
        @endif
    </div>
@stop
