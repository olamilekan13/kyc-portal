<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$setting = \App\Models\SystemSetting::find(19);

if ($setting) {
    echo "ID: " . $setting->id . "\n";
    echo "Key: " . $setting->key . "\n";
    echo "Type: " . $setting->type . "\n";
    echo "Raw Value: ";
    var_dump($setting->getRawOriginal('value'));
    echo "Value (via accessor): ";
    var_dump($setting->value);
    echo "Solar Power Description (raw): ";
    var_dump($setting->getRawOriginal('solar_power_description'));
} else {
    echo "Setting not found\n";
}
