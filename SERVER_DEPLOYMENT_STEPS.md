# Server Deployment - Solar Power Features

## Quick Fix Guide

Run these commands on your **deployment server** to fix the solar image display issue:

```bash
# 1. Diagnose current state
php diagnose_solar_image.php

# 2. Create storage symlink
php artisan storage:link

# 3. Fix solar image type (if needed)
php fix_solar_image_type.php

# 4. Update description to richtext
php update_solar_description_richtext.php

# 5. Set proper permissions
chmod -R 775 storage/app/public
chmod -R 775 bootstrap/cache

# 6. Clear all caches
php artisan optimize:clear
```

## Detailed Steps

### Step 1: Upload Code to Server

```bash
# On your server
cd /path/to/your/project
git pull origin main
composer install --optimize-autoloader --no-dev
```

### Step 2: Check Storage Configuration

```bash
# Run diagnostic
php diagnose_solar_image.php
```

This will show you:
- ✓ Database setting status
- ✓ File existence
- ✓ Storage symlink status
- ✓ Permissions
- ❌ Any issues found

### Step 3: Create Storage Symlink

**IMPORTANT:** This is the most common reason images don't show on production.

```bash
php artisan storage:link
```

**Expected output:**
```
The [public/storage] link has been connected to [storage/app/public].
```

**If you see "link already exists":**

Check if it's a valid symlink or a directory:
```bash
# Linux/Mac
ls -la public/storage

# Windows (Git Bash or WSL)
ls -la public/storage
```

If it's a **directory** (not a symlink), remove it and recreate:
```bash
# Backup first (if it contains files)
mv public/storage public/storage_backup

# Create proper symlink
php artisan storage:link

# If there were important files in the directory, copy them:
# cp -r public/storage_backup/* storage/app/public/
```

### Step 4: Fix Database Settings

```bash
# Fix image type
php fix_solar_image_type.php

# Update description to richtext
php update_solar_description_richtext.php
```

### Step 5: Upload Solar Image via Admin Panel

**On production server's admin panel:**

1. Navigate to: `https://yourdomain.com/dashboard/system-settings`
2. Find `solar_power_image` (ID: 17 or search for it)
3. Click **Edit**
4. Ensure **Type** is set to "Image"
5. Upload your solar power image (JPG, PNG, max 2MB)
6. Click **Save**

The image will be stored in: `storage/app/public/system-settings/`

### Step 6: Format Description Text

**On production admin panel:**

1. Still in System Settings
2. Find `solar_power_description`
3. Click **Edit**
4. Ensure **Type** is set to "Rich Text"
5. Use the rich text editor to format your description
6. Click **Save**

### Step 7: Set Permissions (Linux/Mac only)

```bash
# Make storage writable
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# If using a specific web server user (e.g., www-data for Apache/Nginx)
sudo chown -R www-data:www-data storage bootstrap/cache

# Or for nginx user
sudo chown -R nginx:nginx storage bootstrap/cache
```

### Step 8: Clear Caches

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan optimize
```

## Verification on Server

### 1. Check Database

```bash
php artisan tinker
```

In tinker:
```php
// Check solar image
$img = DB::table('system_settings')->where('key', 'solar_power_image')->first();
echo "Type: {$img->type}, Value: {$img->value}\n";

// Check description
$desc = DB::table('system_settings')->where('key', 'solar_power_description')->first();
echo "Type: {$desc->type}\n";
echo "Value: " . substr($desc->value, 0, 100) . "...\n";

exit;
```

**Expected:**
- Image type: `image`
- Image value: `system-settings/filename.jpg`
- Description type: `richtext`
- Description value: `<p>HTML content...</p>`

### 2. Check File Exists

```bash
ls -la storage/app/public/system-settings/
```

Should show your uploaded image file.

### 3. Check Public Access

```bash
ls -la public/storage
```

Should show a symlink (indicated by `->`) pointing to `../storage/app/public`

### 4. Test Image URL

```bash
# Get the image filename from database
php artisan tinker --execute="echo DB::table('system_settings')->where('key', 'solar_power_image')->value('value');"
```

Then test the URL in browser:
```
https://yourdomain.com/storage/system-settings/FILENAME.jpg
```

Should display the image (not 404).

### 5. Check Frontend Pages

Visit these URLs and verify image and formatted description display:

- `https://yourdomain.com/onboarding/{any-valid-token}`
- `https://yourdomain.com/partner/orders/create`

