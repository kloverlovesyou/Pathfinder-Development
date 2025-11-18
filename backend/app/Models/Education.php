<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Education
 * 
 * @property int $educationID
 * @property string $educationLevel
 * @property string|null $major
 * @property string $institutionName
 * @property string $institutionAddress
 * @property int $graduationYear
 * @property int|null $resumeID
 * 
 * @property Resume|null $resume
 *
 * @package App\Models
 */
class Education extends Model
{
	protected $table = 'education';
	protected $primaryKey = 'educationID';
	public $timestamps = false;

	protected $casts = [
		'graduationYear' => 'int',
		'resumeID' => 'int'
	];

	protected $fillable = [
		'educationLevel',
		'major',
		'institutionName',
		'institutionAddress',
		'graduationYear',
		'resumeID'
	];

	public function resume()
	{
		return $this->belongsTo(Resume::class, 'resumeID');
	}
}
