<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourImage extends Model
{
    use HasFactory;

    protected $table = 'tour_images';
    protected $primaryKey = 'imageID';
    public $timestamps = false;
    const CREATED_AT = 'uploadDate';
    const UPDATED_AT = null;

    protected $fillable = [
        'tourID',
        'imageURL',
    ];

    protected $casts = [
        'uploadDate' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function tour()
    {
        return $this->belongsTo(Tour::class, 'tourID', 'tourID');
    }
}
