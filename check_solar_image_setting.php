<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get the solar_power_image setting
$setting = \App\Models\SystemSetting::where('key', 'solar_power_image')->first();

if ($setting) {
    echo "Solar Power Image Setting:\n";
    echo "ID: {$setting->id}\n";
    echo "Key: {$setting->key}\n";
    echo "Type: {$setting->type}\n";
    echo "Value (from DB): {$setting->getOriginal('value')}\n";
    echo "Value (via accessor): {$setting->value}\n";

    // Check if cache is working
    $cachedValue = \App\Models\SystemSetting::get('solar_power_image');
    echo "Cached value: {$cachedValue}\n";

    // Clear the cache
    echo "\nClearing cache for this setting...\n";
    \Illuminate\Support\Facades\Cache::forget("setting_solar_power_image");

    // Get again after cache clear
    $freshValue = \App\Models\SystemSetting::get('solar_power_image');
    echo "Fresh value after cache clear: {$freshValue}\n";

    // Check if file exists
    $imagePath = storage_path('app/public/' . $setting->value);
    echo "\nFull path: {$imagePath}\n";
    echo "File exists: " . (file_exists($imagePath) ? 'YES' : 'NO') . "\n";

    if (file_exists($imagePath)) {
        echo "File size: " . filesize($imagePath) . " bytes\n";
    }

    echo "\nPublic URL: " . asset('storage/' . $setting->value) . "\n";
} else {
    echo "Solar power image setting not found!\n";
}
