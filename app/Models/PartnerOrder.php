<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PartnerOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_user_id',
        'partnership_model_id',
        'partnership_model_name',
        'partnership_model_price',
        'order_number',
        'solar_power',
        'solar_power_amount',
        'signup_fee_amount',
        'subtotal',
        'total_amount',
        'payment_method',
        'payment_status',
        'payment_reference',
        'paid_at',
        'payment_notes',
        'payment_proof',
        'paystack_response',
        'status',
        'start_date',
        'end_date',
        'duration_months',
        'form_data',
        'order_token',
    ];

    protected $casts = [
        'partnership_model_price' => 'decimal:2',
        'solar_power' => 'boolean',
        'solar_power_amount' => 'decimal:2',
        'signup_fee_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
        'form_data' => 'array',
        'paystack_response' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (!$order->order_number) {
                $order->order_number = self::generateOrderNumber();
            }
            if (!$order->order_token) {
                $order->order_token = Str::random(64);
            }
        });
    }

    public static function generateOrderNumber(): string
    {
        $year = date('Y');
        $lastOrder = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastOrder ? (int) substr($lastOrder->order_number, -5) + 1 : 1;

        return sprintf('ORD-%s-%05d', $year, $number);
    }

    public function partner()
    {
        return $this->belongsTo(PartnerUser::class, 'partner_user_id');
    }

    public function partnershipModel()
    {
        return $this->belongsTo(PartnershipModel::class);
    }

    public function activate()
    {
        $this->update([
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addMonths($this->duration_months ?? 12),
        ]);
    }

    public function isPending(): bool
    {
        return $this->payment_status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->payment_status === 'completed';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
