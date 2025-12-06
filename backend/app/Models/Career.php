<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Career
 *
 * @property int $careerID
 * @property string $position
 * @property string $placeOfAssignment
 * @property string $details
 * @property string $qualificationStandard
 * @property string|null $pdf_directory
 * @property Carbon|null $postingDate
 * @property Carbon|null $closingDate
 * @property int|null $trainingsAttendedPercentage
 * @property int|null $organizationID
 *
 * @property Organization|null $organization
 * @property Collection|Application[] $applications
 */
class Career extends Model
{
    protected $table = 'career';
    protected $primaryKey = 'careerID';
    public $timestamps = false;

    protected $casts = [
        'postingDate' => 'date',
        'closingDate' => 'date',
        'trainingsAttendedPercentage' => 'int',
        'organizationID' => 'int',
    ];

    protected $fillable = [
        'position',
        'placeOfAssignment',
        'details',
        'qualificationStandard',
        'pdf_directory',
        'postingDate',
        'closingDate',
        'trainingsAttendedPercentage',
        'organizationID',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organizationID');
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'careerID');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'career_tag', 'careerID', 'TagID');
    }

    /**
     * Selected trainings for this career (Organization's Choice)
     */
    public function selectedTrainings(): BelongsToMany
    {
        return $this->belongsToMany(Training::class, 'organizationschoice', 'careerID', 'trainingID', 'careerID', 'trainingID')
            ->withPivot('organizationID'); // Include organizationID in pivot for filtering
    }
}