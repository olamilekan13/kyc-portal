<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FinalOnboardingForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'is_default',
        'created_by',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Get the fields for this form
     */
    public function fields()
    {
        return $this->hasMany(FinalOnboardingFormField::class)->orderBy('order');
    }

    /**
     * Get the onboarding submissions for this form
     */
    public function submissions()
    {
        return $this->hasMany(FinalOnboarding::class);
    }

    /**
     * Get the user who created this form
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the route key name for Laravel.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Generate a unique slug from the form name
     */
    public static function generateSlug($name)
    {
        $slug = Str::slug($name);
        $count = static::where('slug', 'LIKE', "{$slug}%")->count();

        return $count ? "{$slug}-{$count}" : $slug;
    }

    /**
     * Get the default final onboarding form
     */
    public static function getDefault()
    {
        return static::where('is_default', true)
            ->where('status', true)
            ->first();
    }

    /**
     * Set this form as the default
     */
    public function setAsDefault()
    {
        // Unset all other defaults
        static::where('is_default', true)->update(['is_default' => false]);

        // Set this as default
        $this->update(['is_default' => true]);
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug if not provided
        static::creating(function ($form) {
            if (empty($form->slug)) {
                $form->slug = static::generateSlug($form->name);
            }
        });

        // Ensure only one default form
        static::saving(function ($form) {
            if ($form->is_default) {
                static::where('id', '!=', $form->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });
    }
}
