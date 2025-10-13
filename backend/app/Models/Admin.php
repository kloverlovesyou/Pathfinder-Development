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
 * @property int $adminID
 * @property string $name
 * @property string $location
 * @property string|null $websiteURL
 * @property string $emailAddress
 * @property string $password
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
		'name',
		'location',
		'websiteURL',
		'emailAddress',
		'password'
	];

	public function organizations()
	{
		return $this->hasMany(Organization::class, 'adminID');
	}
}
