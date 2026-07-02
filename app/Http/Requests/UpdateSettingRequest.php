<?php

namespace App\Http\Requests;

use App\Enums\SettingDataType;
use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $setting = $this->route('setting');

        if (!$setting instanceof Setting) {
            return false;
        }

        return $this->user()?->can('update', $setting) ?? false;
    }

    public function rules(): array
    {
        /** @var Setting $setting */
        $setting = $this->route('setting');

        $valueRules = match ($setting->registryEntry?->data_type) {
            SettingDataType::Integer => ['nullable', 'integer'],
            SettingDataType::Boolean => ['nullable', 'boolean'],
            SettingDataType::Json => ['nullable', 'json'],
            SettingDataType::Decimal => ['nullable', 'numeric'],
            default => ['nullable', 'string'],
        };

        $registryRules = $setting->registryEntry?->validation_rules;
        if (is_array($registryRules)) {
            foreach ($registryRules as $rule) {
                if (is_string($rule) && $rule !== '') {
                    $valueRules[] = $rule;
                }
            }
        }

        return [
            'value' => $valueRules,
            'is_active' => ['required', 'boolean'],

            // Registry and ownership metadata are immutable in this flow.
            'key' => ['prohibited'],
            'scope' => ['prohibited'],
            'scope_id' => ['prohibited'],
            'registry_entry_id' => ['prohibited'],
            'data_type' => ['prohibited'],
            'category' => ['prohibited'],
        ];
    }
}
