<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Resume
 * 
 * @property int $resumeID
 * @property string $summary
 * @property string $professionalLink
 * @property int|null $applicantID
 * 
 * @property Applicant|null $applicant
 * @property Collection|Certification[] $certifications
 * @property Collection|Education[] $education
 * @property Collection|Experience[] $experiences
 * @property Collection|Skill[] $skills
 *
 * @package App\Models
 */
class Resume extends Model
{
	protected $table = 'resume';
	protected $primaryKey = 'resumeID';
	public $timestamps = false;

	protected $casts = [
		'applicantID' => 'int'
	];

	protected $fillable = [
		'summary',
		'professionalLink',
		'applicantID'
	];

	public function applicant()
	{
		return $this->belongsTo(Applicant::class, 'applicantID');
	}

	public function certifications()
	{
		return $this->hasMany(Certification::class, 'resumeID');
	}

	public function education()
	{
		return $this->hasMany(Education::class, 'resumeID');
	}

	public function experiences()
	{
		return $this->hasMany(Experience::class, 'resumeID');
	}

	public function skills()
	{
		return $this->hasMany(Skill::class, 'resumeID');
	}
}
