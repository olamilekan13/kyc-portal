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
        'slug',
        'description',
        'status',
        'is_default',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'boolean',
        'is_default' => 'boolean',
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

    /**
     * Get the route key name for Laravel route model binding.
     * This allows routing by slug instead of ID.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Generate a URL-friendly slug from the form name.
     *
     * @param string $name
     * @return string
     */
    public static function generateSlug(string $name): string
    {
        // Convert to lowercase and replace spaces with hyphens
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        // Ensure uniqueness
        $originalSlug = $slug;
        $counter = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get the default KYC form
     *
     * @return KycForm|null
     */
    public static function getDefault(): ?KycForm
    {
        return static::where('is_default', true)
            ->where('status', true)
            ->with('fields')
            ->first();
    }

    /**
     * Set this form as the default and unset all others
     * Ensures only one form can be default at a time
     *
     * @return bool
     */
    public function setAsDefault(): bool
    {
        // Start a database transaction
        return \DB::transaction(function () {
            // Unset all other forms as default
            static::where('is_default', true)->update(['is_default' => false]);

            // Set this form as default
            $this->is_default = true;
            return $this->save();
        });
    }

    /**
     * Boot method to handle default form logic
     */
    protected static function booted(): void
    {
        // When saving a form, ensure only one is default
        static::saving(function (KycForm $form) {
            if ($form->is_default) {
                // Unset all other forms as default
                static::where('id', '!=', $form->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });
    }
}
