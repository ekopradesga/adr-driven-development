<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReceivePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('receive', $this->route('payment'));
    }

    public function rules(): array
    {
        return [
            'payment_date' => ['nullable', 'date'],
            'amount' => ['nullable', 'numeric', 'gt:0'],
            'method' => ['nullable', Rule::in(['cash', 'bank_transfer', 'va', 'qris', 'card', 'other'])],
            'channel_reference' => ['nullable', 'string', 'max:100'],
            'received_by' => ['nullable', 'integer', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
