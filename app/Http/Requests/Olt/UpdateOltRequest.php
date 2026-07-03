<?php

namespace App\Http\Requests\Olt;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOltRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('olt'));
    }

    public function rules(): array
    {
        $oltId = $this->route('olt')?->id;

        return [
            'olt_code' => ['required', 'string', 'max:50', Rule::unique('olts', 'olt_code')->ignore($oltId)],
            'name' => ['required', 'string', 'max:255'],
            'vendor' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'ip_address' => ['required', 'ip', Rule::unique('olts', 'ip_address')->ignore($oltId)],
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
