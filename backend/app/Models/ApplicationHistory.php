<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ApplicationHistory
 * 
 * @property int $appplicationHistoryID
 * @property \Carbon\Carbon|null $historyDate
 * @property \Carbon\Carbon|null $interviewSchedule
 * @property string|null $interviewMode
 * @property string|null $interviewLocation
 * @property string|null $interviewLink
 * @property int|null $applicationID
 * @property int|null $applicationStatusID
 * 
 * @property Application|null $application
 * @property ApplicationStatus|null $status
 */
class ApplicationHistory extends Model
{
    use HasFactory;

    protected $table = 'applicationhistory';
    protected $primaryKey = 'appplicationHistoryID';
    public $timestamps = false;

    protected $casts = [
        'historyDate'        => 'date',
        'interviewSchedule'  => 'datetime',
        'applicationID'      => 'int',
        'applicationStatusID'=> 'int',
    ];

    protected $fillable = [
        'historyDate',
        'interviewSchedule',
        'interviewMode',
        'interviewLocation',
        'interviewLink',
        'applicationID',
        'applicationStatusID'
    ];

    /* --------------------------
       Relationships
    --------------------------- */

    public function application()
    {
        return $this->belongsTo(Application::class, 'applicationID');
    }

    public function status()
    {
        return $this->belongsTo(ApplicationStatus::class, 'applicationStatusID');
    }
}