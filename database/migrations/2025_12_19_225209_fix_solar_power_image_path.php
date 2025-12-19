<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SystemSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix solar_power_image path if it's corrupted
        $setting = SystemSetting::where('key', 'solar_power_image')->first();

        if ($setting) {
            $currentValue = $setting->getOriginal('value');

            // Check if value is corrupted (just a number or doesn't include file extension)
            if ($currentValue && !preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $currentValue)) {
                echo "Found corrupted solar_power_image value: {$currentValue}\n";

                // Look for the most recent image file in system-settings directory
                $directory = storage_path('app/public/system-settings');

                if (is_dir($directory)) {
                    $files = glob($directory . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);

                    if (!empty($files)) {
                        // Sort by modification time (most recent first)
                        usort($files, function($a, $b) {
                            return filemtime($b) - filemtime($a);
                        });

                        $mostRecent = $files[0];
                        $basename = basename($mostRecent);
                        $newValue = 'system-settings/' . $basename;

                        $setting->value = $newValue;
                        $setting->save();

                        echo "✓ Updated solar_power_image to: {$newValue}\n";
                    } else {
                        echo "⚠ No image files found in system-settings directory\n";
                        // Set to null so it can be re-uploaded
                        $setting->value = null;
                        $setting->save();
                    }
                } else {
                    echo "⚠ system-settings directory does not exist\n";
                }
            } else {
                echo "✓ solar_power_image value is already valid: {$currentValue}\n";
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this fix
    }
};
