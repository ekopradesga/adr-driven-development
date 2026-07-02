<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class SuspendSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('suspend', $this->route('subscription'));
    }

    public function rules(): array
    {
        return [
            'suspension_type'  => ['required', 'in:overdue,manual'],
            'suspension_reason'=> ['required', 'string', 'min:5', 'max:1000'],
        ];
    }
}
