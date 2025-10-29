<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * KycFormField Model
 *
 * Represents a dynamic field in a KYC form
 *
 * @property int $id
 * @property int $kyc_form_id
 * @property string $field_type
 * @property string $field_name
 * @property string $field_label
 * @property array|null $validation_rules
 * @property bool $is_required
 * @property array|null $options
 * @property int $order
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class KycFormField extends Model
{
    /**
     * Available field types for KYC forms.
     */
    public const FIELD_TYPES = [
        'text' => 'Text',
        'email' => 'Email',
        'phone' => 'Phone',
        'date' => 'Date',
        'file' => 'File',
        'select' => 'Select',
        'textarea' => 'Textarea',
        'number' => 'Number',
        'nin' => 'NIN Verification',
        'liveness_selfie' => 'Liveness Selfie',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kyc_form_id',
        'field_type',
        'field_name',
        'field_label',
        'validation_rules',
        'is_required',
        'options',
        'order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'validation_rules' => 'array',
        'options' => 'array',
        'is_required' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the form that this field belongs to.
     *
     * @return BelongsTo
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(KycForm::class, 'kyc_form_id');
    }
}
