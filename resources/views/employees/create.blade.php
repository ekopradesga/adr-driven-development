@extends('adminlte::page')

@section('title', 'Create Employee')

@section('content_header')
    <h1>Create Employee</h1>
@stop

@section('content')
    <form method="POST" action="{{ route('employees.store') }}">
        @csrf
        <div class="card card-outline card-primary">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6"><div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" value="{{ old('name') }}" required></div></div>
                    <div class="col-md-6"><div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" value="{{ old('email') }}"></div></div>
                    <div class="col-md-4"><div class="form-group"><label>Phone</label><input type="text" name="phone" class="form-control" value="{{ old('phone') }}"></div></div>
                    <div class="col-md-4"><div class="form-group"><label>Status</label><select name="status" class="form-control">@foreach ($statuses as $status)<option value="{{ $status->value }}" @selected(old('status', 'active') === $status->value)>{{ $status->label() }}</option>@endforeach</select></div></div>
                    <div class="col-md-12"><div class="form-group"><label>Notes</label><textarea name="notes" rows="4" class="form-control">{{ old('notes') }}</textarea></div></div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between"><a href="{{ route('employees.index') }}" class="btn btn-secondary">Cancel</a><button class="btn btn-primary">Create</button></div>
        </div>
    </form>
@stop