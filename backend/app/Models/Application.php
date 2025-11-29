<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Application
 * 
 * @property int $applicationID
 * @property string|null $Requirements (pdf)
 * @property int|null $careerID
 * @property int|null $applicantID
 * 
 * @property Career|null $career
 * @property Applicant|null $applicant
 */
class Application extends Model
{
    protected $table = 'application';
    protected $primaryKey = 'applicationID';
    public $timestamps = false;

    protected $casts = [
        'careerID'          => 'int',
        'applicantID'       => 'int',
        'dateSubmitted'     => 'datetime',
        'appliedDate'       => 'datetime',
        'screenDate'        => 'datetime',
        'pendingDate'       => 'datetime',
        'hiredDate'         => 'datetime',
        'declinedDate'      => 'datetime',
        'interviewSchedule' => 'datetime',
    ];

    protected $fillable = [
        'Requirements',
        'requirement_directory', // backward compatibility
        'careerID',
        'applicantID',
        'dateSubmitted',
        'applicationStatus',
        'interviewSchedule',
        'interviewMode',
        'interviewLocation',
        'interviewLink',
        'appliedDate',
        'screenDate',
        'pendingDate',
        'hiredDate',
        'declinedDate',
    ];

    /* -----------------------------------------------------------
     | KEEPING THIS EXACT BLOCK AS YOU REQUESTED
     |------------------------------------------------------------ */

    protected $guarded = ['organization']; // Prevent setting organization field

    /**
     * Prevent setting organization attribute
     */
    public function setOrganizationAttribute($value)
    {
        return; // silently ignore
    }

    /**
     * Override setAttribute to prevent organization from being set
     */
    public function setAttribute($key, $value)
    {
        if ($key === 'organization') {
            return $this; // silently ignore
        }
        return parent::setAttribute($key, $value);
    }

    /**
     * Override update to ensure organization is never updated
     */
    public function update(array $attributes = [], array $options = [])
    {
        unset($attributes['organization']);
        return parent::update($attributes, $options);
    }

    /**
     * Override save to ensure organization is never saved
     */
    public function save(array $options = [])
    {
        unset($this->attributes['organization']);
        unset($this->original['organization']);
        return parent::save($options);
    }

    /* -----------------------------------------------------------
     | RELATIONSHIPS
     |------------------------------------------------------------ */

    public function career()
    {
        return $this->belongsTo(Career::class, 'careerID');
    }

    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'applicantID');
    }

    /**
     * An application can have many history entries.
     */
    public function history()
    {
        return $this->hasMany(ApplicationHistory::class, 'applicationID');
    }
}