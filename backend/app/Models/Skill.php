<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    protected $table = 'skills';
    protected $primaryKey = 'skillID';
    public $timestamps = false;

    protected $fillable = [
        'skillName',
        'resumeID',
    ];

    public function resume()
    {
        return $this->belongsTo(Resume::class, 'resumeID', 'resumeID');
    }
}