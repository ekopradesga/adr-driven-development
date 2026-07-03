@extends('adminlte::page')

@section('title', 'Create Service Area')

@section('content_header')
    <h1>Create Service Area</h1>
@stop

@section('content')
    <form method="POST" action="{{ route('service-areas.store') }}">
        @csrf
        <div class="card card-outline card-primary">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6"><div class="form-group"><label>Cluster</label><select name="cluster_id" class="form-control" required>@foreach ($clusters as $cluster)<option value="{{ $cluster->id }}" @selected(old('cluster_id') == $cluster->id)>{{ $cluster->code }} — {{ $cluster->name }}</option>@endforeach</select></div></div>
                    <div class="col-md-6"><div class="form-group"><label>Parent Area</label><select name="parent_id" class="form-control"><option value="">No parent</option>@foreach ($parentOptions as $parentOption)<option value="{{ $parentOption->id }}" @selected(old('parent_id') == $parentOption->id)>{{ $parentOption->code }} — {{ $parentOption->name }}</option>@endforeach</select></div></div>
                    <div class="col-md-4"><div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" value="{{ old('name') }}" required></div></div>
                    <div class="col-md-4"><div class="form-group"><label>Code</label><input type="text" name="code" class="form-control" value="{{ old('code') }}" required></div></div>
                    <div class="col-md-4"><div class="form-group"><label>Level</label><select name="level" class="form-control">@foreach ($levels as $level)<option value="{{ $level->value }}" @selected(old('level', 'area') === $level->value)>{{ $level->label() }}</option>@endforeach</select></div></div>
                    <div class="col-md-6"><div class="form-group"><label>Center Latitude</label><input type="number" step="any" name="center_latitude" class="form-control" value="{{ old('center_latitude') }}"></div></div>
                    <div class="col-md-6"><div class="form-group"><label>Center Longitude</label><input type="number" step="any" name="center_longitude" class="form-control" value="{{ old('center_longitude') }}"></div></div>
                    <div class="col-md-12"><div class="form-group"><label>Boundary GeoJSON</label><textarea name="boundary_geojson" rows="3" class="form-control">{{ old('boundary_geojson') }}</textarea></div></div>
                    <div class="col-md-6"><div class="form-group"><label>Status</label><select name="status" class="form-control">@foreach ($statuses as $status)<option value="{{ $status->value }}" @selected(old('status', 'draft') === $status->value)>{{ $status->label() }}</option>@endforeach</select></div></div>
                    <div class="col-md-12"><div class="form-group"><label>Notes</label><textarea name="notes" rows="3" class="form-control">{{ old('notes') }}</textarea></div></div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between"><a href="{{ route('service-areas.index') }}" class="btn btn-secondary">Cancel</a><button class="btn btn-primary">Create</button></div>
        </div>
    </form>
@stop