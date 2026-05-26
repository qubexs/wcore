<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppRoute extends Model
{
    protected $table = 'app_routes'; // matches your table

    protected $fillable = [
        'name',
        'uri',
        'method',
        'module',
        'group',
        'category', // if you added this column
    ];

    // Relationships with Role (many-to-many)
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions', 'route_id', 'role_id');
    }
}