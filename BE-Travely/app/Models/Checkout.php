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
    ];

    protected $casts = [
        'paymentDate' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'bookingID', 'bookingID');
    }
}
