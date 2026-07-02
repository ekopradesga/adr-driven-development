<?php

namespace App\Http\Requests\Invoice;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;

class PublishInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('publish', $this->route('invoice'));
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
