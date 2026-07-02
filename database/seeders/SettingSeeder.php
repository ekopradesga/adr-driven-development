<?php

namespace Database\Seeders;

use App\Enums\SettingScope;
use App\Models\Setting;
use App\Models\SettingRegistryEntry;
use Illuminate\Database\Seeder;

/**
 * Seeds the global settings table from registry entry defaults.
 *
 * Creates one global Setting record per SettingRegistryEntry using its
 * default_value. Safe to re-run — uses updateOrCreate.
 *
 * SettingRegistrySeeder must run before this seeder.
 */
class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $entries = SettingRegistryEntry::all();

        foreach ($entries as $entry) {
            Setting::updateOrCreate(
                [
                    'key'      => $entry->key,
                    'scope'    => SettingScope::Global->value,
                    'scope_id' => 0,
                ],
                [
                    'registry_entry_id' => $entry->id,
                    'value'             => $entry->default_value,
                    'is_active'         => true,
                ]
            );
        }
    }
}
