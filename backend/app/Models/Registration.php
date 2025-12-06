<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Registration
 * 
 * @property int $registrationID
 * @property Carbon $RegistrationDate
 * @property string $RegistrationStatus (Registered/Cancelled/Did not Attend/Attended)
 * @property string|null $CertTrackingID
 * @property Carbon|null $CertGivenDate
 * @property string|null $Certificate (png)
 * @property int|null $trainingID
 * @property int|null $applicantID
 * 
 * @property Training|null $training
 * @property Applicant|null $applicant
 *
 * @package App\Models
 */
class Registration extends Model
{
	protected $table = 'registration';
	protected $primaryKey = 'registrationID';
	public $timestamps = false;

	protected $casts = [
		'registrationDate' => 'datetime',
		'registrationStatus' => 'string',
		'certGivenDate' => 'datetime',
		'registeredDate' => 'datetime',
		'ongoingDate' => 'datetime',
		'completedDate' => 'datetime',
		'certifiedDate' => 'datetime',
		'trainingID' => 'int',
		'applicantID' => 'int'
	];

	protected $fillable = [
		'RegistrationDate',
		'registrationDate', // backward compatibility
		'RegistrationStatus',
		'registrationStatus', // backward compatibility
		'CertTrackingID',
		'certTrackingID', // backward compatibility
		'CertGivenDate',
		'certGivenDate', // backward compatibility
		'Certificate',
		'certificate', // backward compatibility
		'certificatePath', // backward compatibility
		'registeredDate',
		'ongoingDate',
		'completedDate',
		'certifiedDate',
		'trainingID',
		'applicantID',
	];

	public function training()
	{
		return $this->belongsTo(Training::class, 'trainingID');
	}

	public function applicant()
	{
		return $this->belongsTo(Applicant::class, 'applicantID');
	}

	public function scheduleAttendances()
	{
		return $this->hasMany(ScheduleAttendance::class, 'registrationID', 'registrationID');
	}

	public function recordStage(string $status, ?Carbon $timestamp = null, bool $force = false): void
	{
		$map = [
			'registered' => 'registeredDate',
			'ongoing' => 'ongoingDate',
			'in progress' => 'ongoingDate',
			'attended' => 'completedDate',
			'completed' => 'completedDate',
			'certified' => 'certifiedDate',
		];

		$key = strtolower(trim($status));
		if (!isset($map[$key])) {
			return;
		}

		$column = $map[$key];
		if (!$force && !empty($this->$column)) {
			return;
		}

		$value = $timestamp ?? Carbon::now();
		$this->$column = $value;

		if ($column === 'registeredDate' && empty($this->registrationDate)) {
			$this->registrationDate = $value;
		}

		if ($column === 'certifiedDate' && empty($this->certGivenDate)) {
			$this->certGivenDate = $value;
		}
	}
}
