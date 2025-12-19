<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get the solar_power_description setting
$setting = \App\Models\SystemSetting::where('key', 'solar_power_description')->first();

if ($setting) {
    echo "Current value:\n";
    var_dump($setting->value);

    // Try to update it
    $newContent = '<p>This is a <strong>test</strong> update with <em>rich text</em>.</p>';

    $setting->value = $newContent;
    $setting->save();

    echo "\nAfter save:\n";
    $setting->refresh();
    var_dump($setting->value);

    echo "\nDirect DB check:\n";
    $dbValue = \DB::table('system_settings')
        ->where('key', 'solar_power_description')
        ->value('value');
    var_dump($dbValue);
} else {
    echo "Setting not found!\n";
}
