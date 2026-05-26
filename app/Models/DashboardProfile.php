<?php
// app\Models\DashboardProfile.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashboardProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'is_default'
    ];

    protected $casts = [
        'is_default' => 'boolean'
    ];

    /**
     * Get the user that owns the profile
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the layout for this profile
     */
    public function layout()
    {
        return $this->hasOne(DashboardLayout::class, 'profile_id');
    }

    /**
     * Get the widget count for this profile
     */
    public function getWidgetCountAttribute()
    {
        if ($this->layout && $this->layout->layout_data) {
            return count($this->layout->layout_data);
        }
        return 0;
    }

    /**
     * Get formatted last modified date
     */
    public function getLastModifiedAttribute()
    {
        return $this->updated_at->diffForHumans();
    }
}