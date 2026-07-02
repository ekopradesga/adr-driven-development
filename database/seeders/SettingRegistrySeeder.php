<?php

namespace Database\Seeders;

use App\Enums\SettingDataType;
use App\Models\SettingCategory;
use App\Models\SettingRegistryEntry;
use Illuminate\Database\Seeder;

/**
 * Seeds the setting registry — the approved schema for all configurable values.
 * Only registered keys may be stored in the settings table.
 * SettingCategorySeeder must run before this seeder.
 */
class SettingRegistrySeeder extends Seeder
{
    public function run(): void
    {
        $entries = [
            // ----------------------------------------------------------------
            // Billing
            // ----------------------------------------------------------------
            'billing' => [
                [
                    'key'           => 'billing.due_days',
                    'name'          => 'Invoice Due Days',
                    'description'   => 'Number of days after issue date before an invoice becomes due.',
                    'data_type'     => SettingDataType::Integer,
                    'default_value' => '14',
                ],
                [
                    'key'           => 'billing.overdue_suspension_days',
                    'name'          => 'Overdue Suspension Days',
                    'description'   => 'Number of days after due date before suspension eligibility is triggered.',
                    'data_type'     => SettingDataType::Integer,
                    'default_value' => '7',
                ],
                [
                    'key'           => 'billing.grace_period_days',
                    'name'          => 'Grace Period Days',
                    'description'   => 'Grace period in days before invoice overdue state is applied.',
                    'data_type'     => SettingDataType::Integer,
                    'default_value' => '3',
                ],
                [
                    'key'           => 'billing.tax_rate',
                    'name'          => 'Default Tax Rate (%)',
                    'description'   => 'Default tax rate (percentage) applied to invoices.',
                    'data_type'     => SettingDataType::Decimal,
                    'default_value' => '0',
                ],
                [
                    'key'           => 'billing.currency',
                    'name'          => 'Currency Code',
                    'description'   => 'ISO 4217 currency code used for all billing transactions.',
                    'data_type'     => SettingDataType::String,
                    'default_value' => 'IDR',
                ],
            ],

            // ----------------------------------------------------------------
            // Notifications
            // ----------------------------------------------------------------
            'notification' => [
                [
                    'key'           => 'notification.invoice_reminder_days',
                    'name'          => 'Invoice Reminder Days',
                    'description'   => 'Days before due date to send invoice payment reminders (JSON array).',
                    'data_type'     => SettingDataType::Json,
                    'default_value' => '[7, 3, 1]',
                ],
                [
                    'key'           => 'notification.overdue_reminder_days',
                    'name'          => 'Overdue Reminder Days',
                    'description'   => 'Days after due date to send overdue payment reminders (JSON array).',
                    'data_type'     => SettingDataType::Json,
                    'default_value' => '[1, 3, 7]',
                ],
                [
                    'key'           => 'notification.max_retry_attempts',
                    'name'          => 'Max Notification Retry Attempts',
                    'description'   => 'Maximum delivery retry attempts for failed notifications.',
                    'data_type'     => SettingDataType::Integer,
                    'default_value' => '3',
                ],
            ],

            // ----------------------------------------------------------------
            // Monitoring
            // ----------------------------------------------------------------
            'monitoring' => [
                [
                    'key'           => 'monitoring.health_check_interval_minutes',
                    'name'          => 'Health Check Interval (minutes)',
                    'description'   => 'Interval in minutes between ONU health checks.',
                    'data_type'     => SettingDataType::Integer,
                    'default_value' => '5',
                ],
                [
                    'key'           => 'monitoring.offline_alert_threshold_minutes',
                    'name'          => 'Offline Alert Threshold (minutes)',
                    'description'   => 'Minutes a device must be offline before an alert is triggered.',
                    'data_type'     => SettingDataType::Integer,
                    'default_value' => '10',
                ],
                [
                    'key'           => 'monitoring.signal_warning_threshold_dbm',
                    'name'          => 'Signal Warning Threshold (dBm)',
                    'description'   => 'Optical signal level at which a warning alert is raised.',
                    'data_type'     => SettingDataType::Decimal,
                    'default_value' => '-25',
                ],
                [
                    'key'           => 'monitoring.signal_critical_threshold_dbm',
                    'name'          => 'Signal Critical Threshold (dBm)',
                    'description'   => 'Optical signal level at which a critical alert is raised.',
                    'data_type'     => SettingDataType::Decimal,
                    'default_value' => '-30',
                ],
            ],

            // ----------------------------------------------------------------
            // Security
            // ----------------------------------------------------------------
            'security' => [
                [
                    'key'           => 'security.session_timeout_minutes',
                    'name'          => 'Session Timeout (minutes)',
                    'description'   => 'User session inactivity timeout in minutes.',
                    'data_type'     => SettingDataType::Integer,
                    'default_value' => '60',
                ],
                [
                    'key'           => 'security.max_login_attempts',
                    'name'          => 'Max Login Attempts',
                    'description'   => 'Maximum failed login attempts before temporary account lockout.',
                    'data_type'     => SettingDataType::Integer,
                    'default_value' => '5',
                ],
                [
                    'key'           => 'security.lockout_duration_minutes',
                    'name'          => 'Lockout Duration (minutes)',
                    'description'   => 'Duration in minutes of account lockout after max failed attempts.',
                    'data_type'     => SettingDataType::Integer,
                    'default_value' => '15',
                ],
            ],

            // ----------------------------------------------------------------
            // Collections
            // ----------------------------------------------------------------
            'collections' => [
                [
                    'key'           => 'collections.auto_assign_enabled',
                    'name'          => 'Auto-Assign Collection Tasks',
                    'description'   => 'Enable automatic assignment of collection tasks to available collectors.',
                    'data_type'     => SettingDataType::Boolean,
                    'default_value' => 'false',
                ],
                [
                    'key'           => 'collections.suspension_eligibility_days',
                    'name'          => 'Suspension Eligibility Days',
                    'description'   => 'Days overdue before a subscription becomes eligible for suspension.',
                    'data_type'     => SettingDataType::Integer,
                    'default_value' => '14',
                ],
            ],

            // ----------------------------------------------------------------
            // Ticket & Support
            // ----------------------------------------------------------------
            'ticket' => [
                [
                    'key'           => 'ticket.default_sla_hours',
                    'name'          => 'Default SLA (hours)',
                    'description'   => 'Default SLA resolution target in hours for new tickets.',
                    'data_type'     => SettingDataType::Integer,
                    'default_value' => '24',
                ],
                [
                    'key'           => 'ticket.escalation_threshold_hours',
                    'name'          => 'Escalation Threshold (hours)',
                    'description'   => 'Hours before an unresolved ticket is automatically escalated.',
                    'data_type'     => SettingDataType::Integer,
                    'default_value' => '48',
                ],
            ],

            // ----------------------------------------------------------------
            // System
            // ----------------------------------------------------------------
            'system' => [
                [
                    'key'           => 'system.app_name',
                    'name'          => 'Application Name',
                    'description'   => 'Display name of the platform shown in UI and communications.',
                    'data_type'     => SettingDataType::String,
                    'default_value' => 'ISP Management System',
                ],
                [
                    'key'           => 'system.timezone',
                    'name'          => 'System Timezone',
                    'description'   => 'Default system timezone for date and time display.',
                    'data_type'     => SettingDataType::String,
                    'default_value' => 'Asia/Jakarta',
                ],
                [
                    'key'           => 'system.pagination_size',
                    'name'          => 'Default Pagination Size',
                    'description'   => 'Default number of records per page in list views.',
                    'data_type'     => SettingDataType::Integer,
                    'default_value' => '25',
                ],
            ],
        ];

        foreach ($entries as $categoryCode => $categoryEntries) {
            $category = SettingCategory::where('code', $categoryCode)->first();

            if ($category === null) {
                $this->command->warn("Setting category '{$categoryCode}' not found. Run SettingCategorySeeder first.");
                continue;
            }

            foreach ($categoryEntries as $entry) {
                SettingRegistryEntry::updateOrCreate(
                    ['key' => $entry['key']],
                    [
                        'category_id'   => $category->id,
                        'name'          => $entry['name'],
                        'description'   => $entry['description'],
                        'data_type'     => $entry['data_type']->value,
                        'default_value' => $entry['default_value'],
                        'is_visible'    => true,
                    ]
                );
            }
        }
    }
}
