<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Experience
 * 
 * @property int $experienceID
 * @property string $jobTitle
 * @property string $companyName
 * @property string $companyAddress
 * @property Carbon $startYear
 * @property Carbon $endYear
 * @property int|null $resumeID
 * 
 * @property Resume|null $resume
 *
 * @package App\Models
 */
class Experience extends Model
{
	protected $table = 'experience';
	protected $primaryKey = 'experienceID';
	public $timestamps = false;

	protected $casts = [
		'startYear' => 'datetime',
		'endYear' => 'datetime',
		'resumeID' => 'int'
	];

	protected $fillable = [
		'jobTitle',
		'companyName',
		'companyAddress',
		'startYear',
		'endYear',
		'resumeID'
	];

	public function resume()
	{
		return $this->belongsTo(Resume::class, 'resumeID');
	}
}
