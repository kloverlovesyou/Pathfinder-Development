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
 * @property string|null $Certificate (png)
 * @property bool|null $IsSelected
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
		'applicantID' => 'int',
		'IsSelected' => 'boolean'
	];

	protected $fillable = [
		'certificationName',
		'Certificate',
		'certificate', // backward compatibility
		'certificate_path', // backward compatibility
		'IsSelected',
		'resumeID',
		'applicantID',
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
