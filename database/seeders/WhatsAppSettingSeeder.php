<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WhatsAppSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SystemSetting::updateOrCreate(
            ['key' => 'whatsapp_business_number'],
            [
                'value' => '',
                'type' => 'text',
                'group' => 'general',
                'description' => 'WhatsApp Business number for chat widget (format: country code + number without + or spaces, e.g., 2348012345678)',
            ]
        );

        SystemSetting::updateOrCreate(
            ['key' => 'whatsapp_chat_enabled'],
            [
                'value' => '1',
                'type' => 'number',
                'group' => 'general',
                'description' => 'Enable/Disable WhatsApp chat widget (1 = enabled, 0 = disabled)',
            ]
        );

        SystemSetting::updateOrCreate(
            ['key' => 'whatsapp_welcome_message'],
            [
                'value' => 'Hello! How can we help you today?',
                'type' => 'textarea',
                'group' => 'general',
                'description' => 'Default welcome message for WhatsApp chat',
            ]
        );

        // Solar Power Settings
        SystemSetting::updateOrCreate(
            ['key' => 'solar_power_title'],
            [
                'value' => 'Solar Power Package',
                'type' => 'text',
                'group' => 'onboarding',
                'description' => 'Title for solar power package section',
            ]
        );

        SystemSetting::updateOrCreate(
            ['key' => 'solar_power_description'],
            [
                'value' => 'Get reliable, clean energy for your operations with our solar power solution. This package includes installation and maintenance.',
                'type' => 'textarea',
                'group' => 'onboarding',
                'description' => 'Description text for solar power package',
            ]
        );
    }
}
