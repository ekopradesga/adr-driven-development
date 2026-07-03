@extends('adminlte::page')

@section('title', 'Edit Package')

@section('content_header')
    <h1>Edit Package</h1>
@stop

@section('content')
    <form method="POST" action="{{ route('packages.update', $package) }}">
        @csrf
        @method('PUT')
        <div class="card card-outline card-primary">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3"><div class="form-group"><label>Code</label><input type="text" name="package_code" class="form-control" value="{{ old('package_code', $package->package_code) }}" required></div></div>
                    <div class="col-md-9"><div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" value="{{ old('name', $package->name) }}" required></div></div>

                    <div class="col-md-3"><div class="form-group"><label>Downstream (Kbps)</label><input type="number" name="downstream_kbps" class="form-control" value="{{ old('downstream_kbps', $package->downstream_kbps) }}" min="1" required></div></div>
                    <div class="col-md-3"><div class="form-group"><label>Upstream (Kbps)</label><input type="number" name="upstream_kbps" class="form-control" value="{{ old('upstream_kbps', $package->upstream_kbps) }}" min="1" required></div></div>
                    <div class="col-md-3"><div class="form-group"><label>Contention Ratio</label><input type="number" name="contention_ratio" class="form-control" value="{{ old('contention_ratio', $package->contention_ratio) }}" min="1"></div></div>
                    <div class="col-md-3"><div class="form-group"><label>Billing Cycle Type</label><input type="text" name="billing_cycle_type" class="form-control" value="{{ old('billing_cycle_type', $package->billing_cycle_type) }}"></div></div>

                    <div class="col-md-3"><div class="form-group"><label>Monthly Price</label><input type="number" step="0.01" name="monthly_price" class="form-control" value="{{ old('monthly_price', $package->monthly_price) }}" min="0" required></div></div>
                    <div class="col-md-3"><div class="form-group"><label>Setup Fee</label><input type="number" step="0.01" name="setup_fee" class="form-control" value="{{ old('setup_fee', $package->setup_fee) }}" min="0"></div></div>
                    <div class="col-md-3"><div class="form-group"><label>Billing Cycle Days</label><input type="number" name="billing_cycle_days" class="form-control" value="{{ old('billing_cycle_days', $package->billing_cycle_days) }}" min="1" max="365"></div></div>

                    <div class="col-md-12"><div class="form-group"><label>Description</label><textarea name="description" rows="3" class="form-control">{{ old('description', $package->description) }}</textarea></div></div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between"><a href="{{ route('packages.show', $package) }}" class="btn btn-secondary">Cancel</a><button class="btn btn-primary">Save</button></div>
        </div>
    </form>
@stop
