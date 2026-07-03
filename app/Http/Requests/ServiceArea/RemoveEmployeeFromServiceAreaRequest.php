<?php

namespace App\Http\Requests\ServiceArea;

use Illuminate\Foundation\Http\FormRequest;

class RemoveEmployeeFromServiceAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('removeEmployee', $this->route('service_area'));
    }

    public function rules(): array
    {
        return [];
    }
}