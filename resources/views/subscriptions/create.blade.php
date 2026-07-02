@extends('adminlte::page')

@section('title', 'New Subscription')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>New Subscription</h1>
        <a href="{{ route('subscriptions.index') }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary card-outline">
                <div class="card-header"><h3 class="card-title">Subscription Details</h3></div>
                <form method="POST" action="{{ route('subscriptions.store') }}">
                    @csrf
                    <div class="card-body">

                        <div class="form-group">
                            <label for="customer_id">Customer <span class="text-danger">*</span></label>
                            <select id="customer_id" name="customer_id" class="form-control @error('customer_id') is-invalid @enderror" required>
                                <option value="">— Select customer —</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}"
                                        {{ old('customer_id', $prefill['customer_id'] ?? '') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->customer_number }} — {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label for="package_id">Package <span class="text-danger">*</span></label>
                            <select id="package_id" name="package_id" class="form-control @error('package_id') is-invalid @enderror" required>
                                <option value="">— Select package —</option>
                                @foreach ($packages as $package)
                                    <option value="{{ $package->id }}" {{ old('package_id') == $package->id ? 'selected' : '' }}>
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
                                    <label for="subscription_type">Type <span class="text-danger">*</span></label>
                                    <select id="subscription_type" name="subscription_type" class="form-control @error('subscription_type') is-invalid @enderror">
                                        @foreach ($types as $type)
                                            <option value="{{ $type->value }}"
                                                {{ old('subscription_type', \App\Enums\SubscriptionType::Primary->value) === $type->value ? 'selected' : '' }}>
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
                                           value="{{ old('billing_day', 1) }}" required>
                                    <small class="form-text text-muted">Day of month (1–28) when invoices are generated.</small>
                                    @error('billing_day')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea id="notes" name="notes" rows="2"
                                      class="form-control @error('notes') is-invalid @enderror"
                                      placeholder="Internal notes…">{{ old('notes') }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Create Subscription</button>
                        <a href="{{ route('subscriptions.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-light card-outline">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Help</h3></div>
                <div class="card-body text-sm">
                    <p>New subscriptions start in <strong>Pending</strong> status.</p>
                    <p>Activate the subscription after installation and provisioning are complete.</p>
                    <p class="mb-0 text-muted">Only one active primary subscription per customer is allowed.</p>
                </div>
            </div>
        </div>
    </div>
@stop
