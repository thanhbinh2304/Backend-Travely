<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;

    protected $table = 'history';
    protected $primaryKey = 'historyID';
    public $timestamps = false;
    const CREATED_AT = 'timestamp';
    const UPDATED_AT = null;

    protected $fillable = [
        'userID',
        'tourID',
        'actionType',
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
