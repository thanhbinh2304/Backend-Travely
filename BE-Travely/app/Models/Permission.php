<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $table = 'permissions';
    protected $primaryKey = 'permission_id';

    protected $fillable = [
        'created_by',
        'updated_by',
        'api_path',
        'method',
        'module',
        'name',
        'active',
        'description',
    ];

    protected $casts = [
        'active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'permission_role',
            'permission_role',
            'role_id'
        );
    }
}
