<?php

namespace App\Http\Requests\PaymentAllocation;

use App\Models\PaymentAllocation;
use Illuminate\Foundation\Http\FormRequest;

class ReversePaymentAllocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('reverse', $this->route('allocation'));
    }

    public function rules(): array
    {
        return [
            'reversal_reason' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }
}
