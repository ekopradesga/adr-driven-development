<?php

namespace Database\Seeders;

use App\Models\SettingCategory;
use Illuminate\Database\Seeder;

/**
 * Seeds setting categories aligned with operational business domains.
 * Categories must be created before SettingRegistrySeeder runs.
 */
class SettingCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'code'        => 'billing',
                'label'       => 'Billing',
                'description' => 'Billing cycle, invoice generation, and due date configuration.',
                'sort_order'  => 10,
            ],
            [
                'code'        => 'notification',
                'label'       => 'Notifications',
                'description' => 'Notification delivery channels, reminder schedules, and retry policies.',
                'sort_order'  => 20,
            ],
            [
                'code'        => 'monitoring',
                'label'       => 'Network Monitoring',
                'description' => 'Network health thresholds, alert rules, and monitoring intervals.',
                'sort_order'  => 30,
            ],
            [
                'code'        => 'security',
                'label'       => 'Security',
                'description' => 'Authentication policies, session timeouts, and security controls.',
                'sort_order'  => 40,
            ],
            [
                'code'        => 'collections',
                'label'       => 'Collections',
                'description' => 'Collection workflow policies, suspension eligibility, and task assignment rules.',
                'sort_order'  => 50,
            ],
            [
                'code'        => 'ticket',
                'label'       => 'Ticket & Support',
                'description' => 'SLA targets, escalation thresholds, and ticket assignment policies.',
                'sort_order'  => 60,
            ],
            [
                'code'        => 'system',
                'label'       => 'System',
                'description' => 'General system configuration and platform-wide defaults.',
                'sort_order'  => 70,
            ],
        ];

        foreach ($categories as $data) {
            SettingCategory::updateOrCreate(
                ['code' => $data['code']],
                $data
            );
        }
    }
}
