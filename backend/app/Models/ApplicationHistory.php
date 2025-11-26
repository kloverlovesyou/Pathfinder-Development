<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ApplicationHistory
 * 
 * @property int $applicationHistoryID
 * @property \Carbon\Carbon|null $HistoryDate
 * @property \Carbon\Carbon|null $InterviewSchedule
 * @property string|null $InterviewMode (Onsite/Online)
 * @property string|null $InterviewLocation (if Onsite)
 * @property string|null $InterviewLink (if Online)
 * @property int|null $applicationID
 * @property int|null $applicationStatusID
 * 
 * @property Application|null $application
 * @property ApplicationStatus|null $status
 * 
 * @package App\Models
 */
class ApplicationHistory extends Model
{
    use HasFactory;

    protected $table = 'applicationhistory';
    protected $primaryKey = 'applicationHistoryID'; // Fixed typo: was appplicationHistoryID
    public $timestamps = false;

    protected $casts = [
        'HistoryDate'        => 'date',
        'historyDate'        => 'date', // backward compatibility
        'InterviewSchedule'  => 'datetime',
        'interviewSchedule'  => 'datetime', // backward compatibility
        'applicationID'      => 'int',
        'applicationStatusID'=> 'int',
    ];

    protected $fillable = [
        'HistoryDate',
        'historyDate', // backward compatibility
        'InterviewSchedule',
        'interviewSchedule', // backward compatibility
        'InterviewMode',
        'interviewMode', // backward compatibility
        'InterviewLocation',
        'interviewLocation', // backward compatibility
        'InterviewLink',
        'interviewLink', // backward compatibility
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