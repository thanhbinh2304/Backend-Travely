<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'booking';
    protected $primaryKey = 'bookingID';
    public $timestamps = false;

    protected $fillable = [
        'tourID',
        'userID',
        'bookingDate',
        'numAdults',
        'numChildren',
        'totalPrice',
        'paymentStatus',
        'bookingStatus',
        'specialRequests',
    ];

    protected $casts = [
        'bookingDate' => 'datetime',
        'totalPrice' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(Users::class, 'userID', 'userID');
    }

    public function tour()
    {
        return $this->belongsTo(Tour::class, 'tourID', 'tourID');
    }

    public function checkout()
    {
        return $this->hasOne(Checkout::class, 'bookingID', 'bookingID');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'bookingID', 'bookingID');
    }

    public function conversations()
    {
        return $this->hasMany(ChatConversation::class, 'bookingID', 'bookingID');
    }
}
