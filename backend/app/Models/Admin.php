<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Admin
 * 
 * @property int $AdminID
 * @property string $Name
 * @property string $Location
 * @property string|null $WebsiteURL
 * @property string $EmailAddress
 * @property string $Password
 * 
 * @property Collection|Organization[] $organizations
 *
 * @package App\Models
 */
class Admin extends Model
{
	protected $table = 'admin';
	protected $primaryKey = 'adminID';
	public $timestamps = false;

	protected $hidden = [
		'password'
	];

	protected $fillable = [
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
	];

	public function organizations()
	{
		return $this->hasMany(Organization::class, 'adminID');
	}
}
