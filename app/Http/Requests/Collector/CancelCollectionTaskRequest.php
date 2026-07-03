<?php

namespace App\Http\Requests\Collector;

use App\Models\CollectionTask;
use Illuminate\Foundation\Http\FormRequest;

class CancelCollectionTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('cancel', $this->route('collection_task'));
    }

    public function rules(): array
    {
        return [
            'cancellation_reason' => ['required', 'string', 'max:5000'],
        ];
    }
}