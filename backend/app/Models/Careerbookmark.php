<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Careerbookmark
 * 
 * @property int $careerBookmarkID
 * @property int $careerID
 * @property int $applicantID
 * 
 * @property Applicant $applicant
 * @property Career $career
 *
 * @package App\Models
 */
class Careerbookmark extends Model
{
	protected $table = 'careerbookmark';
	protected $primaryKey = 'careerBookmarkID';
	public $timestamps = false;

	protected $casts = [
		'careerID' => 'int',
		'applicantID' => 'int'
	];

	protected $fillable = [
		'careerID',
		'applicantID'
	];

	public function applicant()
	{
		return $this->belongsTo(Applicant::class, 'applicantID');
	}

	public function career()
	{
		return $this->belongsTo(Career::class, 'careerID');
	}
}
