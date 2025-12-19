# Fix for RichEditor "Unknown format" Error

## Error Details

**Error Message:**
```
Unknown format passed to setContent(). Try passing HTML, JSON or an Array.
```

**When it occurs:**
- When editing a system setting with type 'richtext'
- When the database value contains plain text instead of HTML

## Root Cause

The RichEditor component expects HTML-formatted content, but existing database values may contain plain text. When you try to edit a setting that has plain text in the database with the RichEditor, it throws this error.

## Solution Implemented

### 1. Code Fix (Already Applied)

Updated [SystemSettingResource.php](app/Filament/Resources/SystemSettingResource.php#L113-L124) to automatically convert plain text to HTML:

```php
FormFields\RichEditor::make('value')
    ->label('Value (Rich Text)')
    ->visible(fn ($get) => $get('type') === 'richtext')
    ->formatStateUsing(function ($state) {
        // If the value is plain text (no HTML tags), wrap it in <p> tags
        if (empty($state)) {
            return null;
        }
        // Check if it's already HTML
        if (strip_tags($state) !== $state) {
            return $state;
        }
        // Convert plain text to HTML
        return '<p>' . nl2br(e($state)) . '</p>';
    })
    // ... rest of config
```

This ensures:
- Empty values return `null`
- HTML values pass through unchanged
- Plain text values are wrapped in `<p>` tags with line breaks converted to `<br>`

### 2. Database Update (Already Run)

The script `update_solar_description_richtext.php` was run to:
- Update `solar_power_description` type from 'textarea' to 'richtext'
- Convert existing plain text value to HTML format

## Verification

Check the database value:
```bash
php artisan tinker --execute="echo DB::table('system_settings')->where('key', 'solar_power_description')->value('value');"
```

Should return HTML like:
```html
<p>Get reliable, clean energy for your operations...</p>
```

## Testing

1. **Edit the setting:**
   - Go to Admin → System Settings
   - Find `solar_power_description`
   - Click Edit
   - Should load without error
   - Rich text editor should show the content

2. **Format the text:**
   - Use the toolbar to add formatting
   - Save
   - Verify it saves correctly

3. **Check frontend:**
   - Visit `/onboarding/{token}`
   - Check solar power section
   - Description should display with formatting

## If Error Persists

### Option 1: Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

Then refresh the admin panel.

### Option 2: Manual Database Fix

If a specific setting still has issues:

```bash
php artisan tinker
```

Then:
```php
$setting = DB::table('system_settings')->where('key', 'YOUR_SETTING_KEY')->first();
echo "Current value: {$setting->value}\n";

// Convert to HTML
$htmlValue = '<p>' . nl2br(e($setting->value)) . '</p>';

// Update
DB::table('system_settings')
    ->where('key', 'YOUR_SETTING_KEY')
    ->update([
        'type' => 'richtext',
        'value' => $htmlValue,
    ]);

exit;
```

### Option 3: Change Type Back Temporarily

If you need to edit the value urgently:

1. Go to Admin → System Settings
2. Find the problematic setting
3. Change **Type** to "Textarea"
4. Save
5. Edit the content
6. Change **Type** back to "Rich Text"
7. Save again

## For Other Settings

If you want to convert other settings to use rich text:

1. **In Admin Panel:**
   - Edit the setting
   - Change Type to "Rich Text"
   - If you get the error, the value needs to be HTML

2. **Convert the value:**
   ```bash
   php artisan tinker
   ```

   ```php
   $key = 'your_setting_key';
   $setting = DB::table('system_settings')->where('key', $key)->first();

   // Convert plain text to HTML
   $htmlValue = '<p>' . nl2br(e($setting->value)) . '</p>';

   DB::table('system_settings')
       ->where('key', $key)
       ->update(['value' => $htmlValue]);

   exit;
   ```

3. **Refresh admin panel** and edit the setting

## Prevention

When creating new richtext settings:

- Always set the initial value as HTML: `<p>Your text here</p>`
- Or use the update script approach
- Don't create richtext settings with plain text values

## Related Files

- **System Setting Resource**: [app/Filament/Resources/SystemSettingResource.php](app/Filament/Resources/SystemSettingResource.php)
- **Update Script**: [update_solar_description_richtext.php](update_solar_description_richtext.php)
- **Complete Guide**: [SOLAR_POWER_RICHTEXT_GUIDE.md](SOLAR_POWER_RICHTEXT_GUIDE.md)

## Status

✅ **Fixed** - The code now handles both plain text and HTML values automatically.
