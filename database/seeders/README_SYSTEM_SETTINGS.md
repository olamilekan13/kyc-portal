# System Settings Seeder

This seeder populates the `system_settings` table with default configuration values for the KYC Portal.

## Settings Included

### Payment Settings
- **bank_name**: Bank name for transfer payments
- **bank_account_number**: Bank account number for transfer payments
- **bank_account_name**: Bank account name for transfer payments
- **paystack_public_key**: Paystack public key for online payments
- **paystack_secret_key**: Paystack secret key for online payments
- **signup_fee_amount**: Compulsory non-refundable signup fee (default: ₦20,000)

### Notification Settings
- **admin_notification_email**: Main admin email for all notifications
- **kyc_notification_email**: Specific email for KYC submission notifications (optional)
- **onboarding_notification_email**: Specific email for final onboarding notifications (optional)

## Usage

### Option 1: Run System Settings Seeder Only

```bash
php artisan db:seed --class=SystemSettingsSeeder
```

### Option 2: Run All Seeders (includes SystemSettingsSeeder)

```bash
php artisan db:seed
```

### Option 3: Fresh Migration with Seeding (Production Setup)

```bash
php artisan migrate:fresh --seed
```

## Production Deployment Steps

1. **Deploy your code to the server**

2. **Run migrations**
   ```bash
   php artisan migrate
   ```

3. **Seed system settings**
   ```bash
   php artisan db:seed --class=SystemSettingsSeeder
   ```

4. **Update sensitive values in the admin panel:**
   - Navigate to: `/admin/system-settings`
   - Update the following settings with your production values:
     - Bank Name
     - Bank Account Number
     - Bank Account Name
     - Paystack Public Key (use live key: `pk_live_...`)
     - Paystack Secret Key (use live key: `sk_live_...`)
     - Admin Notification Email
     - Signup Fee Amount (if different from ₦20,000)

5. **Clear cache after updating settings**
   ```bash
   php artisan cache:clear
   ```

## Important Notes

⚠️ **Security Notice:**
- The seeder includes placeholder values for sensitive data (Paystack keys, email addresses)
- **ALWAYS** update these values in the admin panel after seeding
- **NEVER** commit real Paystack secret keys to version control

✅ **Safe to Re-run:**
- The seeder uses `updateOrCreate()`, so it's safe to run multiple times
- Existing settings will be updated, not duplicated
- Your manual updates will be preserved if you re-run with the same values

## Customization

To customize default values before deployment:

1. Edit `database/seeders/SystemSettingsSeeder.php`
2. Update the `$settings` array with your desired default values
3. Commit changes to version control

## Verification

After seeding, verify the settings were created:

```bash
php artisan tinker
>>> App\Models\SystemSetting::all();
```

Or check in the admin panel:
```
https://your-domain.com/admin/system-settings
```
