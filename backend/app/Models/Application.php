<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Application
 * 
 * @property int $applicationID
 * @property string $requirement_directory
 * @property Carbon $dateSubmitted
 * @property string $applicationStatus
 * @property Carbon|null $interviewSchedule
 * @property string|null $interviewMode
 * @property string|null $interviewLocation
 * @property string|null $interviewLink
 * @property int|null $careerID
 * @property int|null $applicantID
 * 
 * @property Career|null $career
 * @property Applicant|null $applicant
 *
 * @package App\Models
 */
class Application extends Model
{
	protected $table = 'application';
	protected $primaryKey = 'applicationID';
	public $timestamps = false;

	protected $casts = [
		'dateSubmitted' => 'datetime',
		'interviewSchedule' => 'datetime',
		'careerID' => 'int',
		'applicantID' => 'int'
	];

	protected $fillable = [
		'requirement_directory',
		'dateSubmitted',
		'applicationStatus',
		'interviewSchedule',
		'interviewMode',
		'interviewLocation',
		'interviewLink',
		'careerID',
		'applicantID'
	];

	protected $guarded = ['organization']; // Prevent setting organization field (it doesn't exist in the table)

	/**
	 * Prevent setting organization attribute (it doesn't exist in the table)
	 */
	public function setOrganizationAttribute($value)
	{
		// Silently ignore - organization doesn't exist as a column
		return;
	}

	/**
	 * Override setAttribute to prevent organization from being set
	 */
	public function setAttribute($key, $value)
	{
		if ($key === 'organization') {
			return $this; // Silently ignore
		}
		return parent::setAttribute($key, $value);
	}

	/**
	 * Override update to ensure organization is never updated
	 */
	public function update(array $attributes = [], array $options = [])
	{
		unset($attributes['organization']);
		return parent::update($attributes, $options);
	}

	/**
	 * Override save to ensure organization is never saved
	 */
	public function save(array $options = [])
	{
		unset($this->attributes['organization']);
		unset($this->original['organization']);
		return parent::save($options);
	}

	public function career()
	{
		return $this->belongsTo(Career::class, 'careerID');
	}

	public function applicant()
	{
		return $this->belongsTo(Applicant::class, 'applicantID');
	}
}
