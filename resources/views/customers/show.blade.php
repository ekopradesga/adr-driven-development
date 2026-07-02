@extends('adminlte::page')

@section('title', $customer->customer_number . ' — ' . $customer->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0">{{ $customer->name }}</h1>
            <small class="text-muted">{{ $customer->customer_number }}</small>
        </div>
        <div class="d-flex align-items-center" style="gap:.5rem">
            @if (!$customer->isTerminated())
                @can('update', $customer)
                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                @endcan
            @endif

            @if ($customer->isActive())
                @can('suspend', $customer)
                    <button type="button" class="btn btn-sm btn-danger"
                            data-toggle="modal" data-target="#suspendModal">
                        <i class="fas fa-user-lock mr-1"></i> Suspend
                    </button>
                @endcan
            @elseif ($customer->isSuspended())
                @can('reactivate', $customer)
                    <form method="POST" action="{{ route('customers.reactivate', $customer) }}" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-success">
                            <i class="fas fa-user-check mr-1"></i> Reactivate
                        </button>
                    </form>
                @endcan
            @endif

            @if (!$customer->isTerminated() && !$customer->isProspect())
                @can('terminate', $customer)
                    <button type="button" class="btn btn-sm btn-outline-danger"
                            data-toggle="modal" data-target="#terminateModal">
                        <i class="fas fa-user-xmark mr-1"></i> Terminate
                    </button>
                @endcan
            @endif

            <a href="{{ route('customers.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
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
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Customer 360 Workspace --}}
    <div class="row">
        {{-- Left column: Profile card --}}
        <div class="col-md-3">
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-{{ $customer->status->badgeColor() }}"
                             style="width:72px;height:72px;">
                            <i class="fas fa-user-tie fa-2x text-white"></i>
                        </div>
                    </div>
                    <h3 class="profile-username text-center">{{ $customer->name }}</h3>
                    <p class="text-muted text-center mb-0">{{ $customer->customer_number }}</p>
                    <p class="text-center mt-1">
                        <span class="badge badge-{{ $customer->status->badgeColor() }}">
                            {{ $customer->status->label() }}
                        </span>
                        <span class="badge badge-{{ $customer->customer_type->badgeColor() }} ml-1">
                            {{ $customer->customer_type->label() }}
                        </span>
                    </p>

                    <ul class="list-group list-group-unbordered mt-3">
                        @if ($customer->phone)
                            <li class="list-group-item px-0">
                                <b><i class="fas fa-phone fa-sm text-muted mr-1"></i> Phone</b>
                                <span class="float-right">{{ $customer->phone }}</span>
                            </li>
                        @endif
                        @if ($customer->whatsapp_phone)
                            <li class="list-group-item px-0">
                                <b><i class="fab fa-whatsapp fa-sm text-success mr-1"></i> WhatsApp</b>
                                <span class="float-right">{{ $customer->whatsapp_phone }}</span>
                            </li>
                        @endif
                        @if ($customer->email)
                            <li class="list-group-item px-0">
                                <b><i class="fas fa-envelope fa-sm text-muted mr-1"></i> Email</b>
                                <span class="float-right" style="max-width:130px;overflow:hidden;text-overflow:ellipsis">
                                    {{ $customer->email }}
                                </span>
                            </li>
                        @endif
                        @if ($customer->serviceArea)
                            <li class="list-group-item px-0">
                                <b><i class="fas fa-map-marker-alt fa-sm text-muted mr-1"></i> Area</b>
                                <span class="float-right">{{ $customer->serviceArea->name }}</span>
                            </li>
                        @endif
                        @if ($customer->cluster)
                            <li class="list-group-item px-0">
                                <b><i class="fas fa-sitemap fa-sm text-muted mr-1"></i> Cluster</b>
                                <span class="float-right">{{ $customer->cluster->name }}</span>
                            </li>
                        @endif
                        <li class="list-group-item px-0">
                            <b><i class="fas fa-calendar fa-sm text-muted mr-1"></i> Registered</b>
                            <span class="float-right">{{ $customer->created_at->format('d M Y') }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            @if ($customer->address)
                <div class="card card-outline card-secondary">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-map-pin mr-1"></i> Address</h3></div>
                    <div class="card-body">
                        <p class="mb-0">{{ $customer->address }}</p>
                        @if ($customer->latitude && $customer->longitude)
                            <p class="text-muted mt-1 mb-0" style="font-size:.8rem">
                                <i class="fas fa-crosshairs mr-1"></i>
                                {{ $customer->latitude }}, {{ $customer->longitude }}
                            </p>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Right column: 360 tabs --}}
        <div class="col-md-9">
            <div class="card card-outline card-primary">
                <div class="card-header p-0 pt-1 border-bottom-0">
                    <ul class="nav nav-tabs" id="customer360Tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#tab-overview" role="tab">
                                <i class="fas fa-tachometer-alt mr-1"></i> Overview
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-subscriptions" role="tab">
                                <i class="fas fa-wifi mr-1"></i> Subscriptions
                                @if ($customer->subscriptions->count())
                                    <span class="badge badge-secondary ml-1">{{ $customer->subscriptions->count() }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-billing" role="tab">
                                <i class="fas fa-file-invoice-dollar mr-1"></i> Billing
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-payments" role="tab">
                                <i class="fas fa-credit-card mr-1"></i> Payments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-tickets" role="tab">
                                <i class="fas fa-ticket-alt mr-1"></i> Tickets
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-timeline" role="tab">
                                <i class="fas fa-history mr-1"></i> Timeline
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-attachments" role="tab">
                                <i class="fas fa-paperclip mr-1"></i> Attachments
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="customer360TabContent">

                        {{-- Overview Tab --}}
                        <div class="tab-pane fade show active" id="tab-overview" role="tabpanel">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="info-box bg-light">
                                        <span class="info-box-icon"><i class="fas fa-wifi text-primary"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text text-muted">Subscriptions</span>
                                            <span class="info-box-number text-primary">{{ $customer->subscriptions->count() }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box bg-light">
                                        <span class="info-box-icon"><i class="fas fa-file-invoice-dollar text-warning"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text text-muted">Invoices</span>
                                            <span class="info-box-number text-warning">{{ $customer->invoices->count() }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box bg-light">
                                        <span class="info-box-icon"><i class="fas fa-credit-card text-success"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text text-muted">Payments</span>
                                            <span class="info-box-number text-success">{{ $customer->payments->count() }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if ($customer->notes)
                                <div class="alert alert-light border mt-2">
                                    <i class="fas fa-sticky-note text-muted mr-1"></i>
                                    <strong>Notes:</strong> {{ $customer->notes }}
                                </div>
                            @endif
                        </div>

                        {{-- Subscriptions Tab --}}
                        <div class="tab-pane fade" id="tab-subscriptions" role="tabpanel">
                            @if ($customer->subscriptions->isEmpty())
                                <p class="text-muted text-center py-4"><i class="fas fa-inbox fa-lg mb-2 d-block"></i> No subscriptions yet.</p>
                            @else
                                <table class="table table-sm table-hover mb-0">
                                    <thead><tr><th>ID</th><th>Status</th></tr></thead>
                                    <tbody>
                                        @foreach ($customer->subscriptions as $sub)
                                            <tr>
                                                <td>#{{ $sub->id }}</td>
                                                <td><span class="badge badge-secondary">{{ $sub->status }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                            <p class="text-muted mt-2 mb-0" style="font-size:.8rem">
                                <i class="fas fa-info-circle mr-1"></i>
                                Full subscription management available in the Subscription module.
                            </p>
                        </div>

                        {{-- Billing Tab --}}
                        <div class="tab-pane fade" id="tab-billing" role="tabpanel">
                            @if ($customer->invoices->isEmpty())
                                <p class="text-muted text-center py-4"><i class="fas fa-file-invoice fa-lg mb-2 d-block"></i> No invoices yet.</p>
                            @else
                                <table class="table table-sm table-hover mb-0">
                                    <thead><tr><th>ID</th><th>Status</th><th>Amount</th></tr></thead>
                                    <tbody>
                                        @foreach ($customer->invoices as $invoice)
                                            <tr>
                                                <td>#{{ $invoice->id }}</td>
                                                <td><span class="badge badge-secondary">{{ $invoice->status }}</span></td>
                                                <td>{{ number_format($invoice->amount, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                            <p class="text-muted mt-2 mb-0" style="font-size:.8rem">
                                <i class="fas fa-info-circle mr-1"></i>
                                Full billing management available in the Billing module.
                            </p>
                        </div>

                        {{-- Payments Tab --}}
                        <div class="tab-pane fade" id="tab-payments" role="tabpanel">
                            @if ($customer->payments->isEmpty())
                                <p class="text-muted text-center py-4"><i class="fas fa-credit-card fa-lg mb-2 d-block"></i> No payments recorded.</p>
                            @else
                                <table class="table table-sm table-hover mb-0">
                                    <thead><tr><th>ID</th><th>Status</th><th>Amount</th></tr></thead>
                                    <tbody>
                                        @foreach ($customer->payments as $payment)
                                            <tr>
                                                <td>#{{ $payment->id }}</td>
                                                <td><span class="badge badge-secondary">{{ $payment->status }}</span></td>
                                                <td>{{ number_format($payment->amount, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                            <p class="text-muted mt-2 mb-0" style="font-size:.8rem">
                                <i class="fas fa-info-circle mr-1"></i>
                                Full payment management available in the Payments module.
                            </p>
                        </div>

                        {{-- Tickets Tab --}}
                        <div class="tab-pane fade" id="tab-tickets" role="tabpanel">
                            <p class="text-muted text-center py-4">
                                <i class="fas fa-ticket-alt fa-lg mb-2 d-block"></i>
                                Ticket management available in the Ticket module (Sprint 3+).
                            </p>
                        </div>

                        {{-- Timeline Tab --}}
                        <div class="tab-pane fade" id="tab-timeline" role="tabpanel">
                            <p class="text-muted text-center py-4">
                                <i class="fas fa-history fa-lg mb-2 d-block"></i>
                                Timeline available after Timeline platform component is implemented.
                            </p>
                        </div>

                        {{-- Attachments Tab --}}
                        <div class="tab-pane fade" id="tab-attachments" role="tabpanel">
                            <p class="text-muted text-center py-4">
                                <i class="fas fa-paperclip fa-lg mb-2 d-block"></i>
                                Attachment management available after Attachment platform component is implemented.
                            </p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Suspend Modal --}}
    @can('suspend', $customer)
        @if ($customer->isActive())
            <div class="modal fade" id="suspendModal" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <form method="POST" action="{{ route('customers.suspend', $customer) }}">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title"><i class="fas fa-user-lock mr-2"></i> Suspend Customer Account</h5>
                                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <p>You are about to suspend <strong>{{ $customer->name }}</strong> ({{ $customer->customer_number }}).</p>
                                <p class="text-muted small">Customer account suspension is an administrative action. It does <strong>not</strong> automatically suspend active subscriptions.</p>
                                <div class="form-group">
                                    <label for="suspend_reason">Reason <span class="text-danger">*</span></label>
                                    <textarea id="suspend_reason" name="reason" rows="3"
                                              class="form-control @error('reason') is-invalid @enderror"
                                              placeholder="Describe the reason for suspension (min 10 characters)…" required>{{ old('reason') }}</textarea>
                                    @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger">Suspend Account</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endcan

    {{-- Terminate Modal --}}
    @can('terminate', $customer)
        @if (!$customer->isTerminated() && !$customer->isProspect())
            <div class="modal fade" id="terminateModal" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <form method="POST" action="{{ route('customers.terminate', $customer) }}">
                        @csrf
                        <div class="modal-content border-danger">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title"><i class="fas fa-user-xmark mr-2"></i> Terminate Customer Account</h5>
                                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-danger">
                                    <strong>Warning:</strong> Account termination is <strong>permanent</strong> and cannot be undone. All subscriptions must already be terminated.
                                </div>
                                <p>You are about to permanently terminate <strong>{{ $customer->name }}</strong> ({{ $customer->customer_number }}).</p>
                                <div class="form-group">
                                    <label for="terminate_reason">Reason <span class="text-danger">*</span></label>
                                    <textarea id="terminate_reason" name="reason" rows="3"
                                              class="form-control @error('reason') is-invalid @enderror"
                                              placeholder="Describe the reason for termination (min 10 characters)…" required>{{ old('reason') }}</textarea>
                                    @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger">Permanently Terminate</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endcan

@stop

@section('css')
@stop

@section('js')
    <script>
        // Restore modal state if validation failed and we were redirected back
        @if ($errors->any() && old('reason'))
            $(document).ready(function() {
                // Reopen suspend modal if reason field had an error
                @if ($errors->has('reason'))
                    $('#suspendModal').modal('show');
                @endif
            });
        @endif
    </script>
@stop
