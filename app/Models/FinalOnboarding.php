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
}
