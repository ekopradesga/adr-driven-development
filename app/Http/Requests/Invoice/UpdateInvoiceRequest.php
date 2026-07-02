<?php

namespace App\Http\Requests\Invoice;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('invoice'));
    }

    public function rules(): array
    {
        return [
            'period_start'    => ['required', 'date'],
            'period_end'      => ['required', 'date', 'after_or_equal:period_start'],
            'issue_date'      => ['required', 'date'],
            'due_date'        => ['required', 'date', 'after_or_equal:issue_date'],
            'tax_amount'      => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'notes'           => ['nullable', 'string', 'max:5000'],
        ];
    }
}
