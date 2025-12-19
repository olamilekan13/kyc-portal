<?php
/**
 * Diagnostic script to check solar power image configuration
 * Run this on your deployment server: php diagnose_solar_image.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SystemSetting;

echo "=== Solar Power Image Diagnostic ===\n\n";

// 1. Check database setting
echo "1. Checking database setting...\n";
$setting = DB::table('system_settings')
    ->where('key', 'solar_power_image')
    ->first();

if (!$setting) {
    echo "   ❌ ERROR: solar_power_image setting not found in database!\n";
    echo "   → Run: php artisan db:seed --class=SystemSettingsSeeder\n\n";
} else {
    echo "   ✓ Setting found in database\n";
    echo "   - Type: {$setting->type}\n";
    echo "   - Value: {$setting->value}\n";
    echo "   - Group: {$setting->group}\n\n";

    // 2. Check if it's the correct type
    if ($setting->type !== 'image') {
        echo "   ⚠️  WARNING: Type is '{$setting->type}' but should be 'image'\n";
        echo "   → Run: php fix_solar_image_type.php\n\n";
    }

    // 3. Check if file exists
    echo "2. Checking if image file exists...\n";
    $imagePath = $setting->value;

    // Try different possible locations
    $possiblePaths = [
        storage_path('app/public/' . $imagePath),
        public_path('storage/' . $imagePath),
        public_path($imagePath),
        storage_path('app/' . $imagePath),
    ];

    $found = false;
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            echo "   ✓ File found at: {$path}\n";
            echo "   - File size: " . filesize($path) . " bytes\n";
            echo "   - Readable: " . (is_readable($path) ? 'Yes' : 'No') . "\n";
            $found = true;
            break;
        }
    }

    if (!$found) {
        echo "   ❌ ERROR: Image file not found at any expected location!\n";
        echo "   Checked:\n";
        foreach ($possiblePaths as $path) {
            echo "   - {$path}\n";
        }
        echo "\n";
    }
    echo "\n";
}

// 4. Check storage link
echo "3. Checking storage symlink...\n";
$storageLinkPath = public_path('storage');
if (is_link($storageLinkPath)) {
    echo "   ✓ Storage symlink exists\n";
    $target = readlink($storageLinkPath);
    echo "   - Points to: {$target}\n";
    echo "   - Target exists: " . (file_exists($target) ? 'Yes' : 'No') . "\n";
} elseif (is_dir($storageLinkPath)) {
    echo "   ⚠️  WARNING: 'storage' exists but is a directory, not a symlink\n";
    echo "   → This might work but is not the standard setup\n";
} else {
    echo "   ❌ ERROR: Storage symlink does not exist!\n";
    echo "   → Run: php artisan storage:link\n";
}
echo "\n";

// 5. Check system-settings directory
echo "4. Checking system-settings directory...\n";
$systemSettingsDir = storage_path('app/public/system-settings');
if (is_dir($systemSettingsDir)) {
    echo "   ✓ Directory exists: {$systemSettingsDir}\n";
    echo "   - Writable: " . (is_writable($systemSettingsDir) ? 'Yes' : 'No') . "\n";

    // List files
    $files = scandir($systemSettingsDir);
    $files = array_diff($files, ['.', '..']);
    if (count($files) > 0) {
        echo "   - Files found: " . count($files) . "\n";
        foreach ($files as $file) {
            echo "     • {$file}\n";
        }
    } else {
        echo "   - No files in directory\n";
    }
} else {
    echo "   ⚠️  Directory does not exist: {$systemSettingsDir}\n";
    echo "   → Creating directory...\n";
    if (mkdir($systemSettingsDir, 0775, true)) {
        echo "   ✓ Directory created successfully\n";
    } else {
        echo "   ❌ Failed to create directory\n";
    }
}
echo "\n";

// 6. Check permissions
echo "5. Checking directory permissions...\n";
$storagePath = storage_path('app/public');
echo "   Storage path: {$storagePath}\n";
echo "   - Exists: " . (file_exists($storagePath) ? 'Yes' : 'No') . "\n";
echo "   - Writable: " . (is_writable($storagePath) ? 'Yes' : 'No') . "\n";
echo "   - Permissions: " . substr(sprintf('%o', fileperms($storagePath)), -4) . "\n";
echo "\n";

// 7. Generate test URLs
echo "6. Expected URLs for the image:\n";
if ($setting && $setting->value) {
    $value = $setting->value;

    // Check if it starts with system-settings/
    if (str_starts_with($value, 'system-settings/')) {
        echo "   Public URL: " . url('storage/' . $value) . "\n";
    } else {
        echo "   Public URL: " . url($value) . "\n";
    }
    echo "\n";
}

// 8. Summary and recommendations
echo "=== Summary ===\n";
$issues = [];

if (!$setting) {
    $issues[] = "Solar power image setting not in database";
}
if ($setting && $setting->type !== 'image') {
    $issues[] = "Setting type is not 'image'";
}
if (!is_link($storageLinkPath) && !is_dir($storageLinkPath)) {
    $issues[] = "Storage symlink missing";
}

if (count($issues) > 0) {
    echo "❌ Issues found:\n";
    foreach ($issues as $issue) {
        echo "   • {$issue}\n";
    }
    echo "\n";
    echo "Recommended actions:\n";
    if (!$setting) {
        echo "1. Run: php artisan db:seed --class=SystemSettingsSeeder\n";
    }
    if ($setting && $setting->type !== 'image') {
        echo "2. Run: php fix_solar_image_type.php\n";
    }
    if (!is_link($storageLinkPath)) {
        echo "3. Run: php artisan storage:link\n";
    }
    echo "4. Upload an image via Admin Panel → System Settings → solar_power_image\n";
} else {
    echo "✓ Basic configuration looks good!\n";
    echo "If the image still doesn't show, check:\n";
    echo "1. Browser console for 404 errors\n";
    echo "2. Web server error logs\n";
    echo "3. File permissions on the uploaded image\n";
}

echo "\n";
