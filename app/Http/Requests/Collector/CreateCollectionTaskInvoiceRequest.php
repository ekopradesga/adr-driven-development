<?php

namespace App\Http\Requests\Collector;

use App\Models\CollectionTask;
use Illuminate\Foundation\Http\FormRequest;

class CreateCollectionTaskInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', CollectionTask::class);
    }

    public function rules(): array
    {
        return [
            'invoice_id' => ['required', 'integer', 'exists:invoices,id'],
            'inclusion_reason' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}