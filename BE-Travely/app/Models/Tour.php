<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tour extends Model
{
    use HasFactory;

    protected $table = 'tour';
    protected $primaryKey = 'tourID';
    public $timestamps = true;

    protected $fillable = [
        'title',
        'description',
        'quantity',
        'priceAdult',
        'priceChild',
        'destination',
        'availability',
        'startDate',
        'endDate',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'availability' => 'boolean',
        'priceAdult' => 'decimal:2',
        'priceChild' => 'decimal:2',
        'startDate' => 'date',
        'endDate' => 'date',
    ];

    /**
     * Relationships
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'tourID', 'tourID');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'tourID', 'tourID');
    }

    public function images()
    {
        return $this->hasMany(TourImage::class, 'tourID', 'tourID');
    }

    public function itineraries()
    {
        return $this->hasMany(TourItinerary::class, 'tourID', 'tourID');
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class, 'tourID', 'tourID');
    }

    public function history()
    {
        return $this->hasMany(History::class, 'tourID', 'tourID');
    }
}
