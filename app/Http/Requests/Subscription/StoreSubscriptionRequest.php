<?php

namespace App\Http\Requests\Subscription;

use App\Enums\SubscriptionType;
use App\Models\Subscription;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Subscription::class);
    }

    public function rules(): array
    {
        return [
            'customer_id'       => ['required', 'integer', 'exists:customers,id'],
            'package_id'        => ['required', 'integer', 'exists:packages,id'],
            'subscription_type' => ['required', new Enum(SubscriptionType::class)],
            'billing_day'       => ['required', 'integer', 'between:1,28'],
            'notes'             => ['nullable', 'string', 'max:5000'],
        ];
    }
}
