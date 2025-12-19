<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Find all system settings with richtext type that have NULL values
$settings = \App\Models\SystemSetting::where('type', 'richtext')
    ->whereNull('value')
    ->get();

echo "Found " . $settings->count() . " richtext settings with NULL values\n\n";

foreach ($settings as $setting) {
    echo "Fixing setting ID {$setting->id} ({$setting->key})...\n";

    // Update directly in the database, bypassing accessors
    \DB::table('system_settings')
        ->where('id', $setting->id)
        ->update(['value' => '<p></p>']);

    echo "  Updated to '<p></p>'\n";
}

echo "\nDone! All richtext settings now have valid HTML.\n";
