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

    public function experiences()
    {
        return $this->hasMany(ProfessionalExperience::class, 'resumeID', 'resumeID');
    }

    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'applicantID', 'applicantID');
    }

    // ✅ Relationship with Skill
    public function skills()
    {
        return $this->hasMany(Skill::class, 'resumeID', 'resumeID');
    }
}