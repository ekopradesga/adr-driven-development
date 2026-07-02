<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class TerminateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('terminate', $this->route('subscription'));
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }
}
