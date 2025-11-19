<?php
// Run this on the server to mark the migration as already run
// php mark_migration_as_ran.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get the last batch number
$lastBatch = DB::table('migrations')->max('batch') ?? 0;

// Insert the migration record
DB::table('migrations')->insert([
    'migration' => '2025_01_19_000002_create_system_settings_table',
    'batch' => $lastBatch + 1
]);

echo "Migration marked as ran successfully!\n";
