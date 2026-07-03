@extends('adminlte::page')

@section('title', 'Payment Allocations')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0">Payment Allocations</h1>
            <small class="text-muted">{{ $payment->payment_number }} - {{ $payment->customer?->name }}</small>
        </div>
        <a href="{{ route('payments.show', $payment) }}" class="btn btn-secondary btn-sm">Back to Payment</a>
    </div>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Invoice</th>
                        <th>Status</th>
                        <th>Allocated At</th>
                        <th class="text-right">Amount</th>
                        <th>Notes</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($allocations as $allocation)
                        <tr>
                            <td><a href="{{ route('payments.allocations.show', [$payment, $allocation]) }}">{{ $allocation->invoice?->invoice_number }}</a></td>
                            <td><span class="badge badge-{{ $allocation->status->badgeColor() }}">{{ $allocation->status->label() }}</span></td>
                            <td>{{ $allocation->allocated_at?->format('d M Y H:i') }}</td>
                            <td class="text-right">{{ number_format((float) $allocation->allocated_amount, 2) }}</td>
                            <td>{{ $allocation->notes ?: '—' }}</td>
                            <td class="text-right">
                                <a href="{{ route('payments.allocations.show', [$payment, $allocation]) }}" class="btn btn-xs btn-info">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No allocations found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