## Common Issues & Fixes

### Issue 1: Image Shows 404 Error

**Symptom:** Browser console shows `GET /storage/system-settings/image.jpg 404`

**Fix:**
```bash
# 1. Check symlink exists
ls -la public/storage

# 2. If not a symlink, recreate it
rm -rf public/storage  # Be careful with this!
php artisan storage:link

# 3. Verify file exists
ls -la storage/app/public/system-settings/

# 4. If file missing, re-upload via admin panel
```

### Issue 2: Permission Denied

**Symptom:** Cannot upload images, or "Permission denied" errors

**Fix:**
```bash
# Fix permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Fix ownership
sudo chown -R www-data:www-data storage bootstrap/cache
```

### Issue 3: RichEditor Error

**Symptom:** "Unknown format passed to setContent()" error

**Fix:**
```bash
# Already applied in code, but run update script
php update_solar_description_richtext.php

# Clear cache
php artisan view:clear
```

See [FIX_RICHTEXT_ERROR.md](FIX_RICHTEXT_ERROR.md) for details.

### Issue 4: Image Shows on Localhost but not Production

**Root causes:**
1. Storage symlink not created on server
2. Image not uploaded to server (only exists on localhost)
3. Different file permissions
4. SELinux blocking symlinks (some servers)

**Fix:**
```bash
# 1. Create symlink
php artisan storage:link

# 2. Upload image via admin panel on PRODUCTION (not localhost)

# 3. Check permissions
chmod -R 775 storage/app/public

# 4. If using SELinux, allow symlinks
# sudo setsebool -P httpd_enable_homedirs 1
# sudo restorecon -Rv storage
```

## Environment-Specific Notes

### Shared Hosting

If `php artisan storage:link` doesn't work:

1. **Via cPanel File Manager:**
   - Navigate to `public` folder
   - Create a symlink manually:
     - Name: `storage`
     - Target: `../storage/app/public`

2. **Via SSH:**
   ```bash
   cd public
   ln -s ../storage/app/public storage
   ```

### Windows Server (IIS)

```powershell
# Run PowerShell as Administrator
cd C:\inetpub\wwwroot\your-project\public

# Create junction (Windows symlink)
New-Item -ItemType Junction -Path storage -Target ..\storage\app\public
```

### Docker/Container

Add to your Dockerfile:
```dockerfile
RUN php artisan storage:link
```

Or in docker-compose.yml:
```yaml
services:
  app:
    volumes:
      - ./storage/app/public:/var/www/html/public/storage
```

## Security Checklist

Before deploying:

- [ ] `.env` file has correct `APP_ENV=production`
- [ ] `APP_DEBUG=false` in production `.env`
- [ ] Storage directories have correct permissions (775, not 777)
- [ ] Only necessary files are in `public` directory
- [ ] Sensitive files are in `storage/app/private` (not `public`)

## Testing Checklist

After deployment:

- [ ] Admin can log in
- [ ] Can edit `solar_power_image` setting without error
- [ ] Can upload image via admin panel
- [ ] Can edit `solar_power_description` with rich text editor
- [ ] Image displays on onboarding form
- [ ] Image displays on payment page
- [ ] Image displays on partner order creation
- [ ] Description formatting displays correctly
- [ ] No console errors in browser DevTools

## Rollback Plan

If deployment causes issues:

```bash
# 1. Rollback code
git revert HEAD
git push

# 2. Pull on server
git pull

# 3. Reinstall dependencies
composer install --no-dev

# 4. Clear caches
php artisan optimize:clear

# 5. If database changes were made
# Manually revert using tinker:
php artisan tinker
```

```php
DB::table('system_settings')
    ->where('key', 'solar_power_description')
    ->update(['type' => 'textarea']);
exit;
```

## Support

For issues:

1. Run diagnostic: `php diagnose_solar_image.php`
2. Check documentation:
   - [SOLAR_POWER_RICHTEXT_GUIDE.md](SOLAR_POWER_RICHTEXT_GUIDE.md)
   - [FIX_RICHTEXT_ERROR.md](FIX_RICHTEXT_ERROR.md)
   - [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md)
3. Check server error logs:
   - `storage/logs/laravel.log`
   - Web server logs (Apache/Nginx)

## Post-Deployment Monitoring

Monitor for:
- Image 404 errors in logs
- Permission errors
- Upload failures
- Console errors in browser

---

**Last Updated:** 2025-12-19
**Status:** Ready for Production Deployment
