<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Trainingbookmark
 * 
 * @property int $trainingBookmarkID
 * @property int $trainingID
 * @property int $applicantID
 * 
 * @property Applicant $applicant
 * @property Training $training
 *
 * @package App\Models
 */
class Trainingbookmark extends Model
{
	protected $table = 'trainingbookmark';
	protected $primaryKey = 'trainingBookmarkID';
	public $timestamps = false;

	protected $casts = [
		'trainingID' => 'int',
		'applicantID' => 'int'
	];

	protected $fillable = [
		'trainingID',
		'applicantID'
	];

	public function applicant()
	{
		return $this->belongsTo(Applicant::class, 'applicantID');
	}

	public function training()
	{
		return $this->belongsTo(Training::class, 'trainingID');
	}
}
