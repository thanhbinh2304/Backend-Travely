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
        'images',
        'status',
        'is_verified_purchase',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'updated_at' => 'datetime',
        'approved_at' => 'datetime',
        'images' => 'array',
        'is_verified_purchase' => 'boolean',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_HIDDEN = 'hidden';

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
