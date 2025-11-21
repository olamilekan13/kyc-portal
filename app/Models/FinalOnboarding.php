<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinalOnboarding extends Model
{
    use HasFactory;

    protected $table = 'final_onboarding';

    protected $fillable = [
        'kyc_submission_id',
        'final_onboarding_form_id',
        'form_data',
        'partnership_model_id',
        'partnership_model_name',
        'partnership_model_price',
        'signup_fee_amount',
        'total_amount',
        'solar_power',
        'solar_power_amount',
        'payment_method',
        'payment_status',
        'signup_fee_paid',
        'signup_fee_reference',
        'signup_fee_paid_at',
        'model_fee_paid',
        'model_fee_reference',
        'model_fee_paid_at',
        'payment_notes',
        'payment_proof',
        'paystack_response',
        'partnership_start_date',
        'partnership_end_date',
        'renewal_token',
        'renewal_status',
        'reminder_sent_at',
        'reminder_count',
        'duration_months',
    ];

    protected $casts = [
        'form_data' => 'array',
        'partnership_model_price' => 'decimal:2',
        'signup_fee_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'solar_power' => 'boolean',
        'solar_power_amount' => 'decimal:2',
        'signup_fee_paid' => 'boolean',
        'model_fee_paid' => 'boolean',
        'signup_fee_paid_at' => 'datetime',
        'model_fee_paid_at' => 'datetime',
        'paystack_response' => 'array',
        'partnership_start_date' => 'date',
        'partnership_end_date' => 'date',
        'reminder_sent_at' => 'datetime',
        'reminder_count' => 'integer',
        'duration_months' => 'integer',
    ];

    /**
     * Get the KYC submission associated with this onboarding
     */
    public function kycSubmission()
    {
        return $this->belongsTo(KycSubmission::class);
    }

    /**
     * Get the partnership model associated with this onboarding
     */
    public function partnershipModel()
    {
        return $this->belongsTo(PartnershipModel::class);
    }

    /**
     * Get the final onboarding form associated with this onboarding
     */
    public function form()
    {
        return $this->belongsTo(FinalOnboardingForm::class, 'final_onboarding_form_id');
    }

    /**
     * Check if all payments are completed
     */
    public function isFullyPaid(): bool
    {
        return $this->signup_fee_paid && $this->model_fee_paid;
    }

    /**
     * Get payment completion percentage
     */
    public function getPaymentProgressAttribute(): int
    {
        $completed = 0;
        if ($this->signup_fee_paid) $completed++;
        if ($this->model_fee_paid) $completed++;

        return ($completed / 2) * 100;
    }

    /**
     * Get formatted total amount
     */
    public function getFormattedTotalAttribute(): string
    {
        return '₦' . number_format($this->total_amount, 2);
    }

    /**
     * Get formatted signup fee
     */
    public function getFormattedSignupFeeAttribute(): string
    {
        return '₦' . number_format($this->signup_fee_amount, 2);
    }

    /**
     * Get formatted model price
     */
    public function getFormattedModelPriceAttribute(): string
    {
        return '₦' . number_format($this->partnership_model_price, 2);
    }

    /**
     * Update payment status based on individual payment statuses
     */
    public function updatePaymentStatus(): void
    {
        if ($this->signup_fee_paid && $this->model_fee_paid) {
            $this->payment_status = 'completed';
        } elseif ($this->signup_fee_paid || $this->model_fee_paid) {
            $this->payment_status = 'partial';
        } else {
            $this->payment_status = 'pending';
        }

        $this->save();
    }

    /**
     * Generate a unique renewal token
     */
    public static function generateRenewalToken(): string
    {
        do {
            $token = 'RNW-' . bin2hex(random_bytes(16));
        } while (self::where('renewal_token', $token)->exists());

        return $token;
    }

    /**
     * Activate partnership after payment completion
     */
    public function activatePartnership(): void
    {
        $durationMonths = $this->partnershipModel->duration_months ?? 12;

        $this->partnership_start_date = now()->toDateString();
        $this->partnership_end_date = now()->addMonths($durationMonths)->toDateString();
        $this->renewal_token = self::generateRenewalToken();
        $this->renewal_status = 'active';
        $this->duration_months = $durationMonths;
        $this->save();
    }

    /**
     * Check if partnership is expiring soon (within given days)
     */
    public function isExpiringSoon(int $days = 10): bool
    {
        if (!$this->partnership_end_date) {
            return false;
        }

        $endDate = \Carbon\Carbon::parse($this->partnership_end_date);
        $now = now();

        return $endDate->isFuture() && $endDate->diffInDays($now) <= $days;
    }

    /**
     * Check if partnership has expired
     */
    public function isExpired(): bool
    {
        if (!$this->partnership_end_date) {
            return false;
        }

        return \Carbon\Carbon::parse($this->partnership_end_date)->isPast();
    }

    /**
     * Get days until expiry
     */
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->partnership_end_date) {
            return null;
        }

        $endDate = \Carbon\Carbon::parse($this->partnership_end_date)->startOfDay();
        $now = now()->startOfDay();

        if ($endDate->isPast()) {
            return 0;
        }

        return (int) $now->diffInDays($endDate, false);
    }

    /**
     * Get partner email from KYC submission data
     */
    public function getPartnerEmailAttribute(): ?string
    {
        $submission = $this->kycSubmission;
        if (!$submission || !$submission->submission_data) {
            return null;
        }

        $data = $submission->submission_data;

        // Check common email field names
        return $data['email'] ?? $data['Email'] ?? $data['email_address'] ?? null;
    }

    /**
     * Get partner name from KYC submission data
     */
    public function getPartnerNameAttribute(): ?string
    {
        $submission = $this->kycSubmission;
        if (!$submission || !$submission->submission_data) {
            return null;
        }

        $data = $submission->submission_data;

        // Check common name field combinations
        $firstName = $data['first_name'] ?? $data['firstName'] ?? $data['firstname'] ?? '';
        $lastName = $data['last_name'] ?? $data['lastName'] ?? $data['lastname'] ?? '';

        if ($firstName || $lastName) {
            return trim($firstName . ' ' . $lastName);
        }

        return $data['full_name'] ?? $data['fullName'] ?? $data['name'] ?? 'Partner';
    }

    /**
     * Get formatted partnership end date
     */
    public function getFormattedEndDateAttribute(): ?string
    {
        if (!$this->partnership_end_date) {
            return null;
        }

        return \Carbon\Carbon::parse($this->partnership_end_date)->format('F j, Y');
    }

    /**
     * Scope: Get partnerships expiring within given days
     */
    public function scopeExpiringSoon($query, int $days = 10)
    {
        return $query->where('payment_status', 'completed')
            ->where('renewal_status', 'active')
            ->whereNotNull('partnership_end_date')
            ->whereDate('partnership_end_date', '<=', now()->addDays($days))
            ->whereDate('partnership_end_date', '>=', now());
    }

    /**
     * Scope: Get expired partnerships
     */
    public function scopeExpired($query)
    {
        return $query->where('payment_status', 'completed')
            ->whereNotNull('partnership_end_date')
            ->whereDate('partnership_end_date', '<', now())
            ->where('renewal_status', '!=', 'renewed');
    }

    /**
     * Scope: Get partnerships needing reminder (not yet reminded today)
     */
    public function scopeNeedsReminder($query, int $daysBeforeExpiry = 10)
    {
        return $query->expiringSoon($daysBeforeExpiry)
            ->where(function ($q) {
                $q->whereNull('reminder_sent_at')
                    ->orWhereDate('reminder_sent_at', '<', now()->toDateString());
            });
    }
}
