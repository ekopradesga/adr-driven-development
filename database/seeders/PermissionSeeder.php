<?php

namespace Database\Seeders;

use App\Enums\PermissionStatus;
use App\Models\Permission;
use Illuminate\Database\Seeder;

/**
 * Seeds all platform permissions using dot-notation keys by domain category.
 * This covers all modules in Version 1 scope so roles can be assigned permissions
 * from Sprint 0 onwards without needing to re-seed.
 *
 * Key convention: {domain}.{action}
 */
class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // ----------------------------------------------------------------
            // Dashboard
            // ----------------------------------------------------------------
            ['key' => 'dashboard.view', 'name' => 'View Dashboard', 'category' => 'dashboard'],

            // ----------------------------------------------------------------
            // User Management
            // ----------------------------------------------------------------
            ['key' => 'user.view',        'name' => 'View Users',             'category' => 'user'],
            ['key' => 'user.create',      'name' => 'Create Users',           'category' => 'user'],
            ['key' => 'user.update',      'name' => 'Update Users',           'category' => 'user'],
            ['key' => 'user.delete',      'name' => 'Delete Users',           'category' => 'user'],
            ['key' => 'user.restore',     'name' => 'Restore Users',          'category' => 'user'],
            ['key' => 'user.assign-role', 'name' => 'Assign Roles to Users',  'category' => 'user'],
            ['key' => 'user.impersonate', 'name' => 'Impersonate Users',      'category' => 'user'],

            // ----------------------------------------------------------------
            // Role Management
            // ----------------------------------------------------------------
            ['key' => 'role.view',              'name' => 'View Roles',                    'category' => 'role'],
            ['key' => 'role.create',            'name' => 'Create Roles',                  'category' => 'role'],
            ['key' => 'role.update',            'name' => 'Update Roles',                  'category' => 'role'],
            ['key' => 'role.delete',            'name' => 'Delete Roles',                  'category' => 'role'],
            ['key' => 'role.assign-permission', 'name' => 'Assign Permissions to Roles',   'category' => 'role'],

            // ----------------------------------------------------------------
            // Customer Management
            // ----------------------------------------------------------------
            ['key' => 'customer.view',   'name' => 'View Customers',   'category' => 'customer'],
            ['key' => 'customer.create', 'name' => 'Create Customers', 'category' => 'customer'],
            ['key' => 'customer.update', 'name' => 'Update Customers', 'category' => 'customer'],
            ['key' => 'customer.delete', 'name' => 'Delete Customers', 'category' => 'customer'],
            ['key' => 'customer.export', 'name' => 'Export Customers', 'category' => 'customer'],

            // ----------------------------------------------------------------
            // Subscription Management
            // ----------------------------------------------------------------
            ['key' => 'subscription.view',       'name' => 'View Subscriptions',       'category' => 'subscription'],
            ['key' => 'subscription.create',     'name' => 'Create Subscriptions',     'category' => 'subscription'],
            ['key' => 'subscription.update',     'name' => 'Update Subscriptions',     'category' => 'subscription'],
            ['key' => 'subscription.suspend',    'name' => 'Suspend Subscriptions',    'category' => 'subscription'],
            ['key' => 'subscription.reactivate', 'name' => 'Reactivate Subscriptions', 'category' => 'subscription'],
            ['key' => 'subscription.terminate',  'name' => 'Terminate Subscriptions',  'category' => 'subscription'],

            // ----------------------------------------------------------------
            // Billing
            // ----------------------------------------------------------------
            ['key' => 'billing.view',     'name' => 'View Billing Records', 'category' => 'billing'],
            ['key' => 'billing.generate', 'name' => 'Generate Invoices',    'category' => 'billing'],
            ['key' => 'billing.publish',  'name' => 'Publish Invoices',     'category' => 'billing'],
            ['key' => 'billing.void',     'name' => 'Void Invoices',        'category' => 'billing'],
            ['key' => 'billing.export',   'name' => 'Export Billing Data',  'category' => 'billing'],

            // ----------------------------------------------------------------
            // Collector
            // ----------------------------------------------------------------
            ['key' => 'collector.view',     'name' => 'View Collector Work',     'category' => 'collector'],
            ['key' => 'collector.create',   'name' => 'Create Collector Work',   'category' => 'collector'],
            ['key' => 'collector.update',   'name' => 'Update Collector Work',   'category' => 'collector'],
            ['key' => 'collector.assign',   'name' => 'Assign Collector Work',   'category' => 'collector'],
            ['key' => 'collector.schedule', 'name' => 'Schedule Collector Work', 'category' => 'collector'],
            ['key' => 'collector.route',    'name' => 'Start Collector Route',   'category' => 'collector'],
            ['key' => 'collector.visit',    'name' => 'Record Collector Visit',  'category' => 'collector'],
            ['key' => 'collector.complete', 'name' => 'Complete Collector Work', 'category' => 'collector'],
            ['key' => 'collector.cancel',   'name' => 'Cancel Collector Work',   'category' => 'collector'],
            ['key' => 'collector.export',   'name' => 'Export Collector Work',   'category' => 'collector'],

            // ----------------------------------------------------------------
            // Service Area
            // ----------------------------------------------------------------
            ['key' => 'service-area.view',    'name' => 'View Service Areas and Clusters',   'category' => 'service-area'],
            ['key' => 'service-area.create',  'name' => 'Create Service Areas and Clusters', 'category' => 'service-area'],
            ['key' => 'service-area.update',  'name' => 'Update Service Areas and Clusters', 'category' => 'service-area'],
            ['key' => 'service-area.assign',  'name' => 'Assign Employees to Service Areas', 'category' => 'service-area'],
            ['key' => 'service-area.merge',   'name' => 'Merge Service Areas',                'category' => 'service-area'],
            ['key' => 'service-area.archive', 'name' => 'Archive Service Areas and Clusters', 'category' => 'service-area'],
            ['key' => 'service-area.export',  'name' => 'Export Service Area Data',           'category' => 'service-area'],

            // ----------------------------------------------------------------
            // Payments
            // ----------------------------------------------------------------
            ['key' => 'payment.view',     'name' => 'View Payments',         'category' => 'payment'],
            ['key' => 'payment.create',   'name' => 'Record Payments',       'category' => 'payment'],
            ['key' => 'payment.allocate', 'name' => 'Allocate Payments',     'category' => 'payment'],
            ['key' => 'payment.reverse',  'name' => 'Reverse Payments',      'category' => 'payment'],
            ['key' => 'payment.export',   'name' => 'Export Payment Data',   'category' => 'payment'],

            // ----------------------------------------------------------------
            // Settings
            // ----------------------------------------------------------------
            ['key' => 'settings.view',            'name' => 'View Settings',             'category' => 'settings'],
            ['key' => 'settings.update',          'name' => 'Update Settings',           'category' => 'settings'],
            ['key' => 'settings.manage-registry', 'name' => 'Manage Setting Registry',   'category' => 'settings'],

            // ----------------------------------------------------------------
            // Network & Monitoring
            // ----------------------------------------------------------------
            ['key' => 'network.view',       'name' => 'View Network Infrastructure',     'category' => 'network'],
            ['key' => 'network.manage',     'name' => 'Manage Network Infrastructure',   'category' => 'network'],
            ['key' => 'monitoring.view',    'name' => 'View Monitoring Data',            'category' => 'monitoring'],
            ['key' => 'monitoring.manage',  'name' => 'Manage Monitoring Configuration', 'category' => 'monitoring'],

            // ----------------------------------------------------------------
            // Tickets
            // ----------------------------------------------------------------
            ['key' => 'ticket.view',   'name' => 'View Tickets',   'category' => 'ticket'],
            ['key' => 'ticket.create', 'name' => 'Create Tickets', 'category' => 'ticket'],
            ['key' => 'ticket.update', 'name' => 'Update Tickets', 'category' => 'ticket'],
            ['key' => 'ticket.assign', 'name' => 'Assign Tickets', 'category' => 'ticket'],
            ['key' => 'ticket.close',  'name' => 'Close Tickets',  'category' => 'ticket'],

            // ----------------------------------------------------------------
            // Reports
            // ----------------------------------------------------------------
            ['key' => 'report.view',   'name' => 'View Reports',   'category' => 'report'],
            ['key' => 'report.export', 'name' => 'Export Reports', 'category' => 'report'],
        ];

        foreach ($permissions as $data) {
            Permission::updateOrCreate(
                ['key' => $data['key']],
                array_merge($data, ['status' => PermissionStatus::Active->value])
            );
        }
    }
}
