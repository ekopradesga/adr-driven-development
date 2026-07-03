<?php

namespace App\Http\Requests\Package;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('package'));
    }

    public function rules(): array
    {
        $packageId = $this->route('package')?->id;

        return [
            'package_code' => ['required', 'string', 'max:50', Rule::unique('packages', 'package_code')->ignore($packageId)],
            'name' => ['required', 'string', 'max:255'],
            'downstream_kbps' => ['required', 'integer', 'min:1'],
            'upstream_kbps' => ['required', 'integer', 'min:1'],
            'contention_ratio' => ['nullable', 'integer', 'min:1'],
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'setup_fee' => ['nullable', 'numeric', 'min:0'],
            'billing_cycle_type' => ['nullable', 'string', 'max:30'],
            'billing_cycle_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'description' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
