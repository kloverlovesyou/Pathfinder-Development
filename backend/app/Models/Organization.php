<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Organization
 * 
 * @property int $organizationID
 * @property string|null $logo_directory
 * @property string $name (database column: name or Name)
 * @property string $Name (database column: Name or name)
 * @property string $Location
 * @property string $location (database column: location or Location)
 * @property string|null $WebsiteURL
 * @property string|null $websiteURL (database column: websiteURL or WebsiteURL)
 * @property string|null $phoneNumber
 * @property string $EmailAddress
 * @property string $emailAddress (database column: emailAddress or EmailAddress)
 * @property string $Password
 * @property string $password (database column: password or Password)
	 * @property string|null $registrationRequirements (pdf)
 * @property string|null $RegistrationStatus (Registered, In Review, Verified/Declined)
 * @property int|null $adminID
 * @property string|null $api_token
 * @property Admin|null $admin
 * @property Collection|Career[] $careers
 * @property Collection|Training[] $trainings
 *
 * @package App\Models
 */
class Organization extends Model
{
	protected $table = 'organization';
	protected $primaryKey = 'organizationID';
	public $timestamps = false;

	protected $casts = [
		'adminID' => 'int'
	];

	protected $hidden = [
		'password',
		'api_token'
	];

	protected $fillable = [
		'logo_directory',
		'Logo_directory', // backward compatibility
		'Name',
		'name', // backward compatibility
		'Location',
		'location', // backward compatibility
		'WebsiteURL',
		'websiteURL', // backward compatibility
		'EmailAddress',
		'emailAddress', // backward compatibility
		'Password',
		'password', // backward compatibility
		'registrationRequirements',
		'RegistrationRequirements', // backward compatibility
		'RegistrationStatus',
		'status', // backward compatibility - map status to RegistrationStatus
		'phoneNumber',
		'api_token',
		'adminID',
		'email_verification_token',
		'email_verified_at',
		'statusDate',
	];

	public function admin()
	{
		return $this->belongsTo(Admin::class, 'adminID');
	}

	public function careers()
	{
		return $this->hasMany(Career::class, 'organizationID');
	}

	public function trainings()
	{
		return $this->hasMany(Training::class, 'organizationID');
	}
}
