<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoice';
    protected $primaryKey = 'invoiceID';
    public $timestamps = false;

    protected $fillable = [
        'bookingID',
        'amount',
        'dateIssued',
        'details',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'dateIssued' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'bookingID', 'bookingID');
    }
}
