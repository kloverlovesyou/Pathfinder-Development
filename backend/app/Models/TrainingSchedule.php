<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingSchedule extends Model
{
    use HasFactory;

    protected $table = 'trainingschedule';
    protected $primaryKey = 'trainingScheduleID';
    public $timestamps = false; // because your table does NOT have created_at / updated_at

    protected $fillable = [
        'schedule',
        'end_time',
        'mode',
        'location',
        'trainingLink',
        'qr_generated_at',
        'attendance_expires_at',
        'trainingID',
        'attendance_key',
    ];

    // Relationship: a schedule belongs to a training
    public function training()
    {
        return $this->belongsTo(Training::class, 'trainingID', 'trainingID');
    }
}