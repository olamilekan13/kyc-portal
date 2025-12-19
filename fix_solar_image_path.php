<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get the solar_power_image setting
$setting = \App\Models\SystemSetting::where('key', 'solar_power_image')->first();

if ($setting) {
    echo "Current value: {$setting->value}\n";

    // List available files
    $directory = storage_path('app/public/system-settings');
    $files = scandir($directory);
    $imageFiles = array_filter($files, function($file) {
        return !in_array($file, ['.', '..']) && preg_match('/\.(jpg|jpeg|png|gif)$/i', $file);
    });

    echo "\nAvailable files in system-settings/:\n";
    foreach ($imageFiles as $index => $file) {
        echo ($index + 1) . ". {$file}\n";
    }

    // Use the most recently uploaded file (the one with 01KCVM... prefix)
    $latestFile = '01KCVM32T6XGE2PFB13T5TRWZE.png';

    echo "\nUpdating to use: {$latestFile}\n";

    $setting->value = 'system-settings/' . $latestFile;
    $setting->save();

    // Clear cache
    \Illuminate\Support\Facades\Cache::forget("setting_solar_power_image");

    echo "✓ Updated successfully!\n";
    echo "New value: {$setting->value}\n";
    echo "Public URL: " . asset('storage/' . $setting->value) . "\n";
} else {
    echo "Setting not found!\n";
}
