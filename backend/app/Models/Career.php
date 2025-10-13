<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Career
 * 
 * @property int $careerID
 * @property string $position
 * @property string $detailsAndInstructions
 * @property string $qualifications
 * @property string $requirements
 * @property string $applicationLetterAddress
 * @property Carbon $deadlineOfSubmission
 * @property int|null $organizationID
 * 
 * @property Organization|null $organization
 * @property Collection|Application[] $applications
 * @property Collection|Careerbookmark[] $careerbookmarks
 *
 * @package App\Models
 */
class Career extends Model
{
	protected $table = 'career';
	protected $primaryKey = 'careerID';
	public $timestamps = false;

	protected $casts = [
		'deadlineOfSubmission' => 'datetime',
		'organizationID' => 'int'
	];

	protected $fillable = [
		'position',
		'detailsAndInstructions',
		'qualifications',
		'requirements',
		'applicationLetterAddress',
		'deadlineOfSubmission',
		'organizationID'
	];

	public function organization()
	{
		return $this->belongsTo(Organization::class, 'organizationID');
	}

	public function applications()
	{
		return $this->hasMany(Application::class, 'careerID');
	}

	public function careerbookmarks()
	{
		return $this->hasMany(Careerbookmark::class, 'careerID');
	}
}
