<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinalOnboardingFormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'final_onboarding_form_id',
        'field_type',
        'field_name',
        'field_label',
        'validation_rules',
        'is_required',
        'options',
        'order',
    ];

    protected $casts = [
        'validation_rules' => 'array',
        'is_required' => 'boolean',
        'options' => 'array',
        'order' => 'integer',
    ];

    /**
     * Available field types for final onboarding forms
     */
    const FIELD_TYPES = [
        'text',
        'email',
        'phone',
        'date',
        'file',
        'select',
        'textarea',
        'number',
    ];

    /**
     * Get the form that owns this field
     */
    public function form()
    {
        return $this->belongsTo(FinalOnboardingForm::class, 'final_onboarding_form_id');
    }
}
