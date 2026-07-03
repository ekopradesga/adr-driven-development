@extends('adminlte::page')

@section('title', 'New Package')

@section('content_header')
    <h1>Create Package</h1>
@stop

@section('content')
    <form method="POST" action="{{ route('packages.store') }}">
        @csrf
        <div class="card card-outline card-primary">
            <div class="card-body">
                <div class="alert alert-info">New packages are created in <strong>Draft</strong> status and can be activated later.</div>

                <div class="row">
                    <div class="col-md-3"><div class="form-group"><label>Code</label><input type="text" name="package_code" class="form-control" value="{{ old('package_code') }}" required></div></div>
                    <div class="col-md-9"><div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" value="{{ old('name') }}" required></div></div>

                    <div class="col-md-3"><div class="form-group"><label>Downstream (Kbps)</label><input type="number" name="downstream_kbps" class="form-control" value="{{ old('downstream_kbps') }}" min="1" required></div></div>
                    <div class="col-md-3"><div class="form-group"><label>Upstream (Kbps)</label><input type="number" name="upstream_kbps" class="form-control" value="{{ old('upstream_kbps') }}" min="1" required></div></div>
                    <div class="col-md-3"><div class="form-group"><label>Contention Ratio</label><input type="number" name="contention_ratio" class="form-control" value="{{ old('contention_ratio', 1) }}" min="1"></div></div>
                    <div class="col-md-3"><div class="form-group"><label>Billing Cycle Type</label><input type="text" name="billing_cycle_type" class="form-control" value="{{ old('billing_cycle_type', 'monthly') }}"></div></div>

                    <div class="col-md-3"><div class="form-group"><label>Monthly Price</label><input type="number" step="0.01" name="monthly_price" class="form-control" value="{{ old('monthly_price') }}" min="0" required></div></div>
                    <div class="col-md-3"><div class="form-group"><label>Setup Fee</label><input type="number" step="0.01" name="setup_fee" class="form-control" value="{{ old('setup_fee', 0) }}" min="0"></div></div>
                    <div class="col-md-3"><div class="form-group"><label>Billing Cycle Days</label><input type="number" name="billing_cycle_days" class="form-control" value="{{ old('billing_cycle_days') }}" min="1" max="365"></div></div>

                    <div class="col-md-12"><div class="form-group"><label>Description</label><textarea name="description" rows="3" class="form-control">{{ old('description') }}</textarea></div></div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between"><a href="{{ route('packages.index') }}" class="btn btn-secondary">Cancel</a><button class="btn btn-primary">Create</button></div>
        </div>
    </form>
@stop
