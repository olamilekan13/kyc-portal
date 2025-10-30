<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * KycSubmission Model
 *
 * Represents a KYC form submission with verification workflow
 *
 * @property int $id
 * @property int $kyc_form_id
 * @property array $submission_data
 * @property string $status
 * @property string $verification_status
 * @property array|null $verification_response
 * @property int|null $reviewed_by
 * @property \Illuminate\Support\Carbon|null $reviewed_at
 * @property string|null $decline_reason
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class KycSubmission extends Model
{
    /**
     * Submission status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_DISAPPROVED = 'declined'; // Changed from 'disapproved' to match database enum

    // Legacy status constants (kept for backwards compatibility)
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_DECLINED = 'declined';

    /**
     * Verification status constants
     */
    public const VERIFICATION_NOT_VERIFIED = 'not_verified';
    public const VERIFICATION_VERIFIED = 'verified';
    public const VERIFICATION_FAILED = 'failed';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kyc_form_id',
        'submission_data',
        'status',
        'verification_status',
        'verification_response',
        'reviewed_by',
        'reviewed_at',
        'decline_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'submission_data' => 'array',
        'verification_response' => 'array',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the form this submission belongs to.
     *
     * @return BelongsTo
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(KycForm::class, 'kyc_form_id');
    }

    /**
     * Get the user who reviewed this submission.
     *
     * @return BelongsTo
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get all verification logs for this submission.
     *
     * @return HasMany
     */
    public function verificationLogs(): HasMany
    {
        return $this->hasMany(VerificationLog::class);
    }

    /**
     * Get all notifications for this submission.
     *
     * @return HasMany
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(KycNotification::class);
    }

    /**
     * Get all available status values.
     *
     * @return array
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_DISAPPROVED => 'Disapproved',
        ];
    }

    /**
     * Get all available verification status values.
     *
     * @return array
     */
    public static function getVerificationStatuses(): array
    {
        return [
            self::VERIFICATION_NOT_VERIFIED => 'Not Verified',
            self::VERIFICATION_VERIFIED => 'Verified',
            self::VERIFICATION_FAILED => 'Failed',
        ];
    }
}
