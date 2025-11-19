<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert notification settings
        DB::table('system_settings')->insert([
            [
                'key' => 'admin_notification_email',
                'value' => 'admin@example.com',
                'type' => 'text',
                'group' => 'notifications',
                'description' => 'Admin email to receive notifications for all submissions (KYC and Final Onboarding)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'kyc_notification_email',
                'value' => '',
                'type' => 'text',
                'group' => 'notifications',
                'description' => 'Email to receive KYC submission notifications (leave empty to use admin_notification_email)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'onboarding_notification_email',
                'value' => '',
                'type' => 'text',
                'group' => 'notifications',
                'description' => 'Email to receive final onboarding notifications (leave empty to use admin_notification_email)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('system_settings')->whereIn('key', [
            'admin_notification_email',
            'kyc_notification_email',
            'onboarding_notification_email'
        ])->delete();
    }
};
