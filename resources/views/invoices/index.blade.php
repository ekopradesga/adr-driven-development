@extends('adminlte::page')

@section('title', 'Invoices')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Invoices</h1>
        @can('create', \App\Models\Invoice::class)
            <a href="{{ route('invoices.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> New Invoice
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

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Search &amp; Filter</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('invoices.index') }}" class="form-inline flex-wrap" style="gap:.5rem">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Invoice number or customer…" value="{{ request('search') }}">
                <select name="status" class="form-control form-control-sm">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-secondary"><i class="fas fa-search mr-1"></i> Search</button>
                <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Invoice</th>
                        <th>Customer</th>
                        <th>Subscription</th>
                        <th>Status</th>
                        <th>Period</th>
                        <th>Due</th>
                        <th class="text-right">Total</th>
                        <th class="text-right">Balance</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td><a href="{{ route('invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td>
                            <td>
                                <a href="{{ route('customers.show', $invoice->customer) }}">{{ $invoice->customer?->name }}</a>
                                <small class="text-muted d-block">{{ $invoice->customer?->customer_number }}</small>
                            </td>
                            <td>
                                <a href="{{ route('subscriptions.show', $invoice->subscription) }}">Subscription #{{ $invoice->subscription_id }}</a>
                            </td>
                            <td><span class="badge badge-{{ $invoice->status->badgeColor() }}">{{ $invoice->status->label() }}</span></td>
                            <td>{{ $invoice->period_start?->format('d M Y') }} - {{ $invoice->period_end?->format('d M Y') }}</td>
                            <td>{{ $invoice->due_date?->format('d M Y') }}</td>
                            <td class="text-right">{{ number_format((float) $invoice->total_amount, 2) }}</td>
                            <td class="text-right">{{ number_format((float) $invoice->balance_amount, 2) }}</td>
                            <td class="text-right">
                                <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                                @can('update', $invoice)
                                    @if ($invoice->isDraft())
                                        <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i></a>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">No invoices found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-end">{{ $invoices->links() }}</div>
@stop
