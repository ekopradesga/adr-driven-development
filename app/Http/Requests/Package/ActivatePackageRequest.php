<?php

namespace App\Http\Requests\Package;

use Illuminate\Foundation\Http\FormRequest;

class ActivatePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('activate', $this->route('package'));
    }

    public function rules(): array
    {
        return [];
    }
}
