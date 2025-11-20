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
            [
                'key' => 'solar_power_amount',
                'value' => '50000',
                'type' => 'number',
                'group' => 'payments',
                'description' => 'Solar power package amount',
            ],
            [
                'key' => 'solar_power_title',
                'value' => 'Do you want solar power?',
                'type' => 'text',
                'group' => 'onboarding',
                'description' => 'Title/question displayed for the solar power option on onboarding form',
            ],
            [
                'key' => 'solar_power_description',
                'value' => 'Get reliable, clean energy for your operations with our solar power solution. This package includes installation and maintenance.',
                'type' => 'textarea',
                'group' => 'onboarding',
                'description' => 'Description text for solar power package displayed when user selects Yes',
            ],
            [
                'key' => 'partnership_fee_label',
                'value' => 'Partnership Fee',
                'type' => 'text',
                'group' => 'onboarding',
                'description' => 'Label displayed under the price in partnership model cards',
            ],

            // WhatsApp Settings
            [
                'key' => 'whatsapp_business_number',
                'value' => '',
                'type' => 'text',
                'group' => 'general',
                'description' => 'WhatsApp Business number for chat widget (format: country code + number without + or spaces, e.g., 2348012345678)',
            ],
            [
                'key' => 'whatsapp_chat_enabled',
                'value' => '1',
                'type' => 'number',
                'group' => 'general',
                'description' => 'Enable/Disable WhatsApp chat widget (1 = enabled, 0 = disabled)',
            ],
            [
                'key' => 'whatsapp_welcome_message',
                'value' => 'Hello! How can we help you today?',
                'type' => 'textarea',
                'group' => 'general',
                'description' => 'Default welcome message for WhatsApp chat',
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

        $newCount = 0;
        $skippedCount = 0;

        foreach ($settings as $setting) {
            // Check if setting already exists
            $exists = SystemSetting::where('key', $setting['key'])->exists();

            if (!$exists) {
                // Only create if it doesn't exist
                SystemSetting::create([
                    'key' => $setting['key'],
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'group' => $setting['group'],
                    'description' => $setting['description'],
                ]);
                $newCount++;
            } else {
                $skippedCount++;
            }
        }

        $this->command->info("System settings seeded successfully! Added: {$newCount}, Skipped (already exists): {$skippedCount}");
    }
}
