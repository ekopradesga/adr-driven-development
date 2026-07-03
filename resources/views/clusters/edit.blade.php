@extends('adminlte::page')

@section('title', 'Edit Cluster')

@section('content_header')
    <h1>Edit Cluster</h1>
@stop

@section('content')
    <form method="POST" action="{{ route('clusters.update', $cluster) }}">
        @csrf
        @method('PUT')
        <div class="card card-outline card-primary">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6"><div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" value="{{ old('name', $cluster->name) }}" required></div></div>
                    <div class="col-md-3"><div class="form-group"><label>Code</label><input type="text" name="code" class="form-control" value="{{ old('code', $cluster->code) }}" required></div></div>
                    <div class="col-md-12"><div class="form-group"><label>Description</label><textarea name="description" rows="3" class="form-control">{{ old('description', $cluster->description) }}</textarea></div></div>
                    <div class="col-md-12"><div class="form-group"><label>Notes</label><textarea name="notes" rows="3" class="form-control">{{ old('notes', $cluster->notes) }}</textarea></div></div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between"><a href="{{ route('clusters.show', $cluster) }}" class="btn btn-secondary">Cancel</a><button class="btn btn-primary">Save</button></div>
        </div>
    </form>
@stop