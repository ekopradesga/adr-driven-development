<?php

namespace App\Http\Requests\ServiceArea;

use Illuminate\Foundation\Http\FormRequest;

class ActivateServiceAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('activate', $this->route('service_area'));
    }

    public function rules(): array
    {
        return [];
    }
}