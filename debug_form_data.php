<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$setting = \App\Models\SystemSetting::find(13);

if ($setting) {
    echo "ID: {$setting->id}\n";
    echo "Key: {$setting->key}\n";
    echo "Type: {$setting->type}\n";
    echo "\ngetOriginal('value'): ";
    var_dump($setting->getOriginal('value'));
    echo "\ngetAttribute('value'): ";
    var_dump($setting->getAttribute('value'));
    echo "\n->value: ";
    var_dump($setting->value);
    echo "\ntoArray():\n";
    print_r($setting->toArray());
}
