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

    protected $appends = ['imageUrl'];

    /**
     * Get imageUrl accessor for frontend compatibility
     */
    public function getImageUrlAttribute()
    {
        // Force HTTPS and correct port for image URLs - use attributes array to avoid infinite loop
        $url = $this->attributes['imageURL'] ?? '';

        // Replace http with https
        $url = str_replace('http://', 'https://', $url);

        // Replace port 8000 with 8443 (proxy port)
        $url = str_replace('127.0.0.1:8000', '127.0.0.1:8443', $url);

        return $url;
    }

    /**
     * Relationships
     */
    public function tour()
    {
        return $this->belongsTo(Tour::class, 'tourID', 'tourID');
    }
}
