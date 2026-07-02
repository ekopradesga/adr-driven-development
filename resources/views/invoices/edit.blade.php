@extends('adminlte::page')

@section('title', 'Edit Invoice')

@section('content_header')
    <h1>Edit Invoice {{ $invoice->invoice_number }}</h1>
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('invoices.update', $invoice) }}">
        @csrf
        @method('PUT')
        <div class="card card-outline card-warning">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info">Only draft invoices may be edited.</div>
                    </div>
                    <div class="col-md-4"><div class="form-group"><label>Period Start</label><input type="date" name="period_start" value="{{ old('period_start', optional($invoice->period_start)->format('Y-m-d')) }}" class="form-control" required></div></div>
                    <div class="col-md-4"><div class="form-group"><label>Period End</label><input type="date" name="period_end" value="{{ old('period_end', optional($invoice->period_end)->format('Y-m-d')) }}" class="form-control" required></div></div>
                    <div class="col-md-4"><div class="form-group"><label>Issue Date</label><input type="date" name="issue_date" value="{{ old('issue_date', optional($invoice->issue_date)->format('Y-m-d')) }}" class="form-control" required></div></div>
                    <div class="col-md-4"><div class="form-group"><label>Due Date</label><input type="date" name="due_date" value="{{ old('due_date', optional($invoice->due_date)->format('Y-m-d')) }}" class="form-control" required></div></div>
                    <div class="col-md-4"><div class="form-group"><label>Tax</label><input type="number" step="0.01" name="tax_amount" value="{{ old('tax_amount', $invoice->tax_amount) }}" class="form-control"></div></div>
                    <div class="col-md-4"><div class="form-group"><label>Discount</label><input type="number" step="0.01" name="discount_amount" value="{{ old('discount_amount', $invoice->discount_amount) }}" class="form-control"></div></div>
                    <div class="col-md-12"><div class="form-group"><label>Notes</label><textarea name="notes" rows="4" class="form-control">{{ old('notes', $invoice->notes) }}</textarea></div></div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-secondary">Cancel</a>
                <button class="btn btn-warning">Save Changes</button>
            </div>
        </div>
    </form>
@stop
