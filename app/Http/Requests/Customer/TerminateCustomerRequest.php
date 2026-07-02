<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class TerminateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('terminate', $this->route('customer'));
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'A termination reason is required.',
            'reason.min'      => 'The termination reason must be at least 10 characters.',
        ];
    }
}
