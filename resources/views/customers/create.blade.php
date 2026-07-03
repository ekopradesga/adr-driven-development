@extends('adminlte::page')

@section('title', 'New Customer')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>New Customer</h1>
        <a href="{{ route('customers.index') }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary card-outline">
                <div class="card-header"><h3 class="card-title">Customer Details</h3></div>
                <form method="POST" action="{{ route('customers.store') }}">
                    @csrf
                    <div class="card-body">

                        {{-- Identity --}}
                        <h6 class="text-muted text-uppercase font-weight-bold mb-3" style="letter-spacing:.05em">Identity</h6>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="name">Full Name / Company Name <span class="text-danger">*</span></label>
                                    <input id="name" type="text" name="name"
                                           class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name') }}" required autofocus>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="customer_type">Customer Type <span class="text-danger">*</span></label>
                                    <select id="customer_type" name="customer_type"
                                            class="form-control @error('customer_type') is-invalid @enderror">
                                        @foreach ($types as $type)
                                            <option value="{{ $type->value }}"
                                                {{ old('customer_type', \App\Enums\CustomerType::Individual->value) === $type->value ? 'selected' : '' }}>
                                                {{ $type->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('customer_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <hr>

                        {{-- Contact --}}
                        <h6 class="text-muted text-uppercase font-weight-bold mb-3" style="letter-spacing:.05em">Contact</h6>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input id="phone" type="text" name="phone"
                                           class="form-control @error('phone') is-invalid @enderror"
                                           value="{{ old('phone') }}">
                                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="whatsapp_phone">WhatsApp</label>
                                    <input id="whatsapp_phone" type="text" name="whatsapp_phone"
                                           class="form-control @error('whatsapp_phone') is-invalid @enderror"
                                           value="{{ old('whatsapp_phone') }}">
                                    @error('whatsapp_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="alt_phone">Alternative Phone</label>
                                    <input id="alt_phone" type="text" name="alt_phone"
                                           class="form-control @error('alt_phone') is-invalid @enderror"
                                           value="{{ old('alt_phone') }}">
                                    @error('alt_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input id="email" type="email" name="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email') }}">
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <hr>

                        {{-- Service Location --}}
                        <h6 class="text-muted text-uppercase font-weight-bold mb-3" style="letter-spacing:.05em">Service Location</h6>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" rows="2"
                                      class="form-control @error('address') is-invalid @enderror">{{ old('address') }}</textarea>
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="latitude">Latitude</label>
                                    <input id="latitude" type="number" name="latitude" step="any"
                                           class="form-control @error('latitude') is-invalid @enderror"
                                           value="{{ old('latitude') }}" placeholder="-6.200000">
                                    @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="longitude">Longitude</label>
                                    <input id="longitude" type="number" name="longitude" step="any"
                                           class="form-control @error('longitude') is-invalid @enderror"
                                           value="{{ old('longitude') }}" placeholder="106.816666">
                                    @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cluster_id">Cluster</label>
                                    <select id="cluster_id" name="cluster_id"
                                            class="form-control @error('cluster_id') is-invalid @enderror">
                                        <option value="">Select cluster...</option>
                                        @foreach ($clusters as $cluster)
                                            <option value="{{ $cluster->id }}" {{ (string) old('cluster_id') === (string) $cluster->id ? 'selected' : '' }}>
                                                {{ $cluster->code }} — {{ $cluster->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('cluster_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="service_area_id">Service Area</label>
                                    <select id="service_area_id" name="service_area_id"
                                            class="form-control @error('service_area_id') is-invalid @enderror">
                                        <option value="">Select service area...</option>
                                        @foreach ($serviceAreas as $serviceArea)
                                            <option value="{{ $serviceArea->id }}" {{ (string) old('service_area_id') === (string) $serviceArea->id ? 'selected' : '' }}>
                                                {{ $serviceArea->code }} — {{ $serviceArea->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('service_area_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <hr>

                        {{-- Notes --}}
                        <h6 class="text-muted text-uppercase font-weight-bold mb-3" style="letter-spacing:.05em">Notes</h6>

                        <div class="form-group">
                            <label for="notes">Internal Notes</label>
                            <textarea id="notes" name="notes" rows="2"
                                      class="form-control @error('notes') is-invalid @enderror"
                                      placeholder="Internal notes (not visible to customer)">{{ old('notes') }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Create Customer
                        </button>
                        <a href="{{ route('customers.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-light card-outline">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Help</h3></div>
                <div class="card-body text-sm">
                    <p>New customers are created in <strong>Prospect</strong> status.</p>
                    <p>A customer number (<code>CUST-XXXXXX</code>) is automatically generated.</p>
                    <p>The customer account transitions to <strong>Active</strong> when their first subscription is activated.</p>
                    <p class="mb-0 text-muted">At least one contact field (phone or email) is recommended for notification delivery.</p>
                </div>
            </div>
        </div>
    </div>
@stop
