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
}
