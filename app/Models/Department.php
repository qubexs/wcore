<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'code',
        'description'
    ];

        /**
     * Relationship: Department belongs to many Roles (Layer 2 Access)
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'department_role')
                    ->withTimestamps();
    }

    /**
     * Relationship: Department has many Users
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'department_user')
                    ->withPivot('is_primary')
                    ->withTimestamps();
    }
}