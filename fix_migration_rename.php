<?php
// Run this on the server to fix the renamed migration
// php fix_migration_rename.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Update the migration name in the database
$updated = DB::table('migrations')
    ->where('migration', '2025_11_01_000001_create_final_onboarding_table')
    ->update(['migration' => '2025_01_19_115959_create_final_onboarding_table']);

if ($updated) {
    echo "Migration name updated successfully!\n";
} else {
    echo "No migration found to update (might not have run yet)\n";
}

echo "You can now run: php artisan migrate --force\n";
