<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Applicant
 * 
 * @property int $applicantID
 * @property string $firstName
 * @property string|null $middleName
 * @property string $lastName
 * @property string $address
 * @property string $emailAddress
 * @property string $phoneNumber
 * @property string $password
 * @property string|null $api_token
 * 
 * @property Collection|Application[] $applications
 * @property Collection|Certification[] $certifications
 * @property Collection|Registration[] $registrations
 * @property Collection|Resume[] $resumes
 *
 * @package App\Models
 */
class Applicant extends Model
{
	protected $table = 'applicant';
	protected $primaryKey = 'applicantID';
	public $timestamps = false;

	protected $hidden = [
		'password',
		'api_token'
	];

	protected $fillable = [
		'displayPicture_directory',
		'firstName',
		'middleName',
		'lastName',
		'address',
		'emailAddress',
		'phoneNumber',
		'password',
		'api_token',
		'email_verification_token',
		'email_verified_at',
	];

	public function applications()
	{
		return $this->hasMany(Application::class, 'applicantID');
	}

	public function certifications()
	{
		return $this->hasMany(Certification::class, 'applicantID');
	}

	public function registrations()
	{
		return $this->hasMany(Registration::class, 'applicantID');
	}

	public function resumes()
	{
		return $this->hasOne(Resume::class, 'applicantID');
	}

}
