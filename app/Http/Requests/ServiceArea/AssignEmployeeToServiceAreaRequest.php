<?php

namespace App\Http\Requests\ServiceArea;

use Illuminate\Foundation\Http\FormRequest;

class AssignEmployeeToServiceAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('assignEmployee', $this->route('service_area'));
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'is_primary' => ['nullable', 'boolean'],
        ];
    }
}