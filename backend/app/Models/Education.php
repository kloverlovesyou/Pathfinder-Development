<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Education extends Model
{

    protected $table = 'education';
    protected $primaryKey = 'educationID';
    public $timestamps = false;

    protected $fillable = [
        'educationLevel',
        'major',
        'institutionName',
        'institutionAddress',
        'graduationYear',
        'resumeID',
    ];
    

        // ✅ Relationship (each experience belongs to one resume)
    public function resume()
    {
        return $this->belongsTo(Resume::class, 'resumeID', 'resumeID');
    }
}
