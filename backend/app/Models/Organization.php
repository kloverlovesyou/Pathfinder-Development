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
 * @property string $name
 * @property string $location
 * @property string|null $websiteURL
 * @property string $emailAddress
 * @property string $password
 * @property string|null $api_token
 * @property int|null $adminID
 * 
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
		'name',
		'location',
		'websiteURL',
		'emailAddress',
		'password',
		'api_token',
		'adminID'
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
