<?php

namespace App\Http\Requests\Fat;

use Illuminate\Foundation\Http\FormRequest;

class MaintenanceFatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('maintenance', $this->route('fat'));
    }

    public function rules(): array
    {
        return [];
    }
}
