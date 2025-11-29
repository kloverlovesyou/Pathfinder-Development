<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationsChoice extends Model
{
    use HasFactory;

    protected $table = 'organizationschoice';
    protected $primaryKey = 'organizationsChoiceID';
    public $timestamps = false;

    protected $fillable = [
        'organizationsChoiceID',
        'trainingID',
        'organizationID',
        'careerID',
    ];

    /**
     * Relationship: belongs to Training
     */
    public function training()
    {
        return $this->belongsTo(Training::class, 'trainingID', 'trainingID');
    }

    /**
     * Relationship: belongs to Organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organizationID', 'organizationID');
    }

    /**
     * Relationship: belongs to Career
     */
    public function career()
    {
        return $this->belongsTo(Career::class, 'careerID', 'careerID');
    }
}