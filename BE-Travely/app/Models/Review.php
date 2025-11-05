<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $table = 'review';
    protected $primaryKey = 'reviewID';
    public $timestamps = false;
    const CREATED_AT = 'timestamp';
    const UPDATED_AT = null;

    protected $fillable = [
        'tourID',
        'userID',
        'rating',
        'comment',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
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
}
