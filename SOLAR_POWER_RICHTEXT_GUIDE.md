# Solar Power Rich Text Description Guide

## Overview

This guide explains the rich text editor implementation for the `solar_power_description` system setting and troubleshooting for solar image deployment issues.

## Part 1: Rich Text Editor for Description

### What Changed

The `solar_power_description` field in System Settings now supports rich text formatting with a WYSIWYG editor, allowing you to:
- **Bold** and *italic* text
- Create bullet lists and numbered lists
- Add links
- Underline and strikethrough text
- Proper HTML formatting

### Files Modified

#### 1. [app/Filament/Resources/SystemSettingResource.php](app/Filament/Resources/SystemSettingResource.php)

**Added new type option:**
```php
'richtext' => 'Rich Text',
```

**Added RichEditor field (lines 99-115):**
```php
FormFields\RichEditor::make('value')
    ->label('Value (Rich Text)')
    ->visible(fn ($get) => $get('type') === 'richtext')
    ->toolbarButtons([
        'bold',
        'bulletList',
        'italic',
        'link',
        'orderedList',
        'redo',
        'strike',
        'underline',
        'undo',
    ])
    ->required(fn ($get) => $get('type') === 'richtext')
    ->dehydrated(fn ($get) => $get('type') === 'richtext')
    ->columnSpanFull(),
```

**Updated table display to strip HTML tags in list view:**
```php
->formatStateUsing(fn ($state, $record) => $record->type === 'richtext' ? strip_tags($state) : $state)
```

#### 2. [resources/views/onboarding/form.blade.php](resources/views/onboarding/form.blade.php#L351-L353)

**Before:**
```blade
<p class="text-sm text-gray-700 mb-3">{{ $solarPowerDescription ?? '...' }}</p>
```

**After:**
```blade
<div class="text-sm text-gray-700 mb-3 prose prose-sm max-w-none">
    {!! $solarPowerDescription ?? '...' !!}
</div>
```

Changes:
- Changed from `<p>` to `<div>` to allow multiple paragraphs and lists
- Added `prose prose-sm max-w-none` classes for Tailwind Typography styling
- Changed from `{{ }}` (escaped) to `{!! !!}` (unescaped) to render HTML

#### 3. [resources/views/partner/orders/create.blade.php](resources/views/partner/orders/create.blade.php#L96-L98)

**Before:**
```blade
<p class="text-sm text-gray-600 mb-4">{{ $solarPowerDescription }}</p>
```

**After:**
```blade
<div class="text-sm text-gray-600 mb-4 prose prose-sm max-w-none">
    {!! $solarPowerDescription !!}
</div>
```

### Migration Script

**File:** [update_solar_description_richtext.php](update_solar_description_richtext.php)

This script:
1. Checks if `solar_power_description` exists in the database
2. Updates the `type` from `textarea` to `richtext`
3. Converts plain text to HTML if needed (wraps in `<p>` tags)
4. Creates the setting if it doesn't exist

**Run on both localhost and deployment:**
```bash
php update_solar_description_richtext.php
```

### How to Use

1. **Run the migration script:**
   ```bash
   php update_solar_description_richtext.php
   ```

2. **Go to Admin Panel → System Settings**

3. **Find and edit** `solar_power_description`

4. **Change type to** "Rich Text" (if not already)

5. **Use the rich text editor** to format your description:
   - Type your text
   - Select text and use the toolbar buttons to format
   - Add bullet points or numbered lists
   - Add links by selecting text and clicking the link button

6. **Save** and view on the frontend

### Frontend Display

The description will now render with proper HTML formatting:
- **Lists** will display as actual bullet points or numbers
- **Bold/Italic** text will be styled correctly
- **Links** will be clickable
- **Line breaks** and **paragraphs** will be preserved

## Part 2: Solar Image Deployment Issue

### Problem

Solar power image shows on localhost but not on deployed server.

### Common Causes

1. **Storage symlink not created**
2. **Image file doesn't exist on server**
3. **Wrong file permissions**
4. **Database setting has wrong type**
5. **Path mismatch**

### Diagnostic Script

**File:** [diagnose_solar_image.php](diagnose_solar_image.php)

Run this on your deployment server to identify the issue:

```bash
php diagnose_solar_image.php
```

The script checks:
- ✓ Database setting exists and has correct type
- ✓ Image file exists at expected location
- ✓ Storage symlink is properly configured
- ✓ Directory permissions are correct
- ✓ Expected public URLs

### Fix Steps

