<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class ReversePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('reverse', $this->route('payment'));
    }

    public function rules(): array
    {
        return [
            'reversal_reason' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }
}
