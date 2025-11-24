<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourItinerary extends Model
{
    use HasFactory;

    protected $table = 'tour_itinerary';
    protected $primaryKey = 'itineraryID';
    public $timestamps = false;

    protected $fillable = [
        'tourID',
        'dayNumber',
        'destination',
        'activity',
    ];

    /**
     * Relationships
     */
    public function tour()
    {
        return $this->belongsTo(Tour::class, 'tourID', 'tourID');
    }
}
