<?php

/**
 * Script to upload and configure the solar power image
 *
 * Usage:
 * 1. Save the solar power image to storage/app/public/system-images/solar-power.jpg
 * 2. Run: php setup_solar_image.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SystemSetting;

// Set the solar power image path
$imagePath = 'system-images/solar-power.jpg';

// Check if image exists
$fullPath = storage_path('app/public/' . $imagePath);
if (!file_exists($fullPath)) {
    echo "Error: Solar power image not found at: {$fullPath}\n";
    echo "Please save your solar power image to this location first.\n";
    exit(1);
}

// Update the system setting
SystemSetting::set('solar_power_image', $imagePath, 'image', 'partnership', 'Solar power package image');

echo "✓ Solar power image configured successfully!\n";
echo "Image path: {$imagePath}\n";
echo "You can now see this image when creating new orders with solar power enabled.\n";
