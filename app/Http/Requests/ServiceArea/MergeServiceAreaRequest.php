<?php

namespace App\Http\Requests\ServiceArea;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MergeServiceAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('merge', $this->route('service_area'));
    }

    public function rules(): array
    {
        return [
            'merged_into_service_area_id' => [
                'required',
                'integer',
                'exists:service_areas,id',
                Rule::notIn([$this->route('service_area')?->id]),
            ],
        ];
    }
}