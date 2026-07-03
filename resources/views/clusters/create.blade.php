@extends('adminlte::page')

@section('title', 'Create Cluster')

@section('content_header')
    <h1>Create Cluster</h1>
@stop

@section('content')
    <form method="POST" action="{{ route('clusters.store') }}">
        @csrf
        <div class="card card-outline card-primary">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6"><div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" value="{{ old('name') }}" required></div></div>
                    <div class="col-md-3"><div class="form-group"><label>Code</label><input type="text" name="code" class="form-control" value="{{ old('code') }}" required></div></div>
                    <div class="col-md-3"><div class="form-group"><label>Status</label><select name="status" class="form-control">@foreach ($statuses as $status)<option value="{{ $status->value }}" @selected(old('status', 'planned') === $status->value)>{{ $status->label() }}</option>@endforeach</select></div></div>
                    <div class="col-md-12"><div class="form-group"><label>Description</label><textarea name="description" rows="3" class="form-control">{{ old('description') }}</textarea></div></div>
                    <div class="col-md-12"><div class="form-group"><label>Notes</label><textarea name="notes" rows="3" class="form-control">{{ old('notes') }}</textarea></div></div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between"><a href="{{ route('clusters.index') }}" class="btn btn-secondary">Cancel</a><button class="btn btn-primary">Create</button></div>
        </div>
    </form>
@stop