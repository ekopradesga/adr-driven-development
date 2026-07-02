<?php

namespace App\Http\Requests\Invoice;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Invoice::class);
    }

    public function rules(): array
    {
        return [
            'customer_id'     => ['required', 'integer', 'exists:customers,id'],
            'subscription_id'  => ['required', 'integer', 'exists:subscriptions,id'],
            'period_start'    => ['required', 'date'],
            'period_end'      => ['required', 'date', 'after_or_equal:period_start'],
            'issue_date'      => ['required', 'date'],
            'due_date'        => ['required', 'date', 'after_or_equal:issue_date'],
            'tax_amount'      => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items'           => ['nullable', 'array'],
            'items.*.description'  => ['required_with:items', 'string', 'max:255'],
            'items.*.item_type'    => ['nullable', 'string', 'max:30'],
            'items.*.quantity'     => ['nullable', 'integer', 'min:1'],
            'items.*.unit_price'   => ['required_with:items', 'numeric', 'min:0'],
            'items.*.total_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.sort_order'   => ['nullable', 'integer', 'min:0'],
            'items.*.notes'        => ['nullable', 'string', 'max:5000'],
            'notes'           => ['nullable', 'string', 'max:5000'],
        ];
    }
}
