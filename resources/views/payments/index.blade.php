@extends('adminlte::page')

@section('title', 'Payments')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Payments</h1>
        @can('create', \App\Models\Payment::class)
            <a href="{{ route('payments.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> New Payment
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
            <form method="GET" action="{{ route('payments.index') }}" class="form-inline flex-wrap" style="gap:.5rem">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Payment number, reference, or customer..." value="{{ request('search') }}">
                <select name="status" class="form-control form-control-sm">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-secondary"><i class="fas fa-search mr-1"></i> Search</button>
                <a href="{{ route('payments.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Payment</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Method</th>
                        <th class="text-right">Amount</th>
                        <th class="text-right">Allocated</th>
                        <th class="text-right">Unallocated</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td><a href="{{ route('payments.show', $payment) }}">{{ $payment->payment_number }}</a></td>
                            <td>
                                <a href="{{ route('customers.show', $payment->customer) }}">{{ $payment->customer?->name }}</a>
                                <small class="text-muted d-block">{{ $payment->customer?->customer_number }}</small>
                            </td>
                            <td><span class="badge badge-{{ $payment->status->badgeColor() }}">{{ $payment->status->label() }}</span></td>
                            <td>{{ $payment->payment_date?->format('d M Y') }}</td>
                            <td>{{ strtoupper(str_replace('_', ' ', $payment->method)) }}</td>
                            <td class="text-right">{{ number_format((float) $payment->amount, 2) }}</td>
                            <td class="text-right">{{ number_format($payment->allocatedAmount(), 2) }}</td>
                            <td class="text-right">{{ number_format($payment->unallocatedAmount(), 2) }}</td>
                            <td class="text-right">
                                <a href="{{ route('payments.show', $payment) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                                @can('update', $payment)
                                    @if (in_array($payment->status->value, ['intent_created', 'waiting_payment']))
                                        <a href="{{ route('payments.edit', $payment) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i></a>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">No payments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-end">{{ $payments->links() }}</div>
@stop
