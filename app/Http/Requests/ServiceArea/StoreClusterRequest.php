<?php

namespace App\Http\Requests\ServiceArea;

use App\Models\Cluster;
use Illuminate\Foundation\Http\FormRequest;

class StoreClusterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Cluster::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:clusters,code'],
            'description' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}