<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    use SoftDeletes;

    protected $table = 'settings';

    protected $fillable = [
        'key',
        'value',
        'category',
        'description',
        'data_type',
        'is_public',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_public'  => 'boolean',
        'is_active'  => 'boolean',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relationship: user who last updated this setting.
     * Used in Dashboard audit table.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relationship: user who created this setting.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: only active, non-deleted settings.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1)->whereNull('deleted_at');
    }

    /**
     * Scope: filter by category.
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}