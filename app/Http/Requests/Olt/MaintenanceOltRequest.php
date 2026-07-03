<?php

namespace App\Http\Requests\Olt;

use Illuminate\Foundation\Http\FormRequest;

class MaintenanceOltRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('maintenance', $this->route('olt'));
    }

    public function rules(): array
    {
        return [];
    }
}
