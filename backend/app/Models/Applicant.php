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
 * @property string|null $DisplayPicture_directory
 * @property string $FirstName (database column: FirstName or firstName)
 * @property string $firstName (database column: firstName or FirstName)
 * @property string|null $MiddleName
 * @property string|null $middleName (database column: middleName or MiddleName)
 * @property string $LastName (database column: LastName or lastName)
 * @property string $lastName (database column: lastName or LastName)
 * @property string $Address
 * @property string $address (database column: address or Address)
 * @property string $EmailAddress (database column: EmailAddress or emailAddress)
 * @property string $emailAddress (database column: emailAddress or EmailAddress)
 * @property string $PhoneNumber (database column: PhoneNumber or phoneNumber)
 * @property string $phoneNumber (database column: phoneNumber or PhoneNumber)
 * @property string $Password
 * @property string $password (database column: password or Password)
 * @property string|null $api_token
 * 
 * @property Collection|Application[] $applications
 * @property Collection|Certification[] $certifications
 * @property Collection|Registration[] $registrations
 * @property Resume|null $resume
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
		'displayPicture_directory', // actual database column (camelCase, quoted in PostgreSQL)
		'FirstName',
		'firstName', // backward compatibility
		'MiddleName',
		'middleName', // backward compatibility
		'LastName',
		'lastName', // backward compatibility
		'Address',
		'address', // backward compatibility
		'EmailAddress',
		'emailAddress', // backward compatibility
		'PhoneNumber',
		'phoneNumber', // backward compatibility
		'Password',
		'password', // backward compatibility
		'api_token',
		'email_verification_token',
		'email_verified_at',
	];

	/**
	 * Map DisplayPicture_directory to the actual database column
	 * The database column is "displayPicture_directory" (camelCase, quoted in PostgreSQL)
	 * This mutator handles when code tries to set DisplayPicture_directory (capital D and P)
	 * or displaypicture_directory (all lowercase) - PHP method names are case-insensitive
	 */
	public function setDisplayPictureDirectoryAttribute($value)
	{
		$this->attributes['displayPicture_directory'] = $value;
	}

	public function getDisplayPictureDirectoryAttribute()
	{
		return $this->attributes['displayPicture_directory'] ?? null;
	}

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
