<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Foundation — Identity & Access
            // Order matters: permissions and roles must exist before assignments.
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,

            // Foundation — Settings Engine
            SettingCategorySeeder::class,
            SettingRegistrySeeder::class,
            SettingSeeder::class,

            // Sprint 0 Story 1 — Initial admin user (depends on RoleSeeder)
            AdminUserSeeder::class,
        ]);
    }
}
