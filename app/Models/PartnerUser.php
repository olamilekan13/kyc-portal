<?php

namespace App\Models;

use App\Notifications\PartnerResetPasswordNotification;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class PartnerUser extends Authenticatable
{
    use HasFactory, Notifiable, CanResetPassword;

    protected $fillable = [
        'kyc_submission_id',
        'email',
        'password',
        'password_changed',
        'first_name',
        'last_name',
        'phone',
        'email_verified_at',
        'status',
        'kyc_form_completed',
        'onboarding_form_completed',
        'payment_completed',
        'last_accessed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_accessed_at' => 'datetime',
        'kyc_form_completed' => 'boolean',
        'onboarding_form_completed' => 'boolean',
        'payment_completed' => 'boolean',
        'password_changed' => 'boolean',
    ];

    public function kycSubmission()
    {
        return $this->belongsTo(KycSubmission::class);
    }

    public function finalOnboarding()
    {
        return $this->hasOneThrough(
            FinalOnboarding::class,
            KycSubmission::class,
            'id',
            'kyc_submission_id',
            'kyc_submission_id',
            'id'
        );
    }

    public function orders()
    {
        return $this->hasMany(PartnerOrder::class);
    }

    public function activeOrders()
    {
        return $this->hasMany(PartnerOrder::class)->where('status', 'active');
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getProgressPercentageAttribute()
    {
        $steps = [
            $this->kyc_form_completed,
            $this->onboarding_form_completed,
            $this->payment_completed,
        ];

        $completed = count(array_filter($steps));
        return ($completed / count($steps)) * 100;
    }

    public function getNextStepAttribute()
    {
        if (!$this->kyc_form_completed) {
            return 'Complete KYC Form';
        }

        if (!$this->onboarding_form_completed) {
            return 'Complete Partnership Form';
        }

        if (!$this->payment_completed) {
            return 'Complete Payment';
        }

        return 'All steps completed';
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new PartnerResetPasswordNotification($token));
    }
}
