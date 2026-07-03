<?php

namespace App\Http\Requests\Fat;

use Illuminate\Foundation\Http\FormRequest;

class RetireFatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('retire', $this->route('fat'));
    }

    public function rules(): array
    {
        return [];
    }
}
