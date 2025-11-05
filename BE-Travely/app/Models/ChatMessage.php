<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ChatMessage extends Model
{
    use HasFactory;

    protected $table = 'chat_messages';
    protected $primaryKey = 'message_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'message_id',
        'conversation_id',
        'sender_id',
        'parent_message_id',
        'message_text',
        'message_type',
        'attachment_url',
        'attachment_name',
        'attachment_size',
        'is_edited',
        'edited_at',
        'is_deleted',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_edited' => 'boolean',
        'is_deleted' => 'boolean',
        'is_read' => 'boolean',
        'edited_at' => 'datetime',
        'read_at' => 'datetime',
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
            if (empty($model->message_id)) {
                $model->message_id = (string) Str::uuid();
            }
        });
    }

    /**
     * Relationships
     */
    public function conversation()
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id', 'conversation_id');
    }

    public function sender()
    {
        return $this->belongsTo(Users::class, 'sender_id', 'userID');
    }

    public function parent()
    {
        return $this->belongsTo(ChatMessage::class, 'parent_message_id', 'message_id');
    }

    public function replies()
    {
        return $this->hasMany(ChatMessage::class, 'parent_message_id', 'message_id');
    }
}
