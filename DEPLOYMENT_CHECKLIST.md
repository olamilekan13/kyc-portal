# Solar Power Features - Deployment Checklist

This checklist ensures the solar power image and rich text description work correctly on your deployed server.

## Pre-Deployment (Run on Localhost)

- [ ] Test rich text editor in Admin → System Settings → `solar_power_description`
- [ ] Upload solar image via Admin → System Settings → `solar_power_image`
- [ ] Verify image displays on all frontend pages
- [ ] Verify rich text formatting displays correctly
- [ ] Commit and push all changes to repository

## Deployment Steps (Run on Server)

### 1. Update Code

```bash
# Pull latest code
git pull origin main

# Install/update dependencies
composer install --optimize-autoloader --no-dev
```

### 2. Run Diagnostic Script

```bash
php diagnose_solar_image.php
```

Review the output and follow any recommendations shown.

### 3. Create Storage Symlink (if needed)

```bash
php artisan storage:link
```

Expected output: `The [public/storage] link has been connected to [storage/app/public].`

If you see "already exists", that's fine.

### 4. Fix Solar Image Type (if needed)

```bash
php fix_solar_image_type.php
```

This ensures `solar_power_image` has type = 'image' in the database.

### 5. Update Description to Rich Text

```bash
php update_solar_description_richtext.php
```

This converts `solar_power_description` to use the rich text editor.

### 6. Set Permissions

```bash
# Make storage writable
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# If using a specific web server user (e.g., www-data, nginx)
sudo chown -R www-data:www-data storage bootstrap/cache
```

### 7. Clear Caches

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan optimize
```

### 8. Upload Solar Image via Admin Panel

1. Log into admin panel on production server
2. Navigate to **System Settings**
3. Find **solar_power_image**
4. Click **Edit**
5. Ensure **Type** is "Image"
6. **Upload** your solar power image
7. **Save**

### 9. Format Description Text

1. Still in **System Settings**
2. Find **solar_power_description**
3. Click **Edit**
4. Ensure **Type** is "Rich Text"
5. Use the rich text editor to format your description:
   - Add bold/italic text
   - Create bullet lists
   - Add any formatting you want
6. **Save**

## Verification

### Check Solar Image

Visit these URLs and verify the solar image shows:

```
https://yourdomain.com/onboarding/{any-valid-token}
https://yourdomain.com/partner/orders/create
```

### Check Rich Text Description

On the same pages, verify:
- [ ] Description shows with proper formatting (bold, lists, etc.)
- [ ] Line breaks and paragraphs display correctly
- [ ] No HTML code is visible (should be rendered as formatted text)

### Browser Console Check

1. Open browser Developer Tools (F12)
2. Go to the **Console** tab
3. Look for any 404 errors related to images
4. If you see errors like `GET /storage/system-settings/image.jpg 404`, the storage link is missing or image wasn't uploaded

### Database Verification

```bash
php artisan tinker
```

Then in tinker:

```php
// Check solar image setting
$image = DB::table('system_settings')->where('key', 'solar_power_image')->first();
echo "Type: {$image->type}\n";
echo "Value: {$image->value}\n";

// Check description setting
$desc = DB::table('system_settings')->where('key', 'solar_power_description')->first();
echo "Type: {$desc->type}\n";
echo "Value: {$desc->value}\n";

exit;
```

Expected output:
- `solar_power_image` → Type: image, Value: system-settings/filename.jpg
- `solar_power_description` → Type: richtext, Value: <p>HTML content...</p>

## Troubleshooting

### Image Not Showing

**Symptom:** Image shows on localhost but not production

**Solutions:**

1. **Check storage link exists:**
   ```bash
   ls -la public/storage
   ```
   Should show: `storage -> ../storage/app/public`

2. **Recreate storage link:**
   ```bash
   rm public/storage  # Remove if it's a directory instead of symlink
   php artisan storage:link
   ```

3. **Check image file exists:**
   ```bash
   ls -la storage/app/public/system-settings/
   ```

4. **Re-upload image** via admin panel

5. **Check permissions:**
   ```bash
   chmod -R 775 storage/app/public
   ```

### Rich Text Not Formatting

**Symptom:** HTML code visible instead of formatted text

**Solutions:**

1. **Verify type is 'richtext':**
   ```bash
   php update_solar_description_richtext.php
   ```

2. **Clear cache:**
   ```bash
   php artisan view:clear
   ```

3. **Check blade syntax** uses `{!! !!}` not `{{ }}`

### Permission Errors

**Symptom:** Cannot upload images or save settings

**Solutions:**

1. **Fix storage permissions:**
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

2. **Fix ownership (if using specific web server user):**
   ```bash
   sudo chown -R www-data:www-data storage bootstrap/cache
   # Replace www-data with your web server user (nginx, apache, etc.)
   ```

3. **Check directory exists:**
   ```bash
   mkdir -p storage/app/public/system-settings
   chmod 775 storage/app/public/system-settings
   ```

## Quick Fix Commands

Run all fix commands in sequence:

```bash
# 1. Create storage link
php artisan storage:link

# 2. Fix permissions
chmod -R 775 storage bootstrap/cache

# 3. Run fix scripts
php fix_solar_image_type.php
php update_solar_description_richtext.php

# 4. Clear all caches
php artisan optimize:clear

# 5. Done! Now upload image and format description via admin panel
```

## Rollback (If Needed)

If you encounter issues and need to rollback:

```bash
# Revert solar_power_description to textarea
php artisan tinker
```

In tinker:
```php
DB::table('system_settings')
    ->where('key', 'solar_power_description')
    ->update(['type' => 'textarea']);
exit;
```

Then:
```bash
php artisan cache:clear
git revert HEAD  # If you committed changes
```

## Support

For more detailed information, see:
- [SOLAR_POWER_RICHTEXT_GUIDE.md](SOLAR_POWER_RICHTEXT_GUIDE.md) - Comprehensive guide
- [SOLAR_IMAGE_PATH_FIX.md](SOLAR_IMAGE_PATH_FIX.md) - Image path troubleshooting
- [diagnose_solar_image.php](diagnose_solar_image.php) - Diagnostic script

## Post-Deployment Verification

Once deployed, verify all URLs:

- [ ] `/onboarding/{token}` - Solar power section with image and formatted description
- [ ] `/onboarding/{token}/payment` - Payment summary shows solar image
- [ ] `/partner/orders/create` - Solar power option shows image and formatted description
- [ ] Admin panel - Rich text editor works for `solar_power_description`
- [ ] Admin panel - Image upload works for `solar_power_image`

If all checkboxes are ticked, deployment is successful! ✅
