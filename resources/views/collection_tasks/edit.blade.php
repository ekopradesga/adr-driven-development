@extends('adminlte::page')

@section('title', 'Edit Collection Task')

@section('content_header')
    <h1>Edit Collection Task</h1>
@stop

@section('content')
    <form method="POST" action="{{ route('collection-tasks.update', $collectionTask) }}">
        @csrf
        @method('PUT')
        <div class="card card-outline card-primary">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6"><div class="form-group"><label>Customer</label><select name="customer_id" class="form-control" disabled>@foreach ($customers as $customer)<option value="{{ $customer->id }}" @selected(old('customer_id', $collectionTask->customer_id) == $customer->id)>{{ $customer->customer_number }} — {{ $customer->name }}</option>@endforeach</select></div></div>
                    <div class="col-md-6"><div class="form-group"><label>Collector</label><select name="employee_id" class="form-control" disabled>@foreach ($employees as $employee)<option value="{{ $employee->id }}" @selected(old('employee_id', $collectionTask->employee_id) == $employee->id)>{{ $employee->name }}</option>@endforeach</select></div></div>
                    <div class="col-md-4"><div class="form-group"><label>Scheduled For</label><input type="datetime-local" name="scheduled_for" class="form-control" value="{{ old('scheduled_for', optional($collectionTask->scheduled_for)->format('Y-m-d\TH:i')) }}"></div></div>
                    <div class="col-md-12"><div class="form-group"><label>Notes</label><textarea name="notes" rows="4" class="form-control">{{ old('notes', $collectionTask->notes) }}</textarea></div></div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between"><a href="{{ route('collection-tasks.show', $collectionTask) }}" class="btn btn-secondary">Cancel</a><button class="btn btn-primary">Save</button></div>
        </div>
    </form>
@stop