<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class ValidatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('validatePayment', $this->route('payment'));
    }

    public function rules(): array
    {
        return [];
    }
}
