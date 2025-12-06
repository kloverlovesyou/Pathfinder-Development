<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ScheduleAttendance
 * 
 * @property int $scheduleAttendanceID
 * @property int $registrationID
 * @property int $trainingScheduleID
 * @property \Carbon\Carbon $attendedAt
 * @property string|null $attendanceKey
 * 
 * @property Registration $registration
 * @property TrainingSchedule $schedule
 */
class ScheduleAttendance extends Model
{
    protected $table = 'schedule_attendance';
    protected $primaryKey = 'scheduleAttendanceID';
    public $timestamps = false;

    protected $casts = [
        'registrationID' => 'int',
        'trainingScheduleID' => 'int',
        'attendedAt' => 'datetime',
    ];

    protected $fillable = [
        'registrationID',
        'trainingScheduleID',
        'attendedAt',
        'attendanceKey',
    ];

    public function registration()
    {
        return $this->belongsTo(Registration::class, 'registrationID', 'registrationID');
    }

    public function schedule()
    {
        return $this->belongsTo(TrainingSchedule::class, 'trainingScheduleID', 'trainingScheduleID');
    }
}

