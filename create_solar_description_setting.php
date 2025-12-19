<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Create the solar_power_description setting
$setting = \App\Models\SystemSetting::updateOrCreate(
    ['key' => 'solar_power_description'],
    [
        'type' => 'richtext',
        'group' => 'general', // or 'onboarding' - choose the appropriate group
        'description' => 'Rich text description for solar power information displayed in the onboarding form',
        'value' => '<p>Enter your solar power description here.</p>', // Default value
    ]
);

echo "✓ System setting created/updated successfully!\n";
echo "ID: {$setting->id}\n";
echo "Key: {$setting->key}\n";
echo "Type: {$setting->type}\n";
echo "Group: {$setting->group}\n";
echo "\nYou can now edit this setting at:\n";
echo "http://127.0.0.1:8000/dashboard/system-settings/{$setting->id}/edit\n";
