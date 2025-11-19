<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Payment Settings
            [
                'key' => 'bank_name',
                'value' => 'Your Bank Name',
                'type' => 'text',
                'group' => 'payments',
                'description' => 'Bank name for transfer payments',
            ],
            [
                'key' => 'bank_account_number',
                'value' => '0000000000',
                'type' => 'text',
                'group' => 'payments',
                'description' => 'Bank account number for transfer payments',
            ],
            [
                'key' => 'bank_account_name',
                'value' => 'Company Account Name',
                'type' => 'text',
                'group' => 'payments',
                'description' => 'Bank account name for transfer payments',
            ],
            [
                'key' => 'paystack_public_key',
                'value' => 'pk_test_xxxxxxxxxxxxxxxxxxxxx',
                'type' => 'text',
                'group' => 'payments',
                'description' => 'Paystack public key for online payments',
            ],
            [
                'key' => 'paystack_secret_key',
                'value' => 'sk_test_xxxxxxxxxxxxxxxxxxxxx',
                'type' => 'text',
                'group' => 'payments',
                'description' => 'Paystack secret key for online payments',
            ],
            [
                'key' => 'signup_fee_amount',
                'value' => '20000',
                'type' => 'number',
                'group' => 'payments',
                'description' => 'Compulsory non-refundable signup fee',
            ],

            // Notification Settings
            [
                'key' => 'admin_notification_email',
                'value' => 'admin@example.com',
                'type' => 'text',
                'group' => 'notifications',
                'description' => 'Admin email to receive notifications for all submissions (KYC and Final Onboarding)',
            ],
            [
                'key' => 'kyc_notification_email',
                'value' => '',
                'type' => 'text',
                'group' => 'notifications',
                'description' => 'Email to receive KYC submission notifications (leave empty to use admin_notification_email)',
            ],
            [
                'key' => 'onboarding_notification_email',
                'value' => '',
                'type' => 'text',
                'group' => 'notifications',
                'description' => 'Email to receive final onboarding notifications (leave empty to use admin_notification_email)',
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'group' => $setting['group'],
                    'description' => $setting['description'],
                ]
            );
        }

        $this->command->info('System settings seeded successfully!');
    }
}
