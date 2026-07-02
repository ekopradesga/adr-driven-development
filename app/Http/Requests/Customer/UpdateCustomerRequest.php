<?php

namespace App\Http\Requests\Customer;

use App\Enums\CustomerType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('customer'));
    }

    public function rules(): array
    {
        $customerId = $this->route('customer')?->id;

        return [
            'name'           => ['required', 'string', 'max:255'],
            'customer_type'  => ['required', new Enum(CustomerType::class)],
            'email'          => ['nullable', 'email', 'max:255', "unique:customers,email,{$customerId}"],
            'phone'          => ['nullable', 'string', 'max:30'],
            'whatsapp_phone' => ['nullable', 'string', 'max:30'],
            'alt_phone'      => ['nullable', 'string', 'max:30'],
            'address'        => ['nullable', 'string', 'max:1000'],
            'latitude'       => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'      => ['nullable', 'numeric', 'between:-180,180'],
            'notes'          => ['nullable', 'string', 'max:5000'],
            'cluster_id'     => ['nullable', 'integer', 'exists:clusters,id'],
            'service_area_id'=> ['nullable', 'integer', 'exists:service_areas,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'A customer with this email address already exists.',
        ];
    }
}
