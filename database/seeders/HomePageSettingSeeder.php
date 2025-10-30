<?php

namespace Database\Seeders;

use App\Models\HomePageSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HomePageSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        HomePageSetting::create([
            'title' => 'Welcome to KYC Portal',
            'subtitle' => 'Complete your Know Your Customer verification process quickly and securely',
            'instructions' => '<h2>Before You Begin</h2><p>Please ensure you have the following ready:</p><ul><li>A valid National Identity Number (NIN)</li><li>Access to your phone camera for identity verification</li><li>All required documents and information</li></ul><h3>Important Notes</h3><p>The KYC process typically takes 5-10 minutes to complete. Please ensure you have a stable internet connection throughout the process.</p><p>All information provided will be kept strictly confidential and will only be used for verification purposes.</p>',
            'button_text' => 'Start KYC Process',
            'button_link' => '/kyc',
            'is_active' => true,
        ]);
    }
}
