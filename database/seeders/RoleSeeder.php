<?php

namespace Database\Seeders;

use App\Enums\RoleStatus;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Seeds the platform roles defined in project-scope.md.
 * Uses updateOrCreate to be idempotent across re-runs.
 */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name'        => 'Super Administrator',
                'slug'        => 'super-admin',
                'description' => 'Full system access. Bypasses all permission checks.',
            ],
            [
                'name'        => 'Administrator',
                'slug'        => 'admin',
                'description' => 'Administrative access to all operational modules.',
            ],
            [
                'name'        => 'Finance',
                'slug'        => 'finance',
                'description' => 'Billing governance, payment oversight, financial reconciliation, and reporting.',
            ],
            [
                'name'        => 'Sales',
                'slug'        => 'sales',
                'description' => 'Lead management, prospect qualification, survey scheduling, and pre-installation workflow.',
            ],
            [
                'name'        => 'Technician',
                'slug'        => 'technician',
                'description' => 'Physical installation, service provisioning, maintenance, and field resolution.',
            ],
            [
                'name'        => 'Customer Service',
                'slug'        => 'customer-service',
                'description' => 'Customer communication, ticket management, service coordination, and operational exception handling.',
            ],
            [
                'name'        => 'Collector',
                'slug'        => 'collector',
                'description' => 'Field payment collection, outstanding invoice follow-up, and collection visit management.',
            ],
            [
                'name'        => 'NOC',
                'slug'        => 'noc',
                'description' => 'Network monitoring, device health events, and coordination with technicians on network incidents.',
            ],
            [
                'name'        => 'Supervisor',
                'slug'        => 'supervisor',
                'description' => 'Team oversight, assignment management, SLA monitoring, escalation handling, and workload balancing.',
            ],
        ];

        foreach ($roles as $data) {
            Role::updateOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, ['status' => RoleStatus::Active->value])
            );
        }
    }
}
