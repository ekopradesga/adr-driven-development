<?php

namespace App\Http\Requests\ServiceArea;

use App\Enums\ServiceAreaLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('service_area'));
    }

    public function rules(): array
    {
        $serviceAreaId = $this->route('service_area')?->id;

        return [
            'cluster_id' => ['required', 'integer', 'exists:clusters,id'],
            'parent_id' => ['nullable', 'integer', 'exists:service_areas,id', Rule::notIn([$serviceAreaId])],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('service_areas', 'code')->ignore($serviceAreaId)],
            'level' => ['required', Rule::in(ServiceAreaLevel::values())],
            'boundary_geojson' => ['nullable', 'string'],
            'center_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'center_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}