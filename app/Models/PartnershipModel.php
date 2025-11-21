<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnershipModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_months',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_months' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope to get only active partnership models
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Get all onboarding submissions using this model
     */
    public function onboardingSubmissions()
    {
        return $this->hasMany(FinalOnboarding::class);
    }

    /**
     * Format price for display
     */
    public function getFormattedPriceAttribute(): string
    {
        return '₦' . number_format($this->price, 2);
    }

    /**
     * Get formatted duration for display
     */
    public function getFormattedDurationAttribute(): string
    {
        if ($this->duration_months == 1) {
            return '1 Month';
        } elseif ($this->duration_months < 12) {
            return $this->duration_months . ' Months';
        } elseif ($this->duration_months == 12) {
            return '1 Year';
        } else {
            $years = floor($this->duration_months / 12);
            $months = $this->duration_months % 12;
            $result = $years . ' Year' . ($years > 1 ? 's' : '');
            if ($months > 0) {
                $result .= ' ' . $months . ' Month' . ($months > 1 ? 's' : '');
            }
            return $result;
        }
    }
}
