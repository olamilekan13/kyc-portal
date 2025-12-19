<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

DB::table('system_settings')->where('id', 17)->update(['value' => null]);

echo "Set setting 17 value to NULL\n";
echo "TipTap will use its default empty document structure.\n";
