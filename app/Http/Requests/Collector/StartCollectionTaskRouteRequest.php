<?php

namespace App\Http\Requests\Collector;

use App\Models\CollectionTask;
use Illuminate\Foundation\Http\FormRequest;

class StartCollectionTaskRouteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('startRoute', $this->route('collection_task'));
    }

    public function rules(): array
    {
        return [];
    }
}