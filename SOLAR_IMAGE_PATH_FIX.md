# Solar Power Image Display Fix

## Problem
The solar power image uploaded through the admin panel (System Settings) was not displaying correctly in the onboarding payment page, even though it worked fine in the onboarding form.

## Root Cause

When images are uploaded through Filament's FileUpload component in the System Settings admin panel, they are stored in the `public` disk under the `system-settings/` directory (as configured in [SystemSettingResource.php:112](app/Filament/Resources/SystemSettingResource.php#L112)).

The database stores the path as: `system-settings/image-name.jpg`

However, to access files from the public disk via the web, they need to be prefixed with `storage/`:
- **Correct**: `asset('storage/system-settings/image.jpg')`
- **Incorrect**: `asset('system-settings/image.jpg')`

The code was using `asset(\App\Models\SystemSetting::get('solar_power_image'))` which didn't include the `storage/` prefix, causing the images not to load.

## Solution

Added logic to detect if the image path is from a Filament upload (starts with `system-settings/`) and automatically prepend `storage/` to the path.

## Files Modified

### 1. [resources/views/onboarding/payment.blade.php](resources/views/onboarding/payment.blade.php#L110-L117)

**Before:**
```blade
<img src="{{ asset(\App\Models\SystemSetting::get('solar_power_image', 'images/solar_power.jpg')) }}" alt="Solar Power" class="w-16 h-16 object-cover rounded-lg shadow-sm">
```

**After:**
```blade
@php
    $solarImage = \App\Models\SystemSetting::get('solar_power_image', 'images/solar_power.jpg');
    // If image is from Filament upload (stored in public disk), use storage path
    $imagePath = str_starts_with($solarImage, 'system-settings/')
        ? asset('storage/' . $solarImage)
        : asset($solarImage);
@endphp
<img src="{{ $imagePath }}" alt="Solar Power" class="w-16 h-16 object-cover rounded-lg shadow-sm">
```

### 2. [resources/views/onboarding/form.blade.php](resources/views/onboarding/form.blade.php#L341-L348)

**Before:**
```blade
<img src="{{ asset(\App\Models\SystemSetting::get('solar_power_image', 'images/solar_power.jpg')) }}" alt="Solar Power" class="w-32 h-32 object-cover rounded-lg shadow-lg">
```

**After:**
```blade
@php
    $solarImage = \App\Models\SystemSetting::get('solar_power_image', 'images/solar_power.jpg');
    // If image is from Filament upload (stored in public disk), use storage path
    $imagePath = str_starts_with($solarImage, 'system-settings/')
        ? asset('storage/' . $solarImage)
        : asset($solarImage);
@endphp
<img src="{{ $imagePath }}" alt="Solar Power" class="w-32 h-32 object-cover rounded-lg shadow-lg">
```

### 3. [resources/views/partner/orders/create.blade.php](resources/views/partner/orders/create.blade.php#L100-L106)

**Before:**
```blade
<img src="{{ asset('storage/' . $solarPowerImage) }}"
     alt="Solar Power Package"
     class="w-full h-48 object-cover rounded-lg shadow-sm">
```

**After:**
```blade
@php
    // If image is from Filament upload (stored in public disk), use storage path
    $imagePath = str_starts_with($solarPowerImage, 'system-settings/')
        ? asset('storage/' . $solarPowerImage)
        : asset($solarPowerImage);
@endphp
<img src="{{ $imagePath }}"
     alt="Solar Power Package"
     class="w-full h-48 object-cover rounded-lg shadow-sm">
```

## How It Works

The fix uses `str_starts_with()` to check if the image path begins with `system-settings/`:

1. **If YES** (uploaded via Filament): Prepends `storage/` to create the correct public URL
   - Path in DB: `system-settings/solar-image.jpg`
   - Generated URL: `https://yourdomain.com/storage/system-settings/solar-image.jpg`

2. **If NO** (legacy/static image): Uses the path as-is
   - Path in DB: `images/solar_power.jpg`
   - Generated URL: `https://yourdomain.com/images/solar_power.jpg`

This approach ensures **backward compatibility** with any existing images that might be stored in different locations.

## Storage Link Requirement

For this to work, ensure the storage link is created:

```bash
php artisan storage:link
```

This creates a symbolic link from `public/storage` to `storage/app/public`, making files stored in the public disk accessible via the web.

## Testing

To verify the fix:

1. **Upload a new image** through Admin Panel → System Settings → Edit `solar_power_image`
2. **Navigate to** `/onboarding/{token}` (onboarding form page)
3. **Select a partnership model** and choose "Yes" for solar power
4. **Verify the image displays** correctly
5. **Proceed to payment page** `/onboarding/{token}/payment`
6. **Verify the image displays** in the payment summary under "Solar Power"
7. **Navigate to** `/partner/orders/create` (partner order creation)
8. **Verify the image displays** when solar power section is shown

## Related Files

- **System Setting Resource**: [app/Filament/Resources/SystemSettingResource.php](app/Filament/Resources/SystemSettingResource.php)
- **Onboarding Form**: [resources/views/onboarding/form.blade.php](resources/views/onboarding/form.blade.php)
- **Onboarding Payment**: [resources/views/onboarding/payment.blade.php](resources/views/onboarding/payment.blade.php)
- **Partner Order Create**: [resources/views/partner/orders/create.blade.php](resources/views/partner/orders/create.blade.php)
- **Solar Image Type Fix Script**: [fix_solar_image_type.php](fix_solar_image_type.php)
- **Solar Image Setup Guide**: [SOLAR_IMAGE_SETUP.md](SOLAR_IMAGE_SETUP.md)

## Additional Notes

- The Filament FileUpload component automatically handles the file upload and stores it in `storage/app/public/system-settings/`
- The `value` column in the `system_settings` table stores only the relative path (e.g., `system-settings/image.jpg`)
- The actual file location is `storage/app/public/system-settings/image.jpg`
- The web-accessible URL is `public/storage/system-settings/image.jpg` (via symlink)
