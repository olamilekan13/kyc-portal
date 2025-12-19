<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$setting = \App\Models\SystemSetting::find(17);

echo "=== Debug Setting ID 17 ===\n\n";
echo "Value from getOriginal('value'): ";
var_dump($setting->getOriginal('value'));
echo "\n";

echo "Value from accessor (\$setting->value): ";
var_dump($setting->value);
echo "\n";

echo "All attributes: ";
var_dump($setting->attributes);
