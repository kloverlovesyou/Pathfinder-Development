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
 * @property Carbon $registrationDate
 * @property Carbon $registrationStatus
 * @property string|null $certTrackingID
 * @property Carbon|null $certGivenDate
 * @property string|null $certificate
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
		'trainingID' => 'int',
		'applicantID' => 'int'
	];

	protected $fillable = [
		'registrationDate',
		'registrationStatus',
		'certTrackingID',
		'certGivenDate',
		'certificate',
		'trainingID',
		'applicantID'
	];

	public function training()
	{
		return $this->belongsTo(Training::class, 'trainingID');
	}

	public function applicant()
	{
		return $this->belongsTo(Applicant::class, 'applicantID');
	}
}
