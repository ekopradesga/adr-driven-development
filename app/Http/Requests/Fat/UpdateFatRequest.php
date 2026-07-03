<?php

namespace App\Http\Requests\Fat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('fat'));
    }

    public function rules(): array
    {
        $fatId = $this->route('fat')?->id;

        return [
            'odf_id' => ['required', 'integer', 'exists:odfs,id'],
            'service_area_id' => ['nullable', 'integer', 'exists:service_areas,id'],
            'fat_code' => ['required', 'string', 'max:50', Rule::unique('fats', 'fat_code')->ignore($fatId)],
            'name' => ['required', 'string', 'max:255'],
            'capacity_ports' => ['required', 'integer', 'min:1'],
            'used_ports' => ['nullable', 'integer', 'min:0'],
            'splitter_ratio' => ['nullable', 'string', 'max:30'],
            'location_name' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'last_onu_ping_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
