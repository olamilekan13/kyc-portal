# Solar Power Image Field Issue - Fix Guide

## Problem
The `solar_power_image` setting shows as an **image upload field** on localhost but shows as a **text field** when deployed to the server.

## Root Cause
The issue is that the `type` field in the `system_settings` database table is set to `'text'` instead of `'image'` on the deployed server.

The Filament admin panel determines which input field to display based on the `type` column:
- `type = 'image'` → Shows FileUpload component (image upload field)
- `type = 'text'` → Shows TextInput component (text field)

Reference: [SystemSettingResource.php:107-132](app/Filament/Resources/SystemSettingResource.php#L107-L132)

## How This Happened
The `solar_power_image` setting was likely created or updated with the wrong type on the production database. This could happen if:

1. The seeder wasn't run on production
2. The setting was manually created with the wrong type
3. The migration ran but the seeder with the correct type wasn't executed
4. A previous update changed the type

## Solution

### Option 1: Run the Fix Script (Recommended)

1. Upload the fix script to your server:
   ```bash
   # Copy fix_solar_image_type.php to your server
   ```

2. Run the script on your server:
   ```bash
   php fix_solar_image_type.php
   ```

3. The script will:
   - Check if the `solar_power_image` setting exists
   - Display the current type
   - Update the type to `'image'` if it's not already
   - Create the setting if it doesn't exist

4. Clear your browser cache or do a hard refresh (Ctrl+F5) to see the changes

### Option 2: Manual Database Update

Run this SQL query directly on your production database:

```sql
UPDATE system_settings
SET type = 'image'
WHERE key = 'solar_power_image';
```

### Option 3: Run the Seeder

If the setting doesn't exist at all, run the seeder:

```bash
php artisan db:seed --class=SystemSettingsSeeder
```

Note: The seeder will only create settings that don't exist, it won't update existing ones.

## Verification

After applying the fix:

1. Log into your admin panel on the deployed server
2. Go to System Settings
3. Find and click on the `solar_power_image` setting
4. You should now see an **image upload field** instead of a text field

## Important Files

- **Filament Resource**: [app/Filament/Resources/SystemSettingResource.php](app/Filament/Resources/SystemSettingResource.php)
  - Lines 107-132 define the FileUpload component for image type
  - Line 109 shows the visibility condition: `visible(fn ($get) => $get('type') === 'image')`

- **Seeder**: [database/seeders/SystemSettingsSeeder.php](database/seeders/SystemSettingsSeeder.php)
  - Lines 89-94 define the correct settings for `solar_power_image`

- **Migration**: [database/migrations/2025_01_19_000002_create_system_settings_table.php](database/migrations/2025_01_19_000002_create_system_settings_table.php)
  - Defines the structure of the `system_settings` table

## Storage Configuration

Make sure the image storage is properly configured on your server:

1. The `public` disk should be linked:
   ```bash
   php artisan storage:link
   ```

2. The `storage/app/public/system-settings` directory should be writable:
   ```bash
   chmod -R 775 storage/app/public/system-settings
   ```

3. Verify the `.env` file has correct filesystem configuration:
   ```env
   FILESYSTEM_DISK=public
   ```

## After Fix

Once the type is corrected:
1. The image upload field will appear in the admin panel
2. You can upload an image directly through the Filament interface
3. The image will be stored in `storage/app/public/system-settings/`
4. The `value` column will store the path (e.g., `system-settings/image.jpg`)

## Troubleshooting

**Still showing text field after fix?**
- Clear browser cache (Ctrl+F5)
- Clear Laravel cache: `php artisan cache:clear`
- Clear Filament cache: `php artisan filament:cache-clear`
- Verify database was actually updated: `SELECT * FROM system_settings WHERE key = 'solar_power_image';`

**Upload not working?**
- Check storage is linked: `php artisan storage:link`
- Check directory permissions on `storage/app/public`
- Check `.env` has `FILESYSTEM_DISK=public`
- Check PHP upload limits in `php.ini`: `upload_max_filesize` and `post_max_size`
