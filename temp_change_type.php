<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Temporarily change to textarea so we can edit it
DB::table('system_settings')->where('id', 17)->update(['type' => 'textarea', 'value' => '<p>Solar power content goes here</p>']);

echo "Changed setting 17:\n";
echo "  - Type: richtext -> textarea\n";
echo "  - Value: set to placeholder HTML\n";
echo "\nNow you can edit it at /dashboard/system-settings/17/edit\n";
echo "After adding your content, we'll change it back to richtext.\n";
