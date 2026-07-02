@extends('adminlte::page')

@section('title', 'Edit — ' . $customer->customer_number)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0">Edit Customer</h1>
            <small class="text-muted">{{ $customer->customer_number }} — {{ $customer->name }}</small>
        </div>
        <a href="{{ route('customers.show', $customer) }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-warning card-outline">
                <div class="card-header"><h3 class="card-title">Customer Details</h3></div>
                <form method="POST" action="{{ route('customers.update', $customer) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">

                        {{-- Identity --}}
                        <h6 class="text-muted text-uppercase font-weight-bold mb-3" style="letter-spacing:.05em">Identity</h6>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="name">Full Name / Company Name <span class="text-danger">*</span></label>
                                    <input id="name" type="text" name="name"
                                           class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name', $customer->name) }}" required autofocus>
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
                                                {{ old('customer_type', $customer->customer_type->value) === $type->value ? 'selected' : '' }}>
                                                {{ $type->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('customer_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="text-muted">Customer Number</label>
                            <input type="text" class="form-control-plaintext pl-2" value="{{ $customer->customer_number }}" readonly>
                            <small class="form-text text-muted">Customer number is immutable after creation.</small>
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
                                           value="{{ old('phone', $customer->phone) }}">
                                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="whatsapp_phone">WhatsApp</label>
                                    <input id="whatsapp_phone" type="text" name="whatsapp_phone"
                                           class="form-control @error('whatsapp_phone') is-invalid @enderror"
                                           value="{{ old('whatsapp_phone', $customer->whatsapp_phone) }}">
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
                                           value="{{ old('alt_phone', $customer->alt_phone) }}">
                                    @error('alt_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input id="email" type="email" name="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email', $customer->email) }}">
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
                                      class="form-control @error('address') is-invalid @enderror">{{ old('address', $customer->address) }}</textarea>
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="latitude">Latitude</label>
                                    <input id="latitude" type="number" name="latitude" step="any"
                                           class="form-control @error('latitude') is-invalid @enderror"
                                           value="{{ old('latitude', $customer->latitude) }}">
                                    @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="longitude">Longitude</label>
                                    <input id="longitude" type="number" name="longitude" step="any"
                                           class="form-control @error('longitude') is-invalid @enderror"
                                           value="{{ old('longitude', $customer->longitude) }}">
                                    @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h6 class="text-muted text-uppercase font-weight-bold mb-3" style="letter-spacing:.05em">Notes</h6>

                        <div class="form-group">
                            <label for="notes">Internal Notes</label>
                            <textarea id="notes" name="notes" rows="2"
                                      class="form-control @error('notes') is-invalid @enderror"
                                      placeholder="Internal notes (not visible to customer)">{{ old('notes', $customer->notes) }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save mr-1"></i> Save Changes
                        </button>
                        <a href="{{ route('customers.show', $customer) }}" class="btn btn-secondary ml-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-light card-outline">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Current Status</h3></div>
                <div class="card-body">
                    <p>
                        Account status:
                        <span class="badge badge-{{ $customer->status->badgeColor() }} ml-1">
                            {{ $customer->status->label() }}
                        </span>
                    </p>
                    <p class="text-muted small mb-0">
                        Status transitions (suspend, reactivate, terminate) are available on the
                        <a href="{{ route('customers.show', $customer) }}">Customer 360</a> page.
                    </p>
                </div>
            </div>
        </div>
    </div>
@stop
