<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Skill
 * 
 * @property int $skillID
 * @property string $skillName
 * @property int|null $resumeID
 * 
 * @property Resume|null $resume
 *
 * @package App\Models
 */
class Skill extends Model
{
	protected $table = 'skills';
	protected $primaryKey = 'skillID';
	public $timestamps = false;

	protected $casts = [
		'resumeID' => 'int'
	];

	protected $fillable = [
		'skillName',
		'resumeID'
	];

	public function resume()
	{
		return $this->belongsTo(Resume::class, 'resumeID');
	}
}
