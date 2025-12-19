<?php

/**
 * Fix Solar Power Image Path on Server
 *
 * Run this script on the server to fix the corrupted solar_power_image value.
 * Usage: php fix_solar_image_server.php
 */

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Solar Power Image Path Fix ===\n\n";

// Get the solar_power_image setting
$setting = \App\Models\SystemSetting::where('key', 'solar_power_image')->first();

if (!$setting) {
    echo "❌ solar_power_image setting not found in database.\n";
    echo "Please create this setting in the admin panel first.\n";
    exit(1);
}

$currentValue = $setting->getOriginal('value');
echo "Current database value: '{$currentValue}'\n\n";

// Check if value is already valid (has file extension)
if ($currentValue && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $currentValue)) {
    echo "✓ Image path is already valid!\n";

    // Verify file exists
    $fullPath = storage_path('app/public/' . $currentValue);
    if (file_exists($fullPath)) {
        echo "✓ Image file exists at: {$fullPath}\n";
        echo "✓ No fix needed.\n";
        exit(0);
    } else {
        echo "⚠ Warning: Database value looks correct but file doesn't exist.\n";
        echo "Expected file at: {$fullPath}\n";
        echo "Attempting to find the correct file...\n\n";
    }
}

// Look for image files in system-settings directory
$directory = storage_path('app/public/system-settings');

if (!is_dir($directory)) {
    echo "❌ Directory does not exist: {$directory}\n";
    echo "Please create the directory and upload an image through the admin panel.\n";
    exit(1);
}

$files = glob($directory . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);

if (empty($files)) {
    echo "❌ No image files found in: {$directory}\n";
    echo "Please upload an image through the admin panel.\n";

    // Set to null so it can be re-uploaded
    $setting->value = null;
    $setting->save();
    echo "✓ Set value to NULL. Please upload a new image.\n";
    exit(1);
}

// Sort by modification time (most recent first)
usort($files, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

echo "Found " . count($files) . " image file(s) in system-settings:\n";
foreach ($files as $index => $file) {
    $basename = basename($file);
    $mtime = date('Y-m-d H:i:s', filemtime($file));
    $size = round(filesize($file) / 1024, 2);
    echo "  [" . ($index + 1) . "] {$basename}\n";
    echo "      Modified: {$mtime}, Size: {$size} KB\n";
}
echo "\n";

// Use the most recent file
$mostRecent = $files[0];
$basename = basename($mostRecent);
$newValue = 'system-settings/' . $basename;

echo "Selected: {$basename} (most recent)\n";
echo "New value: {$newValue}\n\n";

// Update the database
$setting->value = $newValue;
$setting->save();

echo "✓ SUCCESS! Updated solar_power_image to: {$newValue}\n";
echo "✓ The image should now display correctly on the frontend.\n";
echo "\nYou can verify at:\n";
echo "- Onboarding page: http://your-domain.com/onboarding/...\n";
echo "- Partner orders page: http://your-domain.com/partner/orders/create\n";
