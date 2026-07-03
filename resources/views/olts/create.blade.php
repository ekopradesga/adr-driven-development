@extends('adminlte::page')

@section('title', 'Create OLT')

@section('content_header')
    <h1>Create OLT</h1>
@stop

@section('content')
    <form method="POST" action="{{ route('olts.store') }}">
        @csrf
        <div class="card card-outline card-primary">
            <div class="card-body">
                <div class="alert alert-info">New OLT assets are created in <strong>Planned</strong> status and can be activated later.</div>
                <div class="row">
                    <div class="col-md-3"><div class="form-group"><label>Code</label><input type="text" name="olt_code" class="form-control" value="{{ old('olt_code') }}" required></div></div>
                    <div class="col-md-5"><div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" value="{{ old('name') }}" required></div></div>
                    <div class="col-md-4"><div class="form-group"><label>IP Address</label><input type="text" name="ip_address" class="form-control" value="{{ old('ip_address') }}" required></div></div>
                    <div class="col-md-3"><div class="form-group"><label>Vendor</label><input type="text" name="vendor" class="form-control" value="{{ old('vendor') }}"></div></div>
                    <div class="col-md-3"><div class="form-group"><label>Model</label><input type="text" name="model" class="form-control" value="{{ old('model') }}"></div></div>
                    <div class="col-md-3"><div class="form-group"><label>Location</label><input type="text" name="location_name" class="form-control" value="{{ old('location_name') }}"></div></div>
                    <div class="col-md-3"><div class="form-group"><label>Last Seen At</label><input type="datetime-local" name="last_seen_at" class="form-control" value="{{ old('last_seen_at') }}"></div></div>
                    <div class="col-md-3"><div class="form-group"><label>Latitude</label><input type="number" step="0.0000001" name="latitude" class="form-control" value="{{ old('latitude') }}"></div></div>
                    <div class="col-md-3"><div class="form-group"><label>Longitude</label><input type="number" step="0.0000001" name="longitude" class="form-control" value="{{ old('longitude') }}"></div></div>
                    <div class="col-md-3"><div class="form-group"><label>SNMP Community</label><input type="text" name="snmp_community" class="form-control" value="{{ old('snmp_community') }}"></div></div>
                    <div class="col-md-3"><div class="form-group"><label>API Username</label><input type="text" name="api_username" class="form-control" value="{{ old('api_username') }}"></div></div>
                    <div class="col-md-3"><div class="form-group"><label>API Password</label><input type="text" name="api_password" class="form-control" value="{{ old('api_password') }}"></div></div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between"><a href="{{ route('olts.index') }}" class="btn btn-secondary">Cancel</a><button class="btn btn-primary">Create</button></div>
        </div>
    </form>
@stop
