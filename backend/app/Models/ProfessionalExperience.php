<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfessionalExperience extends Model
{
    use HasFactory;

    // ✅ Explicit table name
    protected $table = 'experience';

    // ✅ Primary key
    protected $primaryKey = 'experienceID';

    // ✅ If no created_at / updated_at
    public $timestamps = false;
    // ✅ Mass assignable fields
    protected $fillable = [
        'jobTitle',
        'companyName',
        'companyAddress',   // ✅ include this
        'startYear',
        'endYear',
        'resumeID',
    ];
    // ✅ Relationship (each experience belongs to one resume)
    public function resume()
    {
        return $this->belongsTo(Resume::class, 'resumeID', 'resumeID');
    }
}