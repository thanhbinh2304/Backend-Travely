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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['createdAt', 'updatedAt'];

    /**
     * Get createdAt accessor for frontend compatibility
     */
    public function getCreatedAtAttribute()
    {
        $createdAt = $this->attributes['created_at'] ?? null;
        return $createdAt ? \Carbon\Carbon::parse($createdAt)->toIso8601String() : null;
    }

    /**
     * Get updatedAt accessor for frontend compatibility
     */
    public function getUpdatedAtAttribute()
    {
        $updatedAt = $this->attributes['updated_at'] ?? null;
        return $updatedAt ? \Carbon\Carbon::parse($updatedAt)->toIso8601String() : null;
    }

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
