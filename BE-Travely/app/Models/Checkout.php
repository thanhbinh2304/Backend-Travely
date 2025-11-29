<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checkout extends Model
{
    use HasFactory;

    protected $table = 'checkout';
    protected $primaryKey = 'checkoutID';
    public $timestamps = false;

    protected $fillable = [
        'bookingID',
        'paymentMethod',
        'paymentDate',
        'amount',
        'paymentStatus',
        'transactionID',
        'paymentData',
        'qrCode',
        'createdAt',
        'updatedAt',
        'refundDate',
        'refundAmount',
        'refundReason',
        'refundBy',
    ];

    protected $casts = [
        'paymentDate' => 'datetime',
        'amount' => 'decimal:2',
        'paymentData' => 'array',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
        'refundDate' => 'datetime',
        'refundAmount' => 'decimal:2',
    ];

    /**
     * Payment status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';

    /**
     * Payment method constants
     */
    const METHOD_MOMO = 'momo';
    const METHOD_BANK_TRANSFER = 'bank_transfer';
    const METHOD_CASH = 'cash';

    /**
     * Relationships
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'bookingID', 'bookingID');
    }

    /**
     * Scope for pending payments
     */
    public function scopePending($query)
    {
        return $query->where('paymentStatus', self::STATUS_PENDING);
    }

    /**
     * Scope for completed payments
     */
    public function scopeCompleted($query)
    {
        return $query->where('paymentStatus', self::STATUS_COMPLETED);
    }

    /**
     * Check if payment is pending
     */
    public function isPending()
    {
        return $this->paymentStatus === self::STATUS_PENDING;
    }

    /**
     * Check if payment is completed
     */
    public function isCompleted()
    {
        return $this->paymentStatus === self::STATUS_COMPLETED;
    }

    /**
     * Check if payment is failed
     */
    public function isFailed()
    {
        return $this->paymentStatus === self::STATUS_FAILED;
    }
}
