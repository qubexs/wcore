<?php
/**app\Models\BackupStatus.php **/
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupStatus extends Model
{
    protected $table = 'backup_statuses';
    protected $primaryKey = 'id';

    protected $fillable = [
        'job_id',
        'status',
        'description',
        'file_name',
        'file_size',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'file_size' => 'integer', // in KB
        'status' => 'string',
    ];

    /**
     * Status options.
     */
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    /**
     * Get the status label.
     *
     * @return string
     */
    public function getStatusLabel()
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_RUNNING => 'Running',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
            default => 'Unknown',
        };
    }

    /**
     * Get the status color.
     *
     * @return string
     */
    public function getStatusColor()
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_RUNNING => 'info',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_FAILED => 'danger',
            default => 'default',
        };
    }
}