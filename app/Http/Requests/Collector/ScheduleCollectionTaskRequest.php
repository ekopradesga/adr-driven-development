<?php

namespace App\Http\Requests\Collector;

use App\Models\CollectionTask;
use Illuminate\Foundation\Http\FormRequest;

class ScheduleCollectionTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('schedule', $this->route('collection_task'));
    }

    public function rules(): array
    {
        return [
            'scheduled_for' => ['required', 'date'],
        ];
    }
}