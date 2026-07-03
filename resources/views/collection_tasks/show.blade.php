@extends('adminlte::page')

@section('title', 'Collection Task ' . $collectionTask->id)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0">Collection Task #{{ $collectionTask->id }}</h1>
            <small class="text-muted">{{ $collectionTask->customer?->customer_number }} - {{ $collectionTask->customer?->name }}</small>
        </div>
        <div class="d-flex align-items-center" style="gap:.5rem">
            @can('update', $collectionTask)
                @if (!$collectionTask->isTerminal())
                    <a href="{{ route('collection-tasks.edit', $collectionTask) }}" class="btn btn-sm btn-warning">Edit</a>
                @endif
            @endcan
            <a href="{{ route('collection-tasks.index') }}" class="btn btn-sm btn-secondary">Back</a>
        </div>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-body">
                    <p class="text-center mb-2"><span class="badge badge-{{ $collectionTask->status->badgeColor() }} badge-lg px-3 py-2" style="font-size:1rem">{{ $collectionTask->status->label() }}</span></p>
                    <ul class="list-group list-group-unbordered mt-3">
                        <li class="list-group-item px-0"><b>Collector</b><span class="float-right">{{ $collectionTask->employee?->name ?: 'Waiting Assignment' }}</span></li>
                        <li class="list-group-item px-0"><b>Scheduled For</b><span class="float-right">{{ $collectionTask->scheduled_for?->format('d M Y H:i') ?: '—' }}</span></li>
                        <li class="list-group-item px-0"><b>Route Started</b><span class="float-right">{{ $collectionTask->route_started_at?->format('d M Y H:i') ?: '—' }}</span></li>
                        <li class="list-group-item px-0"><b>Visited</b><span class="float-right">{{ $collectionTask->visited_at?->format('d M Y H:i') ?: '—' }}</span></li>
                        <li class="list-group-item px-0"><b>Completed</b><span class="float-right">{{ $collectionTask->completed_at?->format('d M Y H:i') ?: '—' }}</span></li>
                    </ul>
                </div>
            </div>

            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title mb-0">Assignment</h3></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('collection-tasks.assign', $collectionTask) }}" class="mb-3">
                        @csrf
                        <div class="form-group"><label>Collector</label><select name="employee_id" class="form-control" required><option value="">Select...</option>@foreach ($employees as $employee)<option value="{{ $employee->id }}">{{ $employee->name }}</option>@endforeach</select></div>
                        <button class="btn btn-sm btn-primary">Assign</button>
                    </form>
                    <form method="POST" action="{{ route('collection-tasks.schedule', $collectionTask) }}">
                    @csrf
                    <div class="form-group"><label>Schedule</label><input type="datetime-local" name="scheduled_for" class="form-control" required></div>
                    <button class="btn btn-sm btn-info">Schedule</button></form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#overview">Overview</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#invoices">Invoices</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#actions">Actions</a></li>
                    </ul>
                </div>
                <div class="card-body tab-content">
                    <div class="tab-pane fade show active" id="overview">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Customer</dt><dd class="col-sm-8"><a href="{{ route('customers.show', $collectionTask->customer) }}">{{ $collectionTask->customer?->display_name }}</a></dd>
                            <dt class="col-sm-4">Notes</dt><dd class="col-sm-8">{{ $collectionTask->notes ?: '—' }}</dd>
                            <dt class="col-sm-4">Follow Up</dt><dd class="col-sm-8">{{ $collectionTask->follow_up_reason ?: '—' }}</dd>
                            <dt class="col-sm-4">Cancellation</dt><dd class="col-sm-8">{{ $collectionTask->cancellation_reason ?: '—' }}</dd>
                            <dt class="col-sm-4">Collected Amount</dt><dd class="col-sm-8">{{ $collectionTask->payment_collected_amount !== null ? number_format((float) $collectionTask->payment_collected_amount, 2) : '—' }}</dd>
                            <dt class="col-sm-4">Submission Ref</dt><dd class="col-sm-8">{{ $collectionTask->payment_submission_reference ?: '—' }}</dd>
                        </dl>
                    </div>
                    <div class="tab-pane fade" id="invoices">
                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="thead-light"><tr><th>Invoice</th><th>Status</th><th>Outcome</th><th class="text-right">Actions</th></tr></thead>
                                <tbody>
                                    @forelse ($collectionTask->invoices as $collectionTaskInvoice)
                                        <tr>
                                            <td><a href="{{ route('invoices.show', $collectionTaskInvoice->invoice) }}">{{ $collectionTaskInvoice->invoice?->invoice_number }}</a></td>
                                            <td>{{ $collectionTaskInvoice->status->label() }}</td>
                                            <td>{{ $collectionTaskInvoice->resolution_outcome ?: '—' }}</td>
                                            <td class="text-right">
                                                @can('update', $collectionTaskInvoice)
                                                    @if (!$collectionTaskInvoice->isCancelled())
                                                        <form method="POST" action="{{ route('collection-tasks.invoices.resolve', [$collectionTask, $collectionTaskInvoice]) }}" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="resolution_outcome" value="resolved">
                                                            <button class="btn btn-xs btn-success">Resolve</button>
                                                        </form>
                                                    @endif
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted">No invoices linked.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <form method="POST" action="{{ route('collection-tasks.invoices.store', $collectionTask) }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-4"><div class="form-group"><label>Invoice ID</label><input type="number" name="invoice_id" class="form-control" required></div></div>
                                <div class="col-md-4"><div class="form-group"><label>Reason</label><input type="text" name="inclusion_reason" class="form-control"></div></div>
                                <div class="col-md-4 d-flex align-items-end"><button class="btn btn-primary">Add Invoice</button></div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="actions">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <form method="POST" action="{{ route('collection-tasks.start-route', $collectionTask) }}">@csrf<button class="btn btn-outline-warning btn-block">Start Route</button></form>
                            </div>
                            <div class="col-md-6 mb-3">
                                <form method="POST" action="{{ route('collection-tasks.record-visit', $collectionTask) }}">@csrf<div class="form-group"><label>Collected Amount</label><input type="number" step="0.01" name="payment_collected_amount" class="form-control"></div><div class="form-group"><label>Submission Ref</label><input type="text" name="payment_submission_reference" class="form-control"></div><button class="btn btn-outline-info btn-block">Record Visit</button></form>
                            </div>
                            <div class="col-md-6 mb-3">
                                <form method="POST" action="{{ route('collection-tasks.complete', $collectionTask) }}">@csrf<button class="btn btn-outline-success btn-block">Complete</button></form>
                            </div>
                            <div class="col-md-6 mb-3">
                                <form method="POST" action="{{ route('collection-tasks.follow-up-required', $collectionTask) }}">@csrf<div class="form-group"><label>Follow Up Reason</label><textarea name="follow_up_reason" class="form-control" rows="3"></textarea></div><button class="btn btn-outline-danger btn-block">Follow Up Required</button></form>
                            </div>
                            <div class="col-md-12">
                                <form method="POST" action="{{ route('collection-tasks.cancel', $collectionTask) }}">@csrf<div class="form-group"><label>Cancellation Reason</label><textarea name="cancellation_reason" class="form-control" rows="3"></textarea></div><button class="btn btn-dark btn-block">Cancel Task</button></form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop