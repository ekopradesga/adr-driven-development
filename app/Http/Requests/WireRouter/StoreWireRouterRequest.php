<?php

namespace App\Http\Requests\WireRouter;

use App\Enums\WireRouterType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWireRouterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'router_code' => ['required', 'string', 'max:50', 'unique:routers,router_code'],
            'name' => ['required', 'string', 'max:255'],
            'router_type' => ['required', Rule::enum(WireRouterType::class)],
            'vendor' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'ip_address' => ['required', 'ip', 'max:45', 'unique:routers,ip_address'],
            'snmp_community' => ['nullable', 'string', 'max:255'],
            'api_username' => ['nullable', 'string', 'max:100'],
            'api_password' => ['nullable', 'string', 'max:255'],
            'location_name' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'parent_router_id' => ['nullable', 'exists:routers,id', Rule::requiredIf(fn () => $this->input('router_type') === WireRouterType::Distribution->value)],
            'last_seen_at' => ['nullable', 'date'],
        ];
    }
}