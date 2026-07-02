@extends('adminlte::page')

@section('title', 'Edit Subscription #' . $subscription->id)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0">Edit Subscription #{{ $subscription->id }}</h1>
            <small class="text-muted">{{ $subscription->customer?->customer_number }} — {{ $subscription->customer?->name }}</small>
        </div>
        <a href="{{ route('subscriptions.show', $subscription) }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-warning card-outline">
                <div class="card-header"><h3 class="card-title">Subscription Details</h3></div>
                <form method="POST" action="{{ route('subscriptions.update', $subscription) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">

                        <div class="form-group">
                            <label>Customer</label>
                            <input type="text" class="form-control-plaintext pl-2"
                                   value="{{ $subscription->customer?->customer_number }} — {{ $subscription->customer?->name }}" readonly>
                        </div>

                        <div class="form-group">
                            <label for="package_id">Package <span class="text-danger">*</span></label>
                            <select id="package_id" name="package_id" class="form-control @error('package_id') is-invalid @enderror" required>
                                @foreach ($packages as $package)
                                    <option value="{{ $package->id }}"
                                        {{ old('package_id', $subscription->package_id) == $package->id ? 'selected' : '' }}>
                                        {{ $package->name }}
                                        @if ($package->monthly_price)
                                            — Rp {{ number_format($package->monthly_price, 0, ',', '.') }}/mo
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('package_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="subscription_type">Type</label>
                                    <select id="subscription_type" name="subscription_type" class="form-control @error('subscription_type') is-invalid @enderror">
                                        @foreach ($types as $type)
                                            <option value="{{ $type->value }}"
                                                {{ old('subscription_type', $subscription->subscription_type->value) === $type->value ? 'selected' : '' }}>
                                                {{ $type->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('subscription_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="billing_day">Billing Day <span class="text-danger">*</span></label>
                                    <input id="billing_day" type="number" name="billing_day" min="1" max="28"
                                           class="form-control @error('billing_day') is-invalid @enderror"
                                           value="{{ old('billing_day', $subscription->billing_day) }}" required>
                                    @error('billing_day')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea id="notes" name="notes" rows="2"
                                      class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $subscription->notes) }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning"><i class="fas fa-save mr-1"></i> Save Changes</button>
                        <a href="{{ route('subscriptions.show', $subscription) }}" class="btn btn-secondary ml-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-light card-outline">
                <div class="card-header"><h3 class="card-title">Current Status</h3></div>
                <div class="card-body">
                    <span class="badge badge-{{ $subscription->status->badgeColor() }}">{{ $subscription->status->label() }}</span>
                    <p class="text-muted small mt-2 mb-0">
                        Status transitions (activate, suspend, reactivate, terminate) are available on the
                        <a href="{{ route('subscriptions.show', $subscription) }}">Subscription detail</a> page.
                    </p>
                </div>
            </div>
        </div>
    </div>
@stop
