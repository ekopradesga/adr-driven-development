<?php

namespace App\Http\Requests\Olt;

use Illuminate\Foundation\Http\FormRequest;

class RetireOltRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('retire', $this->route('olt'));
    }

    public function rules(): array
    {
        return [];
    }
}
