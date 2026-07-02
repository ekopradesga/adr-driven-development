@extends('adminlte::page')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0">Invoice {{ $invoice->invoice_number }}</h1>
            <small class="text-muted">
                {{ $invoice->customer?->customer_number }} — {{ $invoice->customer?->name }}
            </small>
        </div>
        <div class="d-flex align-items-center" style="gap:.5rem">
            @can('publish', $invoice)
                @if ($invoice->isDraft())
                    <form method="POST" action="{{ route('invoices.publish', $invoice) }}">
                        @csrf
                        <button class="btn btn-sm btn-success"><i class="fas fa-check mr-1"></i> Publish</button>
                    </form>
                @endif
            @endcan

            @can('cancel', $invoice)
                @if ($invoice->isDraft())
                    <button type="button" class="btn btn-sm btn-outline-danger" data-toggle="modal" data-target="#cancelModal">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                @endif
            @endcan

            @can('update', $invoice)
                @if ($invoice->isDraft())
                    <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit mr-1"></i> Edit</a>
                @endif
            @endcan

            <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Back</a>
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
                        <span class="badge badge-{{ $invoice->status->badgeColor() }} badge-lg px-3 py-2" style="font-size:1rem">{{ $invoice->status->label() }}</span>
                    </p>
                    <ul class="list-group list-group-unbordered mt-3">
                        <li class="list-group-item px-0"><b>Customer</b><span class="float-right"><a href="{{ route('customers.show', $invoice->customer) }}">{{ $invoice->customer?->name }}</a></span></li>
                        <li class="list-group-item px-0"><b>Subscription</b><span class="float-right"><a href="{{ route('subscriptions.show', $invoice->subscription) }}">#{{ $invoice->subscription_id }}</a></span></li>
                        <li class="list-group-item px-0"><b>Period</b><span class="float-right">{{ $invoice->period_start?->format('d M Y') }} - {{ $invoice->period_end?->format('d M Y') }}</span></li>
                        <li class="list-group-item px-0"><b>Due Date</b><span class="float-right">{{ $invoice->due_date?->format('d M Y') }}</span></li>
                        <li class="list-group-item px-0"><b>Total</b><span class="float-right">{{ number_format((float) $invoice->total_amount, 2) }}</span></li>
                        <li class="list-group-item px-0"><b>Balance</b><span class="float-right">{{ number_format((float) $invoice->balance_amount, 2) }}</span></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card card-outline card-primary">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs" id="invoice-tabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#overview">Overview</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#items">Items</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#timeline">Timeline</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#attachments">Attachments</a></li>
                    </ul>
                </div>
                <div class="card-body tab-content">
                    <div class="tab-pane fade show active" id="overview">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Billing Snapshot</h5>
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">Invoice Number</dt><dd class="col-sm-8">{{ $invoice->invoice_number }}</dd>
                                    <dt class="col-sm-4">Issue Date</dt><dd class="col-sm-8">{{ $invoice->issue_date?->format('d M Y') }}</dd>
                                    <dt class="col-sm-4">Published At</dt><dd class="col-sm-8">{{ $invoice->published_at?->format('d M Y H:i') ?? '—' }}</dd>
                                    <dt class="col-sm-4">Overdue At</dt><dd class="col-sm-8">{{ $invoice->overdue_at?->format('d M Y H:i') ?? '—' }}</dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <h5>Financial Summary</h5>
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">Subtotal</dt><dd class="col-sm-8">{{ number_format((float) $invoice->subtotal_amount, 2) }}</dd>
                                    <dt class="col-sm-4">Tax</dt><dd class="col-sm-8">{{ number_format((float) $invoice->tax_amount, 2) }}</dd>
                                    <dt class="col-sm-4">Discount</dt><dd class="col-sm-8">{{ number_format((float) $invoice->discount_amount, 2) }}</dd>
                                    <dt class="col-sm-4">Paid</dt><dd class="col-sm-8">{{ number_format((float) $invoice->paid_amount, 2) }}</dd>
                                    <dt class="col-sm-4">Balance</dt><dd class="col-sm-8">{{ number_format((float) $invoice->balance_amount, 2) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="items">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="thead-light">
                                    <tr><th>Sort</th><th>Description</th><th>Type</th><th class="text-right">Qty</th><th class="text-right">Unit</th><th class="text-right">Total</th></tr>
                                </thead>
                                <tbody>
                                    @forelse ($invoice->items as $item)
                                        <tr>
                                            <td>{{ $item->sort_order }}</td>
                                            <td>{{ $item->description }}</td>
                                            <td>{{ $item->itemTypeLabel() }}</td>
                                            <td class="text-right">{{ $item->quantity }}</td>
                                            <td class="text-right">{{ number_format((float) $item->unit_price, 2) }}</td>
                                            <td class="text-right">{{ number_format((float) $item->total_amount, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center text-muted">No invoice items.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
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

    <div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('invoices.cancel', $invoice) }}">
                    @csrf
                    <div class="modal-header"><h5 class="modal-title">Cancel Invoice</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Cancellation reason</label>
                            <textarea name="cancellation_reason" class="form-control" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Cancel Invoice</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
