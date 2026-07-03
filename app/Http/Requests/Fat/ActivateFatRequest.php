<?php

namespace App\Http\Requests\Fat;

use Illuminate\Foundation\Http\FormRequest;

class ActivateFatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('activate', $this->route('fat'));
    }

    public function rules(): array
    {
        return [];
    }
}
