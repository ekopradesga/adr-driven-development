@extends('adminlte::page')

@section('title', 'Customers')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Customers</h1>
        @can('create', \App\Models\Customer::class)
            <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> New Customer
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
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('error') }}
        </div>
    @endif

    {{-- Search / Filter --}}
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Search &amp; Filter</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('customers.index') }}" class="form-inline flex-wrap" style="gap:.5rem">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Name, number, email or phone…" value="{{ request('search') }}" style="min-width:200px">
                <select name="status" class="form-control form-control-sm">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-secondary">
                    <i class="fas fa-search mr-1"></i> Search
                </button>
                <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            </form>
        </div>
    </div>

    {{-- Results --}}
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Customer #</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Area</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr>
                            <td>
                                <a href="{{ route('customers.show', $customer) }}" class="font-weight-bold">
                                    {{ $customer->customer_number }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('customers.show', $customer) }}">{{ $customer->name }}</a>
                                @if ($customer->customer_type->isBusiness())
                                    <span class="badge badge-primary ml-1" style="font-size:0.65rem">Business</span>
                                @endif
                            </td>
                            <td>
                                @if ($customer->phone)
                                    <div><i class="fas fa-phone fa-xs text-muted mr-1"></i>{{ $customer->phone }}</div>
                                @endif
                                @if ($customer->email)
                                    <div class="text-muted" style="font-size:.85rem">{{ $customer->email }}</div>
                                @endif
                            </td>
                            <td>
                                {{ $customer->serviceArea?->name ?? '—' }}
                                @if ($customer->cluster)
                                    <div class="text-muted" style="font-size:.85rem">{{ $customer->cluster->name }}</div>
                                @endif
                            </td>
                            <td>
                                @php $status = $customer->status @endphp
                                <span class="badge badge-{{ $status->badgeColor() }}">
                                    {{ $status->label() }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('customers.show', $customer) }}" class="btn btn-xs btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('update', $customer)
                                    @if (!$customer->isTerminated())
                                        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-xs btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No customers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($customers->hasPages())
            <div class="card-footer">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
@stop

@section('css')
@stop

@section('js')
@stop
