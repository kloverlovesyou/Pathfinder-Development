<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resume extends Model
{
    protected $table = 'resume';
    protected $primaryKey = 'resumeID';
    public $timestamps = false;

    protected $fillable = [
        'summary',
        'professionalLink',
        'applicantID',
    ];

    // 👇 Fix relationship (tell Laravel to use applicantID, not applicant_id)
    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'applicantID', 'applicantID');
    }
}
