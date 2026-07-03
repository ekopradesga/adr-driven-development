<?php

namespace App\Http\Requests\Package;

use Illuminate\Foundation\Http\FormRequest;

class DeprecatePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('deprecate', $this->route('package'));
    }

    public function rules(): array
    {
        return [];
    }
}
