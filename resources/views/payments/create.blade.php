@extends('adminlte::page')

@section('title', 'Create Payment')

@section('content_header')
    <h1>Create Payment Intent</h1>
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('payments.store') }}">
        @csrf
        <div class="card card-outline card-primary">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6"><div class="form-group"><label>Customer ID</label><input type="number" name="customer_id" value="{{ old('customer_id') }}" class="form-control" required></div></div>
                    <div class="col-md-3"><div class="form-group"><label>Payment Date</label><input type="date" name="payment_date" value="{{ old('payment_date', now()->toDateString()) }}" class="form-control" required></div></div>
                    <div class="col-md-3"><div class="form-group"><label>Amount</label><input type="number" step="0.01" name="amount" value="{{ old('amount') }}" class="form-control" required></div></div>
                    <div class="col-md-3"><div class="form-group"><label>Currency</label><input type="text" name="currency" maxlength="3" value="{{ old('currency', 'IDR') }}" class="form-control"></div></div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Method</label>
                            <select name="method" class="form-control" required>
                                @foreach (['cash','bank_transfer','va','qris','card','other'] as $method)
                                    <option value="{{ $method }}" {{ old('method') === $method ? 'selected' : '' }}>{{ strtoupper(str_replace('_', ' ', $method)) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6"><div class="form-group"><label>Channel Reference</label><input type="text" name="channel_reference" value="{{ old('channel_reference') }}" class="form-control"></div></div>
                    <div class="col-md-12"><div class="form-group"><label>Notes</label><textarea name="notes" rows="4" class="form-control">{{ old('notes') }}</textarea></div></div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('payments.index') }}" class="btn btn-secondary">Cancel</a>
                <button class="btn btn-primary">Create Intent</button>
            </div>
        </div>
    </form>
@stop