#### Step 1: Ensure Storage Link Exists

```bash
php artisan storage:link
```

This creates a symbolic link from `public/storage` to `storage/app/public`.

#### Step 2: Fix Setting Type

If the `solar_power_image` type is not 'image':

```bash
php fix_solar_image_type.php
```

#### Step 3: Upload Image via Admin Panel

1. Go to **Admin Panel → System Settings**
2. Find `solar_power_image`
3. Ensure **Type** is set to "Image"
4. Click **Edit**
5. **Upload** your solar power image
6. **Save**

The image will be stored in `storage/app/public/system-settings/`

#### Step 4: Verify Image Path

The database should store: `system-settings/your-image.jpg`

The public URL will be: `https://yourdomain.com/storage/system-settings/your-image.jpg`

### Path Handling

The views automatically handle both old and new image paths:

```php
@php
    $solarImage = \App\Models\SystemSetting::get('solar_power_image', 'images/solar_power.jpg');
    // If image is from Filament upload (stored in public disk), use storage path
    $imagePath = str_starts_with($solarImage, 'system-settings/')
        ? asset('storage/' . $solarImage)
        : asset($solarImage);
@endphp
<img src="{{ $imagePath }}" alt="Solar Power">
```

This ensures backward compatibility:
- New uploads (via Filament): `storage/system-settings/image.jpg`
- Old/static images: `images/solar_power.jpg`

### Troubleshooting

**Image still not showing after running all steps?**

1. **Check browser console** for 404 errors
2. **Verify storage link:**
   ```bash
   ls -la public/storage
   ```
   Should show: `storage -> ../storage/app/public`

3. **Check file exists:**
   ```bash
   ls -la storage/app/public/system-settings/
   ```

4. **Check permissions:**
   ```bash
   chmod -R 775 storage/app/public
   chmod -R 775 bootstrap/cache
   ```

5. **Clear cache:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```

6. **Check web server logs** for permission errors

## Testing Checklist

### Localhost Testing

- [ ] Run `php update_solar_description_richtext.php`
- [ ] Edit `solar_power_description` with rich text formatting
- [ ] View onboarding form and verify formatting displays correctly
- [ ] View partner order creation and verify formatting displays correctly
- [ ] Upload solar image via admin panel
- [ ] Verify image displays on all pages

### Deployment Testing

- [ ] Run `php diagnose_solar_image.php` to check configuration
- [ ] Run `php artisan storage:link` if needed
- [ ] Run `php fix_solar_image_type.php` if needed
- [ ] Run `php update_solar_description_richtext.php`
- [ ] Upload solar image via admin panel on production
- [ ] Edit description with rich text on production
- [ ] Test all pages where solar power info is displayed:
  - `/onboarding/{token}` - Onboarding form
  - `/onboarding/{token}/payment` - Payment page
  - `/partner/orders/create` - Partner order creation

## Related Files

### Core Files
- **System Setting Resource**: [app/Filament/Resources/SystemSettingResource.php](app/Filament/Resources/SystemSettingResource.php)
- **Onboarding Form**: [resources/views/onboarding/form.blade.php](resources/views/onboarding/form.blade.php)
- **Onboarding Payment**: [resources/views/onboarding/payment.blade.php](resources/views/onboarding/payment.blade.php)
- **Partner Order Create**: [resources/views/partner/orders/create.blade.php](resources/views/partner/orders/create.blade.php)

### Scripts
- **Diagnose Solar Image**: [diagnose_solar_image.php](diagnose_solar_image.php)
- **Fix Image Type**: [fix_solar_image_type.php](fix_solar_image_type.php)
- **Update Description Type**: [update_solar_description_richtext.php](update_solar_description_richtext.php)

### Documentation
- **Solar Image Path Fix**: [SOLAR_IMAGE_PATH_FIX.md](SOLAR_IMAGE_PATH_FIX.md)
- **Solar Image Fix**: [SOLAR_IMAGE_FIX.md](SOLAR_IMAGE_FIX.md)
- **Solar Image Setup**: [SOLAR_IMAGE_SETUP.md](SOLAR_IMAGE_SETUP.md)

## Security Note

When using `{!! !!}` to render HTML content, be aware that this bypasses Blade's XSS protection. However, since the content is:
1. Only editable by admins via the admin panel
2. Stored in the database (not user input from public forms)
3. Limited to specific formatting via the RichEditor toolbar

This is safe for this use case. The RichEditor component sanitizes the HTML and only allows safe formatting tags.
