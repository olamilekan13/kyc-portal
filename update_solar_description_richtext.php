<?php
/**
 * Update solar_power_description to use richtext type
 * Run this on both localhost and deployment: php update_solar_description_richtext.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Updating solar_power_description to Rich Text ===\n\n";

// Check current state
$setting = DB::table('system_settings')
    ->where('key', 'solar_power_description')
    ->first();

if (!$setting) {
    echo "❌ solar_power_description setting not found!\n";
    echo "Creating it now...\n\n";

    DB::table('system_settings')->insert([
        'key' => 'solar_power_description',
        'value' => '<p>Get reliable, clean energy for your operations with our solar power solution. This package includes installation and maintenance.</p>',
        'type' => 'richtext',
        'group' => 'onboarding',
        'description' => 'Description text for solar power package displayed when user selects Yes',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    echo "✅ solar_power_description setting created with type 'richtext'\n";
} else {
    echo "Current solar_power_description setting:\n";
    echo "  - Type: {$setting->type}\n";
    echo "  - Group: {$setting->group}\n";
    echo "  - Value: " . substr($setting->value, 0, 100) . (strlen($setting->value) > 100 ? '...' : '') . "\n\n";

    if ($setting->type !== 'richtext') {
        echo "Updating type from '{$setting->type}' to 'richtext'...\n";

        // If current value is plain text, wrap it in <p> tags
        $currentValue = $setting->value;
        $newValue = $currentValue;

        // Check if value doesn't already contain HTML tags
        if (strip_tags($currentValue) === $currentValue) {
            echo "Converting plain text to HTML...\n";
            $newValue = '<p>' . nl2br(htmlspecialchars($currentValue)) . '</p>';
        }

        $updated = DB::table('system_settings')
            ->where('key', 'solar_power_description')
            ->update([
                'type' => 'richtext',
                'value' => $newValue,
                'updated_at' => now(),
            ]);

        if ($updated) {
            echo "✅ Type updated successfully to 'richtext'!\n";
            echo "✅ Value formatted as HTML\n";
        } else {
            echo "❌ Failed to update type\n";
        }
    } else {
        echo "✅ Type is already set to 'richtext' - no update needed\n";
    }
}

echo "\nDone! You can now edit the solar_power_description with rich text formatting in the admin panel.\n";
echo "The description will display with proper HTML formatting on the frontend.\n";
