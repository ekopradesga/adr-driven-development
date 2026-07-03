<?php

namespace App\Http\Requests\Package;

use Illuminate\Foundation\Http\FormRequest;

class RetirePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('retire', $this->route('package'));
    }

    public function rules(): array
    {
        return [];
    }
}
