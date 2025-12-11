<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $table = 'promotion';
    protected $primaryKey = 'promotionID';
    public $timestamps = false;

    protected $fillable = [
        'code',
        'description',
        'discount',
        'startDate',
        'endDate',
        'quantity',
    ];

    protected $casts = [
        'discount' => 'decimal:2',
        'startDate' => 'date',
        'endDate' => 'date',
    ];
}
