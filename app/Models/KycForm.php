<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * KycForm Model
 *
 * Represents a KYC form template with dynamic fields
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property bool $status
 * @property int $created_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class KycForm extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'status',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Get the fields for this form, ordered by the 'order' column.
     *
     * @return HasMany
     */
    public function fields(): HasMany
    {
        return $this->hasMany(KycFormField::class)->orderBy('order');
    }

    /**
     * Get all submissions for this form.
     *
     * @return HasMany
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(KycSubmission::class);
    }

    /**
     * Get the user who created this form.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
