<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ChatConversation extends Model
{
    use HasFactory;

    protected $table = 'chat_conversations';
    protected $primaryKey = 'conversation_id';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'conversation_id',
        'user_id',
        'admin_id',
        'bookingID',
        'status',
        'last_message_at',
        'is_pinned',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'last_message_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot function - auto-generate UUID on creation
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->conversation_id)) {
                $model->conversation_id = (string) Str::uuid();
            }
        });
    }

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'userID');
    }

    public function admin()
    {
        return $this->belongsTo(Users::class, 'admin_id', 'userID');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'bookingID', 'bookingID');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id', 'conversation_id');
    }
}
