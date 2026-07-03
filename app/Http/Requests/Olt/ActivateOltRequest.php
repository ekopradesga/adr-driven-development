<?php

namespace App\Http\Requests\Olt;

use Illuminate\Foundation\Http\FormRequest;

class ActivateOltRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('activate', $this->route('olt'));
    }

    public function rules(): array
    {
        return [];
    }
}
