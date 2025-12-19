# Solar Power Image Fix - Deployment Guide

## Problem
The solar power image was not displaying on the frontend because the database value was corrupted (showing `1` instead of the full file path like `system-settings/filename.jpg`).

## Root Cause
The `mutateFormDataBeforeSave` method in `EditSystemSetting.php` was incorrectly manipulating the FileUpload component's return value, causing the file path to be corrupted.

## Fixes Applied

### 1. Code Fix (Already in Repository)
**File**: `app/Filament/Resources/SystemSettingResource/Pages/EditSystemSetting.php`

Removed the problematic code that was prepending the directory to the filename. Filament's FileUpload component already handles the full path correctly.

### 2. Database Fix Options

You have **two options** to fix the corrupted database value on the server:

---

## Option 1: Run the Migration (Recommended)

This is the cleanest approach and will be applied automatically when you deploy.

### Steps:
1. Deploy your code to the server
2. SSH into the server
3. Navigate to your project directory
4. Run migrations:
   ```bash
   php artisan migrate
   ```

The migration `2025_12_19_225209_fix_solar_power_image_path.php` will:
- Detect if the `solar_power_image` value is corrupted
- Find the most recent image file in `storage/app/public/system-settings/`
- Update the database with the correct path
- Display confirmation messages

---

## Option 2: Run the Standalone PHP Script

If you need to fix the issue immediately without running all migrations:

### Steps:
1. Upload the file `fix_solar_image_server.php` to your server (root directory of the project)
2. SSH into the server
3. Navigate to your project directory
4. Run:
   ```bash
   php fix_solar_image_server.php
   ```

The script will:
- Check the current database value
- List all available image files
- Automatically select the most recent one
- Update the database
- Show confirmation

---

## Verification

After applying either fix, verify the image displays correctly:

1. Visit the onboarding page: `http://your-domain.com/onboarding/[token]`
2. Select "Yes" for Solar Power Package
3. The image should display in the expanded section

4. Visit the partner order page: `http://your-domain.com/partner/orders/create`
5. Select a partnership model
6. Select "Yes" for Solar Power Package
7. The image should display in the expanded section

---

## Important Notes

### Storage Link
Make sure the storage link exists on the server:
```bash
php artisan storage:link
```

This creates a symbolic link from `public/storage` to `storage/app/public`.

### File Permissions
Ensure the storage directory has correct permissions:
```bash
chmod -R 775 storage
chown -R www-data:www-data storage  # or your web server user
```

### Re-uploading Images
After this fix, you can safely upload new images through the admin panel:
1. Go to System Settings
2. Find the `solar_power_image` setting
3. Click Edit
4. Upload a new image
5. Save

The new upload will work correctly without corrupting the path.

---

## Files Modified

1. ✅ `app/Filament/Resources/SystemSettingResource/Pages/EditSystemSetting.php` (lines 95-96)
2. ✅ `database/migrations/2025_12_19_225209_fix_solar_power_image_path.php` (new file)
3. ✅ `fix_solar_image_server.php` (standalone fix script)

---

## Troubleshooting

### Issue: Image still not showing after fix

**Check 1**: Verify the database value
```bash
php artisan tinker
```
```php
\App\Models\SystemSetting::where('key', 'solar_power_image')->value('value');
```
Should output something like: `system-settings/01KCWBJ05P4PFJYTREGQHMW4SX.jpg`

**Check 2**: Verify the file exists
```bash
ls -la storage/app/public/system-settings/
```

**Check 3**: Verify the public symlink
```bash
ls -la public/storage
```
Should show: `public/storage -> ../storage/app/public`

**Check 4**: Check browser console for 404 errors on the image URL

### Issue: Migration says "already valid" but image doesn't show

This means the database value is correct but the file might not exist. Upload a new image through the admin panel.

---

## Summary

- **Code fix**: Already committed to repository
- **Database fix**: Choose Option 1 (migration) or Option 2 (script)
- **Future uploads**: Will work correctly after deployment
- **Verification**: Test both onboarding and partner order pages
