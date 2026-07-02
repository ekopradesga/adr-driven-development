@extends('adminlte::page')

@section('title', 'Subscriptions')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Subscriptions</h1>
        @can('create', \App\Models\Subscription::class)
            <a href="{{ route('subscriptions.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> New Subscription
            </a>
        @endcan
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    {{-- Search / Filter --}}
    <div class="card card-outline card-primary">
        <div class="card-header"><h3 class="card-title">Search &amp; Filter</h3>
            <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('subscriptions.index') }}" class="form-inline flex-wrap" style="gap:.5rem">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Customer name or number…" value="{{ request('search') }}">
                <select name="status" class="form-control form-control-sm">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-secondary"><i class="fas fa-search mr-1"></i> Search</button>
                <a href="{{ route('subscriptions.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Package</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Billing Day</th>
                        <th>Activated</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subscriptions as $sub)
                        <tr>
                            <td><a href="{{ route('subscriptions.show', $sub) }}">#{{ $sub->id }}</a></td>
                            <td>
                                @if ($sub->customer)
                                    <a href="{{ route('customers.show', $sub->customer) }}">
                                        {{ $sub->customer->name }}
                                        <small class="text-muted">({{ $sub->customer->customer_number }})</small>
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $sub->package?->name ?? '—' }}</td>
                            <td>
                                <span class="badge badge-{{ $sub->subscription_type->badgeColor() }}">
                                    {{ $sub->subscription_type->label() }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $sub->status->badgeColor() }}">
                                    {{ $sub->status->label() }}
                                </span>
                                @if ($sub->isSuspended())
                                    <small class="text-muted d-block">{{ $sub->suspension_type }}</small>
                                @endif
                            </td>
                            <td>{{ $sub->billing_day }}</td>
                            <td>{{ $sub->activated_at?->format('d M Y') ?? '—' }}</td>
                            <td class="text-right">
                                <a href="{{ route('subscriptions.show', $sub) }}" class="btn btn-xs btn-info" title="View"><i class="fas fa-eye"></i></a>
                                @can('update', $sub)
                                    @if (!$sub->isTerminated())
                                        <a href="{{ route('subscriptions.edit', $sub) }}" class="btn btn-xs btn-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No subscriptions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($subscriptions->hasPages())
            <div class="card-footer">{{ $subscriptions->links() }}</div>
        @endif
    </div>
@stop
