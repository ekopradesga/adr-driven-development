@extends('adminlte::page')

@section('title', 'Package ' . $package->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div><h1 class="mb-0">{{ $package->name }}</h1><small class="text-muted">{{ $package->package_code }}</small></div>
        <div class="d-flex align-items-center" style="gap:.5rem">
            @can('activate', $package)
                @if (!$package->isActive() && !$package->isRetired())
                    <form method="POST" action="{{ route('packages.activate', $package) }}">@csrf<button class="btn btn-sm btn-success">Activate</button></form>
                @endif
            @endcan
            @can('deprecate', $package)
                @if ($package->isActive())
                    <form method="POST" action="{{ route('packages.deprecate', $package) }}">@csrf<button class="btn btn-sm btn-warning">Deprecate</button></form>
                @endif
            @endcan
            @can('retire', $package)
                @if (!$package->isRetired())
                    <form method="POST" action="{{ route('packages.retire', $package) }}">@csrf<button class="btn btn-sm btn-dark">Retire</button></form>
                @endif
            @endcan
            @can('update', $package)
                @if (!$package->isRetired())
                    <a href="{{ route('packages.edit', $package) }}" class="btn btn-sm btn-primary">Edit</a>
                @endif
            @endcan
            <a href="{{ route('packages.index') }}" class="btn btn-sm btn-secondary">Back</a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary card-outline"><div class="card-body">
                <p><span class="badge badge-{{ $package->status->badgeColor() }}">{{ $package->status->label() }}</span></p>
                <dl class="row mb-0">
                    <dt class="col-sm-5">Monthly Price</dt><dd class="col-sm-7">Rp {{ number_format($package->monthly_price, 0, ',', '.') }}</dd>
                    <dt class="col-sm-5">Setup Fee</dt><dd class="col-sm-7">Rp {{ number_format($package->setup_fee, 0, ',', '.') }}</dd>
                    <dt class="col-sm-5">Speed</dt><dd class="col-sm-7">{{ number_format($package->downstream_kbps) }} / {{ number_format($package->upstream_kbps) }} Kbps</dd>
                    <dt class="col-sm-5">Contention</dt><dd class="col-sm-7">{{ $package->contention_ratio }}:1</dd>
                    <dt class="col-sm-5">Billing Cycle</dt><dd class="col-sm-7">{{ $package->billing_cycle_type }}{{ $package->billing_cycle_days ? ' / ' . $package->billing_cycle_days . ' days' : '' }}</dd>
                    <dt class="col-sm-5">Subscriptions</dt><dd class="col-sm-7">{{ $package->subscriptions->count() }}</dd>
                </dl>
            </div></div>
        </div>
        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title mb-0">Recent Subscriptions</h3></div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="thead-light"><tr><th>#</th><th>Customer</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                        <tbody>
                            @forelse ($package->subscriptions as $subscription)
                                <tr>
                                    <td>#{{ $subscription->id }}</td>
                                    <td>{{ $subscription->customer?->name ?? '—' }}</td>
                                    <td>{{ $subscription->status->label() }}</td>
                                    <td class="text-right"><a class="btn btn-xs btn-info" href="{{ route('subscriptions.show', $subscription) }}">View</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">No subscriptions linked.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card card-outline card-secondary">
                <div class="card-header"><h3 class="card-title mb-0">Description</h3></div>
                <div class="card-body">{{ $package->description ?: 'No description.' }}</div>
            </div>
        </div>
    </div>
@stop
