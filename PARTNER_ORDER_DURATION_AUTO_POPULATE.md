# Partner Order Duration Auto-Populate Feature

## Summary
When creating a new partner order at `/partner/orders/create`, the **Partnership Duration** field now automatically populates with the duration from the selected partnership model.

## Changes Made

### File Modified
- [resources/views/partner/orders/create.blade.php](resources/views/partner/orders/create.blade.php)

### Implementation Details

#### 1. Updated Partnership Model Selection (Line 62)
**Before:**
```javascript
@click="selectModel({{ $model->id }}, {{ $model->price }}, '{{ $model->name }}')"
```

**After:**
```javascript
@click="selectModel({{ $model->id }}, {{ $model->price }}, '{{ $model->name }}', {{ $model->duration_months }})"
```

Now passes the `duration_months` value from the partnership model to the `selectModel` function.

#### 2. Updated Duration Input Field (Lines 130-135)

**Before:**
```html
<input type="number" name="duration_months" min="1" value="12"
       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
```

**After:**
```html
<input type="number" name="duration_months" min="1" x-model="durationMonths" readonly
       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100 cursor-not-allowed text-gray-700">
```

Changes:

- Now binds to the Alpine.js `durationMonths` reactive property instead of a static value
- Added `readonly` attribute to prevent manual editing
- Updated styling with `bg-gray-100` and `cursor-not-allowed` to visually indicate the field is read-only

#### 3. Enhanced JavaScript Function (Lines 188-210)
**Before:**
```javascript
function orderForm() {
    return {
        selectedModel: null,
        selectedModelName: '',
        modelPrice: 0,
        solarPower: false,
        solarPowerAmount: {{ $solarPowerAmount }},
        totalAmount: 0,

        selectModel(id, price, name) {
            this.selectedModel = id;
            this.modelPrice = price;
            this.selectedModelName = name;
            this.updateTotal();
        },

        updateTotal() {
            this.totalAmount = this.modelPrice + (this.solarPower ? this.solarPowerAmount : 0);
        }
    }
}
```

**After:**
```javascript
function orderForm() {
    return {
        selectedModel: null,
        selectedModelName: '',
        modelPrice: 0,
        durationMonths: 12,  // Added default duration
        solarPower: false,
        solarPowerAmount: {{ $solarPowerAmount }},
        totalAmount: 0,

        selectModel(id, price, name, duration) {  // Added duration parameter
            this.selectedModel = id;
            this.modelPrice = price;
            this.selectedModelName = name;
            this.durationMonths = duration || 12;  // Auto-populate duration
            this.updateTotal();
        },

        updateTotal() {
            this.totalAmount = this.modelPrice + (this.solarPower ? this.solarPowerAmount : 0);
        }
    }
}
```

## How It Works

1. **Partnership Model Data**: Each partnership model in the database has a `duration_months` field ([PartnershipModel.php:16](app/Models/PartnershipModel.php#L16))

2. **Selection Trigger**: When a partner clicks on a partnership model card, the `@click` event fires

3. **Data Transfer**: The `selectModel()` function receives the model's duration and stores it in `durationMonths`

4. **Field Update**: The duration input field automatically updates via Alpine.js `x-model` binding

5. **Read-Only Protection**: The field is set to `readonly` to prevent manual editing by partners

6. **Form Submission**: The selected duration is submitted with the form to the controller ([OrderController.php:87](app/Http/Controllers/Partner/OrderController.php#L87))

## Benefits

✅ **Automatic Population**: Duration field automatically fills based on the selected model
✅ **Read-Only**: Partners cannot manually edit the duration, ensuring consistency
✅ **Consistency**: Ensures duration always matches the partnership model configuration
✅ **Better UX**: Reduces manual data entry and prevents errors
✅ **Visual Feedback**: Gray background and disabled cursor indicate the field is read-only
✅ **Fallback**: Defaults to 12 months if duration is not set on the model

## Testing

To test this feature:

1. Go to the admin panel and ensure partnership models have different `duration_months` values
2. Log in as a partner who has completed initial onboarding
3. Navigate to `/partner/orders/create`
4. Click on different partnership models
5. Observe that the "Duration (Months)" field automatically updates with each selection
6. Verify the field has a gray background and cannot be edited
7. Submit the form and verify the correct duration is saved

## Related Files

- **View**: [resources/views/partner/orders/create.blade.php](resources/views/partner/orders/create.blade.php)
- **Controller**: [app/Http/Controllers/Partner/OrderController.php](app/Http/Controllers/Partner/OrderController.php)
- **Model**: [app/Models/PartnershipModel.php](app/Models/PartnershipModel.php)
- **Order Model**: [app/Models/PartnerOrder.php](app/Models/PartnerOrder.php)
