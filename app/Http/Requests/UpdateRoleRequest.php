<?php

namespace App\Http\Requests;

use App\Enums\RoleStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('role.update');
    }

    public function rules(): array
    {
        $roleId = $this->route('role')->id;

        return [
            'name'        => ['sometimes', 'string', 'max:100', "unique:roles,name,{$roleId}"],
            'slug'        => ['sometimes', 'string', 'max:100', "unique:roles,slug,{$roleId}"],
            'description' => ['nullable', 'string'],
            'status'      => ['sometimes', new Enum(RoleStatus::class)],
        ];
    }
}
