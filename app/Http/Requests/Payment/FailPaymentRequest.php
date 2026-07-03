<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class FailPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('fail', $this->route('payment'));
    }

    public function rules(): array
    {
        return [
            'failure_reason' => ['required', 'string', 'min:5', 'max:5000'],
        ];
    }
}
