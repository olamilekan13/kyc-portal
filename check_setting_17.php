<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$setting = \App\Models\SystemSetting::find(17);

if (!$setting) {
    echo "System setting ID 17 not found\n";
    exit(1);
}

echo "ID: " . $setting->id . "\n";
echo "Key: " . $setting->key . "\n";
echo "Type: " . $setting->type . "\n";
echo "Value type: " . gettype($setting->value) . "\n";
echo "Value: " . var_export($setting->value, true) . "\n";
echo "\n";

// Check if the type is richtext but value is a file path
if ($setting->type === 'richtext' && is_string($setting->value)) {
    if (str_starts_with($setting->value, 'system-') ||
        str_contains($setting->value, '.jpg') ||
        str_contains($setting->value, '.png') ||
        str_contains($setting->value, '.jpeg')) {
        echo "ISSUE DETECTED: Type is 'richtext' but value appears to be a file path!\n";
        echo "This needs to be fixed. The value should be HTML content, not an image path.\n";
    }
}
