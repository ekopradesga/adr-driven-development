<?php

namespace App\Http\Requests\Invoice;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;

class CancelInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('cancel', $this->route('invoice'));
    }

    public function rules(): array
    {
        return [
            'cancellation_reason' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }
}
