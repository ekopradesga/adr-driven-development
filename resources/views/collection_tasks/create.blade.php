@extends('adminlte::page')

@section('title', 'Create Collection Task')

@section('content_header')
    <h1>Create Collection Task</h1>
@stop

@section('content')
    <form method="POST" action="{{ route('collection-tasks.store') }}">
        @csrf
        <div class="card card-outline card-primary">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6"><div class="form-group"><label>Customer</label><select name="customer_id" class="form-control" required>@foreach ($customers as $customer)<option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->customer_number }} — {{ $customer->name }}</option>@endforeach</select></div></div>
                    <div class="col-md-6"><div class="form-group"><label>Collector</label><select name="employee_id" class="form-control"><option value="">Waiting Assignment</option>@foreach ($employees as $employee)<option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>{{ $employee->name }}</option>@endforeach</select></div></div>
                    <div class="col-md-4"><div class="form-group"><label>Scheduled For</label><input type="datetime-local" name="scheduled_for" class="form-control" value="{{ old('scheduled_for') }}"></div></div>
                    <div class="col-md-12"><div class="form-group"><label>Notes</label><textarea name="notes" rows="4" class="form-control">{{ old('notes') }}</textarea></div></div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between"><a href="{{ route('collection-tasks.index') }}" class="btn btn-secondary">Cancel</a><button class="btn btn-primary">Create</button></div>
        </div>
    </form>
@stop