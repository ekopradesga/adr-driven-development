<?php

namespace App\Http\Requests\WireRouter;

use Illuminate\Foundation\Http\FormRequest;

class ActivateWireRouterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}