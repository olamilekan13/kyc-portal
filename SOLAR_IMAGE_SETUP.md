# Solar Power Image Setup Instructions

## Steps to Add the Solar Power Image

1. **Save the solar power image file**
   - Save the solar power package image (the 500W Solar Energy Storage System image) to:
   - Path: `storage/app/public/system-images/solar-power.jpg`

2. **Run the setup script**
   ```bash
   php setup_solar_image.php
   ```

   This will automatically configure the `solar_power_image` system setting.

## Alternative: Manual Setup via Filament Admin Panel

1. Go to the Filament admin panel
2. Navigate to Settings → System Settings
3. Add/Update the following setting:
   - **Key**: `solar_power_image`
   - **Value**: `system-images/solar-power.jpg`
   - **Type**: `image`
   - **Group**: `partnership`
   - **Description**: `Solar power package image`

4. Make sure the image file exists at: `storage/app/public/system-images/solar-power.jpg`

## Verify Setup

Once configured, when partners create new orders:
- If solar power is enabled in system settings
- The solar power image will be displayed above the solar power package option
- The image will be responsive (full width, 48px height, object-cover, rounded corners)

## Image Requirements

- **Format**: JPG, PNG, or WebP
- **Recommended size**: 1200x400px (3:1 aspect ratio)
- **Max file size**: 2MB
- **Location**: Must be in the public storage disk
