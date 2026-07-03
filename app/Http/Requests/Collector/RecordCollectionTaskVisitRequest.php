<?php

namespace App\Http\Requests\Collector;

use App\Models\CollectionTask;
use Illuminate\Foundation\Http\FormRequest;

class RecordCollectionTaskVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('recordVisit', $this->route('collection_task'));
    }

    public function rules(): array
    {
        return [
            'payment_collected_amount' => ['nullable', 'numeric', 'gte:0'],
            'payment_submission_reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}