<?php

namespace App\Http\Requests\ServiceArea;

use Illuminate\Foundation\Http\FormRequest;

class InactivateClusterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('inactivate', $this->route('cluster'));
    }

    public function rules(): array
    {
        return [];
    }
}