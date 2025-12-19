<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$settingIds = [2, 13, 19]; // Check the failing ones

foreach ($settingIds as $id) {
    $setting = \App\Models\SystemSetting::find($id);

    if ($setting) {
        echo "ID: {$setting->id}\n";
        echo "Key: {$setting->key}\n";
        echo "Type: {$setting->type}\n";
        echo "Raw Value: ";
        var_dump($setting->getRawOriginal('value'));
        echo "Value Length: " . strlen($setting->getRawOriginal('value') ?? '') . "\n";
        echo "Value is empty string: " . (($setting->getRawOriginal('value') === '') ? 'YES' : 'NO') . "\n";
        echo "Value is null: " . (is_null($setting->getRawOriginal('value')) ? 'YES' : 'NO') . "\n";
        echo str_repeat('=', 80) . "\n\n";
    }
}
