<?php

namespace App\Http\Requests\ServiceArea;

use App\Enums\ServiceAreaLevel;
use App\Enums\ServiceAreaStatus;
use App\Models\ServiceArea;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', ServiceArea::class);
    }

    public function rules(): array
    {
        return [
            'cluster_id' => ['required', 'integer', 'exists:clusters,id'],
            'parent_id' => ['nullable', 'integer', 'exists:service_areas,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:service_areas,code'],
            'level' => ['required', Rule::in(ServiceAreaLevel::values())],
            'boundary_geojson' => ['nullable', 'string'],
            'center_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'center_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'status' => ['nullable', Rule::in(ServiceAreaStatus::values())],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}