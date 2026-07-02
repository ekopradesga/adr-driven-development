@extends('adminlte::page')

@section('title', 'Create Invoice')

@section('content_header')
    <h1>Create Invoice</h1>
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('invoices.store') }}">
        @csrf
        <div class="card card-outline card-primary">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Customer ID</label>
                            <input type="number" name="customer_id" value="{{ old('customer_id') }}" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Subscription ID</label>
                            <input type="number" name="subscription_id" value="{{ old('subscription_id') }}" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-4"><div class="form-group"><label>Period Start</label><input type="date" name="period_start" value="{{ old('period_start') }}" class="form-control" required></div></div>
                    <div class="col-md-4"><div class="form-group"><label>Period End</label><input type="date" name="period_end" value="{{ old('period_end') }}" class="form-control" required></div></div>
                    <div class="col-md-4"><div class="form-group"><label>Issue Date</label><input type="date" name="issue_date" value="{{ old('issue_date', now()->toDateString()) }}" class="form-control" required></div></div>
                    <div class="col-md-4"><div class="form-group"><label>Due Date</label><input type="date" name="due_date" value="{{ old('due_date') }}" class="form-control" required></div></div>
                    <div class="col-md-4"><div class="form-group"><label>Subtotal</label><input type="number" step="0.01" name="subtotal_amount" value="{{ old('subtotal_amount', 0) }}" class="form-control"></div></div>
                    <div class="col-md-4"><div class="form-group"><label>Tax</label><input type="number" step="0.01" name="tax_amount" value="{{ old('tax_amount', 0) }}" class="form-control"></div></div>
                    <div class="col-md-4"><div class="form-group"><label>Discount</label><input type="number" step="0.01" name="discount_amount" value="{{ old('discount_amount', 0) }}" class="form-control"></div></div>
                    <div class="col-md-12"><div class="form-group"><label>Notes</label><textarea name="notes" rows="4" class="form-control">{{ old('notes') }}</textarea></div></div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('invoices.index') }}" class="btn btn-secondary">Cancel</a>
                <button class="btn btn-primary">Generate Invoice</button>
            </div>
        </div>
    </form>
@stop
