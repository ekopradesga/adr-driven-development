<?php

namespace App\Http\Requests\Collector;

use App\Models\CollectionTask;
use Illuminate\Foundation\Http\FormRequest;

class FollowUpCollectionTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('followUpRequired', $this->route('collection_task'));
    }

    public function rules(): array
    {
        return [
            'follow_up_reason' => ['required', 'string', 'max:5000'],
        ];
    }
}