<?php

namespace App\Http\Requests\Collector;

use App\Models\CollectionTask;
use Illuminate\Foundation\Http\FormRequest;

class CompleteCollectionTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('complete', $this->route('collection_task'));
    }

    public function rules(): array
    {
        return [];
    }
}