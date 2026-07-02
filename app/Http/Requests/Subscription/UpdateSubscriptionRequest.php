<?php

namespace App\Http\Requests\Subscription;

use App\Enums\SubscriptionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('subscription'));
    }

    public function rules(): array
    {
        return [
            'package_id'        => ['required', 'integer', 'exists:packages,id'],
            'subscription_type' => ['required', new Enum(SubscriptionType::class)],
            'billing_day'       => ['required', 'integer', 'between:1,28'],
            'notes'             => ['nullable', 'string', 'max:5000'],
        ];
    }
}
