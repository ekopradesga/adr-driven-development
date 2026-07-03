@extends('adminlte::page')

@section('title', 'Allocation ' . $allocation->id)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0">Allocation #{{ $allocation->id }}</h1>
            <small class="text-muted">Payment {{ $allocation->payment?->payment_number }} - Invoice {{ $allocation->invoice?->invoice_number }}</small>
        </div>
        <a href="{{ route('payments.show', $allocation->payment) }}" class="btn btn-secondary btn-sm">Back to Payment</a>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-4">
            <div class="card card-outline card-primary">
                <div class="card-body">
                    <p class="text-center mb-2"><span class="badge badge-{{ $allocation->status->badgeColor() }} px-3 py-2">{{ $allocation->status->label() }}</span></p>
                    <ul class="list-group list-group-unbordered mt-3">
                        <li class="list-group-item px-0"><b>Payment</b><span class="float-right">{{ $allocation->payment?->payment_number }}</span></li>
                        <li class="list-group-item px-0"><b>Invoice</b><span class="float-right">{{ $allocation->invoice?->invoice_number }}</span></li>
                        <li class="list-group-item px-0"><b>Allocated Amount</b><span class="float-right">{{ number_format((float) $allocation->allocated_amount, 2) }}</span></li>
                        <li class="list-group-item px-0"><b>Allocated At</b><span class="float-right">{{ $allocation->allocated_at?->format('d M Y H:i') ?? '—' }}</span></li>
                        <li class="list-group-item px-0"><b>Reversed At</b><span class="float-right">{{ $allocation->reversed_at?->format('d M Y H:i') ?? '—' }}</span></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title mb-0">Allocation Controls</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-light border">
                        Allocation reversals and reallocation preserve audit history. Use reverse for cancellation and reallocate to move this amount to a different invoice.
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <form method="POST" action="{{ route('payments.allocations.reverse', [$allocation->payment, $allocation]) }}">
                                @csrf
                                <div class="form-group">
                                    <label>Reversal reason</label>
                                    <textarea name="reversal_reason" class="form-control" rows="3" required></textarea>
                                </div>
                                <button class="btn btn-danger">Reverse Allocation</button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form method="POST" action="{{ route('payments.allocations.reallocate', [$allocation->payment, $allocation]) }}">
                                @csrf
                                <div class="form-group"><label>Invoice ID</label><input type="number" name="invoice_id" class="form-control" required></div>
                                <div class="form-group"><label>Allocated Amount</label><input type="number" step="0.01" name="allocated_amount" class="form-control" required></div>
                                <div class="form-group"><label>Notes</label><input type="text" name="notes" class="form-control"></div>
                                <button class="btn btn-primary">Reallocate</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
