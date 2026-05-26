<?php
// app\Models\DashboardLayout.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashboardLayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'profile_id',
        'layout_data'
    ];

    protected $casts = [
        'layout_data' => 'array'
    ];

    /**
     * Get the user that owns the layout
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the profile this layout belongs to
     */
    public function profile()
    {
        return $this->belongsTo(DashboardProfile::class, 'profile_id');
    }
}