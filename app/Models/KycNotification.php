<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * KycNotification Model
 *
 * Represents email/SMS notifications sent for KYC submissions
 *
 * @property int $id
 * @property int $kyc_submission_id
 * @property string $type
 * @property string $recipient
 * @property string|null $subject
 * @property string $message
 * @property \Illuminate\Support\Carbon|null $sent_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class KycNotification extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kyc_notifications';

    /**
     * Notification type constants
     */
    public const TYPE_EMAIL = 'email';
    public const TYPE_SMS = 'sms';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kyc_submission_id',
        'type',
        'recipient',
        'subject',
        'message',
        'sent_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sent_at' => 'datetime',
    ];

    /**
     * Get the submission this notification belongs to.
     *
     * @return BelongsTo
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(KycSubmission::class, 'kyc_submission_id');
    }

    /**
     * Get all available notification types.
     *
     * @return array
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_EMAIL => 'Email',
            self::TYPE_SMS => 'SMS',
        ];
    }

    /**
     * Mark the notification as sent.
     *
     * @return bool
     */
    public function markAsSent(): bool
    {
        $this->sent_at = now();
        return $this->save();
    }

    /**
     * Check if the notification has been sent.
     *
     * @return bool
     */
    public function isSent(): bool
    {
        return $this->sent_at !== null;
    }
}
