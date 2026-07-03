@extends('adminlte::page')

@section('title', 'Service Area ' . $serviceArea->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div><h1 class="mb-0">{{ $serviceArea->name }}</h1><small class="text-muted">{{ $serviceArea->code }}</small></div>
        <div class="d-flex align-items-center" style="gap:.5rem">
            @can('activate', $serviceArea)
                @if ($serviceArea->isDraft())
                    <form method="POST" action="{{ route('service-areas.activate', $serviceArea) }}">@csrf<button class="btn btn-sm btn-success">Activate</button></form>
                @endif
            @endcan
            @can('archive', $serviceArea)
                @if (!$serviceArea->status->isTerminal())
                    <form method="POST" action="{{ route('service-areas.archive', $serviceArea) }}">@csrf<button class="btn btn-sm btn-dark">Archive</button></form>
                @endif
            @endcan
            @can('update', $serviceArea)
                @if (!$serviceArea->status->isTerminal())
                    <a href="{{ route('service-areas.edit', $serviceArea) }}" class="btn btn-sm btn-warning">Edit</a>
                @endif
            @endcan
            <a href="{{ route('service-areas.index') }}" class="btn btn-sm btn-secondary">Back</a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary card-outline"><div class="card-body">
                <p><span class="badge badge-{{ $serviceArea->status->badgeColor() }}">{{ $serviceArea->status->label() }}</span></p>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Cluster</dt><dd class="col-sm-8">{{ $serviceArea->cluster?->name }}</dd>
                    <dt class="col-sm-4">Level</dt><dd class="col-sm-8">{{ $serviceArea->level->label() }}</dd>
                    <dt class="col-sm-4">Parent</dt><dd class="col-sm-8">{{ $serviceArea->parent?->name ?: '—' }}</dd>
                    <dt class="col-sm-4">Merged Into</dt><dd class="col-sm-8">{{ $serviceArea->mergedInto?->name ?: '—' }}</dd>
                </dl>
            </div></div>

            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title mb-0">Employee Assignment</h3></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('service-areas.employees.assign', $serviceArea) }}">
                        @csrf
                        <div class="form-group"><label>Employee</label><select name="employee_id" class="form-control" required><option value="">Select...</option>@foreach ($employees as $employee)<option value="{{ $employee->id }}">{{ $employee->name }}</option>@endforeach</select></div>
                        <div class="form-check mb-3"><input type="checkbox" name="is_primary" value="1" class="form-check-input" id="is_primary"><label class="form-check-label" for="is_primary">Primary Assignment</label></div>
                        <button class="btn btn-sm btn-primary btn-block">Assign Employee</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-outline card-primary mb-3">
                <div class="card-header"><h3 class="card-title mb-0">Overview</h3></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">Boundary</dt><dd class="col-sm-9">{{ $serviceArea->boundary_geojson ? 'Defined' : '—' }}</dd>
                        <dt class="col-sm-3">Center</dt><dd class="col-sm-9">{{ $serviceArea->center_latitude && $serviceArea->center_longitude ? $serviceArea->center_latitude . ', ' . $serviceArea->center_longitude : '—' }}</dd>
                        <dt class="col-sm-3">Notes</dt><dd class="col-sm-9">{{ $serviceArea->notes ?: '—' }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card card-outline card-primary mb-3">
                <div class="card-header"><h3 class="card-title mb-0">Assigned Employees</h3></div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="thead-light"><tr><th>Name</th><th>Primary</th><th>Assigned At</th><th class="text-right">Actions</th></tr></thead>
                        <tbody>
                            @forelse ($serviceArea->employees as $employee)
                                <tr>
                                    <td>{{ $employee->name }}</td>
                                    <td>{{ $employee->pivot->is_primary ? 'Yes' : 'No' }}</td>
                                    <td>{{ $employee->pivot->assigned_at ? \Illuminate\Support\Carbon::parse($employee->pivot->assigned_at)->format('d M Y H:i') : '—' }}</td>
                                    <td class="text-right">
                                        <form method="POST" action="{{ route('service-areas.employees.remove', [$serviceArea, $employee]) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-xs btn-outline-danger">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">No employees assigned.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card card-outline card-primary mb-3">
                <div class="card-header"><h3 class="card-title mb-0">Customers</h3></div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="thead-light"><tr><th>Customer</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse ($serviceArea->customers as $customer)
                                <tr><td><a href="{{ route('customers.show', $customer) }}">{{ $customer->customer_number }} — {{ $customer->name }}</a></td><td>{{ $customer->status->label() }}</td></tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted py-4">No customers assigned.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title mb-0">Lifecycle Actions</h3></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <form method="POST" action="{{ route('service-areas.merge', $serviceArea) }}">
                                @csrf
                                <div class="form-group"><label>Merge Into</label><select name="merged_into_service_area_id" class="form-control" required><option value="">Select active destination...</option>@foreach ($mergeTargets as $mergeTarget)<option value="{{ $mergeTarget->id }}">{{ $mergeTarget->code }} — {{ $mergeTarget->name }}</option>@endforeach</select></div>
                                <button class="btn btn-outline-warning btn-block">Merge Service Area</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop