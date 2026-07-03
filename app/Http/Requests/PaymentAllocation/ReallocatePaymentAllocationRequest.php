<?php

namespace App\Http\Requests\PaymentAllocation;

use Illuminate\Foundation\Http\FormRequest;

class ReallocatePaymentAllocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('reallocate', $this->route('allocation'));
    }

    public function rules(): array
    {
        return [
            'invoice_id' => ['required', 'integer', 'exists:invoices,id'],
            'allocated_amount' => ['required', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
