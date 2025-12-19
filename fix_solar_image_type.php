<?php
// Run this on the server to fix the solar_power_image type
// php fix_solar_image_type.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Check current state
$setting = DB::table('system_settings')
    ->where('key', 'solar_power_image')
    ->first();

if (!$setting) {
    echo "❌ solar_power_image setting not found!\n";
    echo "Creating it now...\n";

    DB::table('system_settings')->insert([
        'key' => 'solar_power_image',
        'value' => 'images/solar_power.jpg',
        'type' => 'image',
        'group' => 'onboarding',
        'description' => 'Image for solar power package',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    echo "✅ solar_power_image setting created with type 'image'\n";
} else {
    echo "Current solar_power_image setting:\n";
    echo "  - Type: {$setting->type}\n";
    echo "  - Value: {$setting->value}\n";
    echo "  - Group: {$setting->group}\n\n";

    if ($setting->type !== 'image') {
        echo "Updating type from '{$setting->type}' to 'image'...\n";

        $updated = DB::table('system_settings')
            ->where('key', 'solar_power_image')
            ->update([
                'type' => 'image',
                'updated_at' => now(),
            ]);

        if ($updated) {
            echo "✅ Type updated successfully to 'image'!\n";
        } else {
            echo "❌ Failed to update type\n";
        }
    } else {
        echo "✅ Type is already set to 'image' - no update needed\n";
    }
}

echo "\nDone! The solar_power_image setting should now show an image upload field in the admin panel.\n";
echo "Note: You may need to clear your browser cache or do a hard refresh (Ctrl+F5) to see the changes.\n";
