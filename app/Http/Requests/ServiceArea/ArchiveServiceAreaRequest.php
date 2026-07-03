<?php

namespace App\Http\Requests\ServiceArea;

use Illuminate\Foundation\Http\FormRequest;

class ArchiveServiceAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('archive', $this->route('service_area'));
    }

    public function rules(): array
    {
        return [];
    }
}