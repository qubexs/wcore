<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'guard_name',
        'description',
    ];

    /**
     * The permissions that belong to this role.
     * Uses the role_permissions pivot table to link to permissions table.
     */
    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,      // The model we're linking to
            'role_permissions',     // The pivot table name
            'role_id',              // Foreign key for this model on pivot table
            'permission_id'         // Foreign key for Permission model on pivot table
        );
    }


        /**
     * Relationship: Role belongs to many Departments (Layer 2 Access)
     */
    public function departments()
    {
        return $this->belongsToMany(Department::class, 'department_role')
                    ->withTimestamps();
    }

    /**
     * Relationship: Role has many Users
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'model_has_roles', 'role_id', 'model_id')
                    ->where('model_type', User::class);
    }
}