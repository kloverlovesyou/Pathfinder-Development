<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Application
 * 
 * @property int $applicationID
 * @property string $requirement_directory
 * @property Carbon $dateSubmitted
 * @property string $applicationStatus
 * @property Carbon|null $interviewSchedule
 * @property string|null $interviewMode
 * @property string|null $interviewLocation
 * @property string|null $interviewLink
 * @property int|null $careerID
 * @property int|null $applicantID
 * 
 * @property Career|null $career
 * @property Applicant|null $applicant
 *
 * @package App\Models
 */
class Application extends Model
{
	protected $table = 'application';
	protected $primaryKey = 'applicationID';
	public $timestamps = false;

	protected $casts = [
		'dateSubmitted' => 'datetime',
		'interviewSchedule' => 'datetime',
		'careerID' => 'int',
		'applicantID' => 'int'
	];

	protected $fillable = [
		'requirement_directory',
		'dateSubmitted',
		'applicationStatus',
		'interviewSchedule',
		'interviewMode',
		'interviewLocation',
		'interviewLink',
		'careerID',
		'applicantID'
	];

	public function career()
	{
		return $this->belongsTo(Career::class, 'careerID');
	}

	public function applicant()
	{
		return $this->belongsTo(Applicant::class, 'applicantID');
	}
}
