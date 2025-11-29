<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\OrganizationsChoice;

class Training extends Model
{
    protected $table = 'training';
    protected $primaryKey = 'trainingID';
    public $timestamps = false;

    protected $casts = [
        'organizationID' => 'int',
    ];

    protected $fillable = [
        'Title',
        'title', // backward compatibility
        'Description',
        'description', // backward compatibility
        'organizationID',
    ];

    /**
     * A training belongs to an organization.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organizationID', 'organizationID');
    }

    /**
     * A training can have many registrations.
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'trainingID', 'trainingID');
    }

    /**
     * A training can have many schedules.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(TrainingSchedule::class, 'trainingID', 'trainingID');
    }

    /**
     * A training can have many tags.
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'training_tag', 'trainingID', 'TagID');
    }

    /**
     * A training can have many organization choice mappings.
     */
    public function organizationChoices(): HasMany
    {
        return $this->hasMany(OrganizationsChoice::class, 'trainingID', 'trainingID');
    }
}