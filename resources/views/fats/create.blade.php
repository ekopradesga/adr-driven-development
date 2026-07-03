@extends('adminlte::page')

@section('title', 'Create FAT')

@section('content_header')
    <h1>Create FAT</h1>
@stop

@section('content')
    <form method="POST" action="{{ route('fats.store') }}">
        @csrf
        <div class="card card-outline card-primary">
            <div class="card-body">
                <div class="alert alert-info">New FAT assets are created in <strong>Planned</strong> status. Reachability is derived later from downstream ONU observations.</div>
                <div class="row">
                    <div class="col-md-4"><div class="form-group"><label>ODF</label><select name="odf_id" class="form-control" required><option value="">— Select ODF —</option>@foreach ($odfs as $odf)<option value="{{ $odf->id }}" @selected(old('odf_id') == $odf->id)>{{ $odf->odf_code }} — {{ $odf->name }}</option>@endforeach</select></div></div>
                    <div class="col-md-4"><div class="form-group"><label>Service Area</label><select name="service_area_id" class="form-control"><option value="">— Optional —</option>@foreach ($serviceAreas as $serviceArea)<option value="{{ $serviceArea->id }}" @selected(old('service_area_id') == $serviceArea->id)>{{ $serviceArea->code }} — {{ $serviceArea->name }}</option>@endforeach</select></div></div>
                    <div class="col-md-4"><div class="form-group"><label>Code</label><input type="text" name="fat_code" class="form-control" value="{{ old('fat_code') }}" required></div></div>
                    <div class="col-md-6"><div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" value="{{ old('name') }}" required></div></div>
                    <div class="col-md-3"><div class="form-group"><label>Capacity Ports</label><input type="number" name="capacity_ports" class="form-control" value="{{ old('capacity_ports') }}" min="1" required></div></div>
                    <div class="col-md-3"><div class="form-group"><label>Used Ports</label><input type="number" name="used_ports" class="form-control" value="{{ old('used_ports', 0) }}" min="0"></div></div>
                    <div class="col-md-3"><div class="form-group"><label>Splitter Ratio</label><input type="text" name="splitter_ratio" class="form-control" value="{{ old('splitter_ratio') }}"></div></div>
                    <div class="col-md-3"><div class="form-group"><label>Location</label><input type="text" name="location_name" class="form-control" value="{{ old('location_name') }}"></div></div>
                    <div class="col-md-3"><div class="form-group"><label>Latitude</label><input type="number" step="0.0000001" name="latitude" class="form-control" value="{{ old('latitude') }}"></div></div>
                    <div class="col-md-3"><div class="form-group"><label>Longitude</label><input type="number" step="0.0000001" name="longitude" class="form-control" value="{{ old('longitude') }}"></div></div>
                    <div class="col-md-4"><div class="form-group"><label>Last ONU Ping At</label><input type="datetime-local" name="last_onu_ping_at" class="form-control" value="{{ old('last_onu_ping_at') }}"></div></div>
                    <div class="col-md-12"><div class="form-group"><label>Notes</label><textarea name="notes" rows="3" class="form-control">{{ old('notes') }}</textarea></div></div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between"><a href="{{ route('fats.index') }}" class="btn btn-secondary">Cancel</a><button class="btn btn-primary">Create</button></div>
        </div>
    </form>
@stop
