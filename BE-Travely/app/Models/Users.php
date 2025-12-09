<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Users extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'userID';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'userID',
        'userName',
        'passWord',
        'phoneNumber',
        'address',
        'email',
        'role_id',
        'created_by',
        'updated_by',
        'refresh_token',
        'email_verified',
        'verification_token',
        'verification_token_expires_at',
        'google_id',
        'facebook_id',
        'avatar_url',
        'is_admin',
        'is_active',
        'last_login',
    ];

    protected $hidden = [
        'passWord',
        'refresh_token',
        'verification_token',
    ];

    protected $casts = [
        'email_verified' => 'boolean',
        'is_admin' => 'boolean',
        'verification_token_expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_login' => 'datetime',
    ];

    /**
     * Boot function - auto-generate UUID on creation
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->userID)) {
                $model->userID = (string) Str::uuid();
            }
        });
    }

    /**
     * Mutator: auto-hash password when set
     */
    public function setPassWordAttribute($value)
    {
        if ($value && !str_starts_with($value, '$2y$')) {
            $this->attributes['passWord'] = bcrypt($value);
        } else {
            $this->attributes['passWord'] = $value;
        }
    }

    /**
     * Override for Laravel Auth to use passWord column
     */
    public function getAuthPassword()
    {
        return $this->passWord;
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims()
    {
        return [
            'userName' => $this->userName,
            'email' => $this->email,
            'role_id' => $this->role_id,
        ];
    }

    /**
     * Relationships
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'userID', 'userID');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'userID', 'userID');
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class, 'userID', 'userID');
    }

    public function history()
    {
        return $this->hasMany(History::class, 'userID', 'userID');
    }

    public function conversationsAsUser()
    {
        return $this->hasMany(ChatConversation::class, 'user_id', 'userID');
    }

    public function conversationsAsAdmin()
    {
        return $this->hasMany(ChatConversation::class, 'admin_id', 'userID');
    }

    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class, 'sender_id', 'userID');
    }
}
