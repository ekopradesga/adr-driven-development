<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * RolePermissionSeeder — Identity & Access module.
 *
 * Defines the initial RBAC policy by assigning permissions to roles.
 *
 * Responsibilities:
 *   - Assigns permissions to roles only (never creates roles or permissions).
 *   - Looks up roles by slug and permissions by key — no hard-coded IDs.
 *   - Uses sync() which is idempotent: running this seeder multiple times
 *     produces the same result. sync() is chosen over syncWithoutDetaching()
 *     so this file remains the single authoritative source for the initial
 *     role–permission policy.
 *
 * Dependencies:
 *   - RoleSeeder must run first.
 *   - PermissionSeeder must run first.
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Index all permissions as key => id for efficient, ID-free lookups.
        $all = Permission::pluck('id', 'key');

        if ($all->isEmpty()) {
            $this->command->warn('RolePermissionSeeder: no permissions found. Run PermissionSeeder first.');
            return;
        }

        // Resolve permission keys → IDs with a warning for any unknown key.
        $ids = function (array $keys) use ($all): array {
            $missing = array_diff($keys, $all->keys()->all());
            foreach ($missing as $key) {
                $this->command->warn("  <fg=yellow>⚠</> Permission '{$key}' not found — skipped.");
            }
            return $all->only($keys)->values()->all();
        };

        // Assign a resolved permission set to a role found by slug.
        $assign = function (string $slug, array $permissionIds): void {
            $role = Role::where('slug', $slug)->first();
            if (!$role) {
                $this->command->warn("  <fg=yellow>⚠</> Role '{$slug}' not found — skipped.");
                return;
            }
            $role->permissions()->sync($permissionIds);
            $this->command->line(
                "  <fg=green>✔</> {$role->name} — " . count($permissionIds) . ' permissions'
            );
        };

        $this->command->info('Assigning role permissions...');

        // ---------------------------------------------------------------------
        // Super Administrator — every available permission
        // Gets the complete permission set regardless of additions over time.
        // ---------------------------------------------------------------------
        $assign('super-admin', $all->values()->all());

        // ---------------------------------------------------------------------
        // Administrator — all operational permissions.
        // Excludes platform-level capabilities that only Super Admin may use:
        //   user.impersonate   — sensitive; requires explicit individual grant
        //   settings.manage-registry — platform governance, Super Admin only
        // ---------------------------------------------------------------------
        $adminExcluded = ['user.impersonate', 'settings.manage-registry'];
        $assign(
            'admin',
            $all->reject(fn ($id, $key) => in_array($key, $adminExcluded))->values()->all()
        );

        // ---------------------------------------------------------------------
        // Finance — billing governance, payment oversight, financial reporting.
        // Source: project-scope.md — Finance target user definition.
        // ---------------------------------------------------------------------
        $assign('finance', $ids([
            'dashboard.view',
            'customer.view',
            'subscription.view',
            'billing.view', 'billing.generate', 'billing.publish', 'billing.void', 'billing.export',
            'payment.view', 'payment.create', 'payment.allocate', 'payment.reverse', 'payment.export',
            'report.view', 'report.export',
        ]));

        // ---------------------------------------------------------------------
        // Sales — customer acquisition and pre-installation workflow.
        // Source: project-scope.md — Sales target user definition.
        // ---------------------------------------------------------------------
        $assign('sales', $ids([
            'dashboard.view',
            'customer.view', 'customer.create', 'customer.update', 'customer.export',
            'subscription.view', 'subscription.create', 'subscription.update',
            'report.view',
        ]));

        // ---------------------------------------------------------------------
        // Technician — field operations, provisioning, network, ticket execution.
        // Source: project-scope.md — Field Technician target user definition.
        // ---------------------------------------------------------------------
        $assign('technician', $ids([
            'dashboard.view',
            'customer.view',
            'subscription.view',
            'network.view', 'network.manage',
            'monitoring.view',
            'ticket.view', 'ticket.create', 'ticket.update', 'ticket.close',
            'report.view',
        ]));

        // ---------------------------------------------------------------------
        // Customer Service — customer support, tickets; no financial administration.
        // Source: project-scope.md — Customer Service target user definition.
        // ---------------------------------------------------------------------
        $assign('customer-service', $ids([
            'dashboard.view',
            'customer.view', 'customer.create', 'customer.update',
            'subscription.view', 'subscription.update',
            'ticket.view', 'ticket.create', 'ticket.update', 'ticket.assign', 'ticket.close',
            'report.view',
        ]));

        // ---------------------------------------------------------------------
        // Collector — payment collection workflow; no configuration access.
        // Source: project-scope.md — Collector target user definition.
        // ---------------------------------------------------------------------
        $assign('collector', $ids([
            'dashboard.view',
            'customer.view',
            'subscription.view',
            'billing.view',
            'collector.view', 'collector.create', 'collector.update', 'collector.assign', 'collector.schedule', 'collector.route', 'collector.visit', 'collector.complete', 'collector.cancel',
        ]));

        // ---------------------------------------------------------------------
        // NOC — network monitoring and diagnostics; no billing permissions.
        // Source: project-scope.md — NOC target user definition.
        // ---------------------------------------------------------------------
        $assign('noc', $ids([
            'dashboard.view',
            'customer.view',
            'subscription.view',
            'network.view', 'network.manage',
            'monitoring.view', 'monitoring.manage',
            'ticket.view', 'ticket.create', 'ticket.assign',
            'report.view',
        ]));

        // ---------------------------------------------------------------------
        // Supervisor — operational oversight, approvals, SLA monitoring.
        // Source: project-scope.md — Supervisor target user definition.
        // ---------------------------------------------------------------------
        $assign('supervisor', $ids([
            'dashboard.view',
            'user.view',
            'customer.view',
            'subscription.view',
            'billing.view',
            'payment.view',
            'network.view',
            'monitoring.view',
            'ticket.view', 'ticket.assign',
            'report.view', 'report.export',
        ]));

        $this->command->info('Role permission assignment complete.');
    }
}
