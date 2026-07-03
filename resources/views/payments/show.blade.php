@extends('adminlte::page')

@section('title', 'Payment ' . $payment->payment_number)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0">Payment {{ $payment->payment_number }}</h1>
            <small class="text-muted">{{ $payment->customer?->customer_number }} - {{ $payment->customer?->name }}</small>
        </div>
        <div class="d-flex align-items-center" style="gap:.5rem">
            @can('receive', $payment)
                @if (in_array($payment->status->value, ['intent_created', 'waiting_payment']))
                    <form method="POST" action="{{ route('payments.receive', $payment) }}">@csrf<button class="btn btn-sm btn-primary"><i class="fas fa-inbox mr-1"></i> Receive</button></form>
                @endif
            @endcan
            @can('validatePayment', $payment)
                @if ($payment->isReceived())
                    <form method="POST" action="{{ route('payments.validate', $payment) }}">@csrf<button class="btn btn-sm btn-info"><i class="fas fa-check-double mr-1"></i> Validate</button></form>
                @endif
            @endcan
            @can('record', $payment)
                @if ($payment->isValidated())
                    <form method="POST" action="{{ route('payments.record', $payment) }}">@csrf<button class="btn btn-sm btn-success"><i class="fas fa-file-signature mr-1"></i> Record</button></form>
                @endif
            @endcan
            @can('complete', $payment)
                @if (in_array($payment->status->value, ['partially_allocated', 'fully_allocated']))
                    <form method="POST" action="{{ route('payments.complete', $payment) }}">@csrf<button class="btn btn-sm btn-success"><i class="fas fa-flag-checkered mr-1"></i> Complete</button></form>
                @endif
            @endcan
            @can('reverse', $payment)
                @if (in_array($payment->status->value, ['recorded', 'partially_allocated', 'fully_allocated', 'completed']))
                    <button type="button" class="btn btn-sm btn-outline-danger" data-toggle="modal" data-target="#reverseModal"><i class="fas fa-undo mr-1"></i> Reverse</button>
                @endif
            @endcan
            @can('fail', $payment)
                @if (!$payment->status->isTerminal())
                    <button type="button" class="btn btn-sm btn-outline-dark" data-toggle="modal" data-target="#failModal"><i class="fas fa-exclamation-triangle mr-1"></i> Fail</button>
                @endif
            @endcan
            @can('update', $payment)
                @if (in_array($payment->status->value, ['intent_created', 'waiting_payment']))
                    <a href="{{ route('payments.edit', $payment) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit mr-1"></i> Edit</a>
                @endif
            @endcan
            <a href="{{ route('payments.index') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Back</a>
        </div>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="row">
        <div class="col-md-3">
            <div class="card card-primary card-outline">
                <div class="card-body">
                    <p class="text-center mb-2">
                        <span class="badge badge-{{ $payment->status->badgeColor() }} badge-lg px-3 py-2" style="font-size:1rem">{{ $payment->status->label() }}</span>
                    </p>
                    <ul class="list-group list-group-unbordered mt-3">
                        <li class="list-group-item px-0"><b>Customer</b><span class="float-right"><a href="{{ route('customers.show', $payment->customer) }}">{{ $payment->customer?->name }}</a></span></li>
                        <li class="list-group-item px-0"><b>Date</b><span class="float-right">{{ $payment->payment_date?->format('d M Y') }}</span></li>
                        <li class="list-group-item px-0"><b>Amount</b><span class="float-right">{{ number_format((float) $payment->amount, 2) }}</span></li>
                        <li class="list-group-item px-0"><b>Allocated</b><span class="float-right">{{ number_format($payment->allocatedAmount(), 2) }}</span></li>
                        <li class="list-group-item px-0"><b>Unallocated</b><span class="float-right">{{ number_format($payment->unallocatedAmount(), 2) }}</span></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card card-outline card-primary">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs" id="payment-tabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#overview">Overview</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#allocations">Allocations</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#timeline">Timeline</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#attachments">Attachments</a></li>
                    </ul>
                </div>
                <div class="card-body tab-content">
                    <div class="tab-pane fade show active" id="overview">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Payment Snapshot</h5>
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">Number</dt><dd class="col-sm-8">{{ $payment->payment_number }}</dd>
                                    <dt class="col-sm-4">Method</dt><dd class="col-sm-8">{{ strtoupper(str_replace('_', ' ', $payment->method)) }}</dd>
                                    <dt class="col-sm-4">Currency</dt><dd class="col-sm-8">{{ $payment->currency }}</dd>
                                    <dt class="col-sm-4">Reference</dt><dd class="col-sm-8">{{ $payment->channel_reference ?: '—' }}</dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <h5>Lifecycle Timestamps</h5>
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">Recorded At</dt><dd class="col-sm-8">{{ $payment->recorded_at?->format('d M Y H:i') ?? '—' }}</dd>
                                    <dt class="col-sm-4">Completed At</dt><dd class="col-sm-8">{{ $payment->completed_at?->format('d M Y H:i') ?? '—' }}</dd>
                                    <dt class="col-sm-4">Reversed At</dt><dd class="col-sm-8">{{ $payment->reversed_at?->format('d M Y H:i') ?? '—' }}</dd>
                                    <dt class="col-sm-4">Failure</dt><dd class="col-sm-8">{{ $payment->failure_reason ?: '—' }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="allocations">
                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="thead-light">
                                    <tr><th>Invoice</th><th>Status</th><th>Allocated At</th><th class="text-right">Amount</th><th>Notes</th></tr>
                                </thead>
                                <tbody>
                                    @forelse ($payment->allocations as $allocation)
                                        <tr>
                                            <td><a href="{{ route('invoices.show', $allocation->invoice) }}">{{ $allocation->invoice?->invoice_number }}</a></td>
                                            <td>{{ $allocation->status->label() }}</td>
                                            <td>{{ $allocation->allocated_at?->format('d M Y H:i') }}</td>
                                            <td class="text-right">{{ number_format((float) $allocation->allocated_amount, 2) }}</td>
                                            <td>{{ $allocation->notes ?: '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted">No allocations yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @can('allocate', $payment)
                            @if (in_array($payment->status->value, ['recorded', 'partially_allocated', 'fully_allocated']) && $payment->unallocatedAmount() > 0)
                                <form method="POST" action="{{ route('payments.allocate', $payment) }}">
                                    @csrf
                                    <div class="card card-light">
                                        <div class="card-header"><h3 class="card-title mb-0">Add Allocation</h3></div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4"><div class="form-group"><label>Invoice ID</label><input type="number" name="allocations[0][invoice_id]" class="form-control" required></div></div>
                                                <div class="col-md-4"><div class="form-group"><label>Allocated Amount</label><input type="number" step="0.01" name="allocations[0][allocated_amount]" class="form-control" required></div></div>
                                                <div class="col-md-4"><div class="form-group"><label>Notes</label><input type="text" name="allocations[0][notes]" class="form-control"></div></div>
                                            </div>
                                        </div>
                                        <div class="card-footer text-right"><button class="btn btn-sm btn-primary">Apply Allocation</button></div>
                                    </div>
                                </form>
                            @endif
                        @endcan
                    </div>
                    <div class="tab-pane fade" id="timeline">
                        <div class="alert alert-light border mb-0">Timeline integration will render here once the Timeline module is wired.</div>
                    </div>
                    <div class="tab-pane fade" id="attachments">
                        <div class="alert alert-light border mb-0">Attachments will render here once shared attachments are wired.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="reverseModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('payments.reverse', $payment) }}">
                    @csrf
                    <div class="modal-header"><h5 class="modal-title">Reverse Payment</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                    <div class="modal-body"><div class="form-group"><label>Reversal reason</label><textarea name="reversal_reason" class="form-control" rows="4" required></textarea></div></div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button><button type="submit" class="btn btn-danger">Reverse Payment</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="failModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('payments.fail', $payment) }}">
                    @csrf
                    <div class="modal-header"><h5 class="modal-title">Mark Payment as Failed</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                    <div class="modal-body"><div class="form-group"><label>Failure reason</label><textarea name="failure_reason" class="form-control" rows="4" required></textarea></div></div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button><button type="submit" class="btn btn-dark">Mark Failed</button></div>
                </form>
            </div>
        </div>
    </div>
@stop
