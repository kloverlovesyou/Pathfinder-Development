<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Certification
 * 
 * @property int $certificationID
 * @property string $certificationName
 * @property int|null $resumeID
 * @property int|null $applicantID
 * 
 * @property Resume|null $resume
 * @property Applicant|null $applicant
 *
 * @package App\Models
 */
class Certification extends Model
{
	protected $table = 'certifications';
	protected $primaryKey = 'certificationID';
	public $timestamps = false;

	protected $casts = [
		'resumeID' => 'int',
		'applicantID' => 'int'
	];

	protected $fillable = [
		'certificationName',
		'resumeID',
		'applicantID',
		'IsSelected',
		'certificate_path',
		'certificate', // Allow setting certificate field (binary data) - only for manual uploads
	];

	public function resume()
	{
		return $this->belongsTo(Resume::class, 'resumeID');
	}

	public function applicant()
	{
		return $this->belongsTo(Applicant::class, 'applicantID');
	}
}
