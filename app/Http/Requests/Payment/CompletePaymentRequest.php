<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class CompletePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('complete', $this->route('payment'));
    }

    public function rules(): array
    {
        return [];
    }
}
