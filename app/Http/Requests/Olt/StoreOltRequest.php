<?php

namespace App\Http\Requests\Olt;

use App\Models\Olt;
use Illuminate\Foundation\Http\FormRequest;

class StoreOltRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Olt::class);
    }

    public function rules(): array
    {
        return [
            'olt_code' => ['required', 'string', 'max:50', 'unique:olts,olt_code'],
            'name' => ['required', 'string', 'max:255'],
            'vendor' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'ip_address' => ['required', 'ip', 'unique:olts,ip_address'],
            'snmp_community' => ['nullable', 'string', 'max:255'],
            'api_username' => ['nullable', 'string', 'max:100'],
            'api_password' => ['nullable', 'string', 'max:255'],
            'location_name' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'last_seen_at' => ['nullable', 'date'],
        ];
    }
}
