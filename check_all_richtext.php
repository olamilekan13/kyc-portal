<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking all system settings with richtext type:\n\n";

$settings = \App\Models\SystemSetting::where('type', 'richtext')->get();

foreach ($settings as $setting) {
    echo "ID: {$setting->id}\n";
    echo "Key: {$setting->key}\n";
    echo "Type: {$setting->type}\n";
    echo "Raw Value: ";
    var_dump($setting->getRawOriginal('value'));
    echo "Value (via accessor): ";
    var_dump($setting->value);
    echo str_repeat('-', 80) . "\n\n";
}
