<?php

namespace App\Http\Requests\Collector;

use App\Models\CollectionTask;
use Illuminate\Foundation\Http\FormRequest;

class StoreCollectionTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', CollectionTask::class);
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'scheduled_for' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}