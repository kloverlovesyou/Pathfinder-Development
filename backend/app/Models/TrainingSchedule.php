<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TrainingSchedule
 * 
 * @property int $trainingScheduleID
 * @property \Carbon\Carbon|null $schedule (datetime)
 * @property \Carbon\Carbon|null $end_time (datetime)
 * @property string|null $mode (varchar)
 * @property string|null $location (varchar)
 * @property string|null $trainingLink (varchar)
 * @property \Carbon\Carbon|null $qr_generated_at (timestamp)
 * @property \Carbon\Carbon|null $attendance_expires_at (timestamp)
 * @property string|null $attendance_key (varchar)
 * @property int|null $trainingID
 * 
 * @property Training|null $training
 */
class TrainingSchedule extends Model
{
    use HasFactory;

    protected $table = 'trainingschedule';
    protected $primaryKey = 'trainingScheduleID';
    public $timestamps = false; // because your table does NOT have created_at / updated_at

    protected $casts = [
        'schedule' => 'datetime',
        'end_time' => 'datetime',
        'qr_generated_at' => 'datetime',
        'attendance_expires_at' => 'datetime',
        'trainingID' => 'int',
    ];

    protected $fillable = [
        'schedule', // Actual database column (datetime)
        'end_time', // Actual database column (datetime)
        'mode', // Actual database column (varchar)
        'location', // Actual database column (varchar)
        'trainingLink', // Actual database column (varchar)
        'qr_generated_at', // Actual database column (timestamp)
        'attendance_expires_at', // Actual database column (timestamp)
        'attendance_key', // Actual database column (varchar)
        'trainingID', // Actual database column (int)
    ];

    // Relationship: a schedule belongs to a training
    public function training()
    {
        return $this->belongsTo(Training::class, 'trainingID', 'trainingID');
    }
}