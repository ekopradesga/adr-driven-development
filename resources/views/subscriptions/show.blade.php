@extends('adminlte::page')

@section('title', 'Subscription #' . $subscription->id)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0">Subscription #{{ $subscription->id }}</h1>
            <small class="text-muted">
                {{ $subscription->customer?->customer_number }} — {{ $subscription->customer?->name }}
            </small>
        </div>
        <div class="d-flex align-items-center" style="gap:.5rem">
            @if ($subscription->isPending())
                @can('activate', $subscription)
                    <form method="POST" action="{{ route('subscriptions.activate', $subscription) }}" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-success"><i class="fas fa-play mr-1"></i> Activate</button>
                    </form>
                @endcan
            @elseif ($subscription->isActive())
                @can('suspend', $subscription)
                    <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#suspendModal">
                        <i class="fas fa-pause mr-1"></i> Suspend
                    </button>
                @endcan
            @elseif ($subscription->isSuspended())
                @can('reactivate', $subscription)
                    <form method="POST" action="{{ route('subscriptions.request-reactivation', $subscription) }}" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-info"><i class="fas fa-hourglass-half mr-1"></i> Request Reactivation</button>
                    </form>
                @endcan
            @elseif ($subscription->isReactivationPending())
                @can('reactivate', $subscription)
                    <form method="POST" action="{{ route('subscriptions.reactivate', $subscription) }}" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-success"><i class="fas fa-play mr-1"></i> Reactivate</button>
                    </form>
                @endcan
            @endif

            @if (!$subscription->isTerminated())
                @can('terminate', $subscription)
                    <button type="button" class="btn btn-sm btn-outline-danger" data-toggle="modal" data-target="#terminateModal">
                        <i class="fas fa-times mr-1"></i> Terminate
                    </button>
                @endcan
            @endif

            @can('update', $subscription)
                @if (!$subscription->isTerminated())
                    <a href="{{ route('subscriptions.edit', $subscription) }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                @endif
            @endcan

            <a href="{{ route('subscriptions.index') }}" class="btn btn-sm btn-secondary">
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
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="row">
        {{-- Left: Detail card --}}
        <div class="col-md-3">
            <div class="card card-primary card-outline">
                <div class="card-body">
                    <p class="text-center mb-2">
                        <span class="badge badge-{{ $subscription->status->badgeColor() }} badge-lg px-3 py-2" style="font-size:1rem">
                            {{ $subscription->status->label() }}
                        </span>
                    </p>
                    <ul class="list-group list-group-unbordered mt-3">
                        <li class="list-group-item px-0">
                            <b>Customer</b>
                            <span class="float-right">
                                <a href="{{ route('customers.show', $subscription->customer) }}">
                                    {{ $subscription->customer?->name }}
                                </a>
                            </span>
                        </li>
                        <li class="list-group-item px-0">
                            <b>Package</b>
                            <span class="float-right">{{ $subscription->package?->name ?? '—' }}</span>
                        </li>
                        <li class="list-group-item px-0">
                            <b>Type</b>
                            <span class="float-right">
                                <span class="badge badge-{{ $subscription->subscription_type->badgeColor() }}">
                                    {{ $subscription->subscription_type->label() }}
                                </span>
                            </span>
                        </li>
                        <li class="list-group-item px-0">
                            <b>Billing Day</b>
                            <span class="float-right">{{ $subscription->billing_day }}</span>
                        </li>
                        @if ($subscription->activated_at)
                            <li class="list-group-item px-0">
                                <b>Activated</b>
                                <span class="float-right">{{ $subscription->activated_at->format('d M Y') }}</span>
                            </li>
                        @endif
                        @if ($subscription->isSuspended())
                            <li class="list-group-item px-0">
                                <b>Suspended</b>
                                <span class="float-right">{{ $subscription->suspended_at?->format('d M Y') }}</span>
                            </li>
                            <li class="list-group-item px-0">
                                <b>Suspension Type</b>
                                <span class="float-right text-capitalize">{{ $subscription->suspension_type }}</span>
                            </li>
                            @if ($subscription->suspension_reason)
                                <li class="list-group-item px-0">
                                    <b>Reason</b>
                                    <span class="float-right" style="max-width:120px;text-align:right;word-break:break-word">{{ $subscription->suspension_reason }}</span>
                                </li>
                            @endif
                        @endif
                        @if ($subscription->isTerminated())
                            <li class="list-group-item px-0">
                                <b>Terminated</b>
                                <span class="float-right">{{ $subscription->terminated_at?->format('d M Y') }}</span>
                            </li>
                        @endif
                        <li class="list-group-item px-0">
                            <b>Created</b>
                            <span class="float-right">{{ $subscription->created_at->format('d M Y') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Right: Tabs --}}
        <div class="col-md-9">
            <div class="card card-outline card-primary">
                <div class="card-header p-0 pt-1 border-bottom-0">
                    <ul class="nav nav-tabs">
                        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-overview"><i class="fas fa-info-circle mr-1"></i> Overview</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-invoices"><i class="fas fa-file-invoice-dollar mr-1"></i> Invoices</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-timeline"><i class="fas fa-history mr-1"></i> Timeline</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-attachments"><i class="fas fa-paperclip mr-1"></i> Attachments</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">

                        <div class="tab-pane fade show active" id="tab-overview">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-box bg-light">
                                        <span class="info-box-icon"><i class="fas fa-file-invoice-dollar text-warning"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text text-muted">Invoices</span>
                                            <span class="info-box-number text-warning">{{ $subscription->invoices->count() }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if ($subscription->notes)
                                <div class="alert alert-light border mt-2">
                                    <i class="fas fa-sticky-note text-muted mr-1"></i>
                                    <strong>Notes:</strong> {{ $subscription->notes }}
                                </div>
                            @endif
                        </div>

                        <div class="tab-pane fade" id="tab-invoices">
                            @if ($subscription->invoices->isEmpty())
                                <p class="text-muted text-center py-4"><i class="fas fa-file-invoice fa-lg mb-2 d-block"></i> No invoices yet.</p>
                            @else
                                <table class="table table-sm table-hover mb-0">
                                    <thead><tr><th>ID</th><th>Status</th><th>Amount</th></tr></thead>
                                    <tbody>
                                        @foreach ($subscription->invoices as $invoice)
                                            <tr>
                                                <td>#{{ $invoice->id }}</td>
                                                <td><span class="badge badge-secondary">{{ $invoice->status }}</span></td>
                                                <td>{{ number_format($invoice->amount ?? 0, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                            <p class="text-muted mt-2 mb-0" style="font-size:.8rem"><i class="fas fa-info-circle mr-1"></i> Full billing management available in the Billing module.</p>
                        </div>

                        <div class="tab-pane fade" id="tab-timeline">
                            <p class="text-muted text-center py-4"><i class="fas fa-history fa-lg mb-2 d-block"></i> Timeline available after Timeline platform component is implemented.</p>
                        </div>

                        <div class="tab-pane fade" id="tab-attachments">
                            <p class="text-muted text-center py-4"><i class="fas fa-paperclip fa-lg mb-2 d-block"></i> Attachments available after Attachment platform component is implemented.</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Suspend Modal --}}
    @can('suspend', $subscription)
        @if ($subscription->isActive())
            <div class="modal fade" id="suspendModal" tabindex="-1">
                <div class="modal-dialog">
                    <form method="POST" action="{{ route('subscriptions.suspend', $subscription) }}">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header bg-warning text-dark">
                                <h5 class="modal-title"><i class="fas fa-pause mr-2"></i> Suspend Subscription</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Suspension Type <span class="text-danger">*</span></label>
                                    <select name="suspension_type" class="form-control @error('suspension_type') is-invalid @enderror" required>
                                        <option value="manual" {{ old('suspension_type') === 'manual' ? 'selected' : '' }}>Manual (operator action)</option>
                                        <option value="overdue" {{ old('suspension_type') === 'overdue' ? 'selected' : '' }}>Overdue (billing policy)</option>
                                    </select>
                                    @error('suspension_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-group mb-0">
                                    <label>Reason <span class="text-danger">*</span></label>
                                    <textarea name="suspension_reason" rows="2" class="form-control @error('suspension_reason') is-invalid @enderror" required>{{ old('suspension_reason') }}</textarea>
                                    @error('suspension_reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-warning">Suspend</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endcan

    {{-- Terminate Modal --}}
    @can('terminate', $subscription)
        @if (!$subscription->isTerminated())
            <div class="modal fade" id="terminateModal" tabindex="-1">
                <div class="modal-dialog">
                    <form method="POST" action="{{ route('subscriptions.terminate', $subscription) }}">
                        @csrf
                        <div class="modal-content border-danger">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title"><i class="fas fa-times mr-2"></i> Terminate Subscription</h5>
                                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-danger"><strong>Warning:</strong> Termination is permanent.</div>
                                <div class="form-group mb-0">
                                    <label>Reason <span class="text-danger">*</span></label>
                                    <textarea name="reason" rows="2" class="form-control @error('reason') is-invalid @enderror" required>{{ old('reason') }}</textarea>
                                    @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger">Terminate</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endcan
@stop
