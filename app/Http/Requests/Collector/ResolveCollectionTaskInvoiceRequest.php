<?php

namespace App\Http\Requests\Collector;

use App\Models\CollectionTaskInvoice;
use Illuminate\Foundation\Http\FormRequest;

class ResolveCollectionTaskInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('collection_task_invoice'));
    }

    public function rules(): array
    {
        return [
            'resolution_outcome' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}