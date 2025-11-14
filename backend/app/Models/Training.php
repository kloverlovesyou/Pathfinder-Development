<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; 
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Training
 * 
 * @property int $trainingID
 * @property string $title
 * @property string $description
 * @property Carbon $schedule
 * @property string $mode
 * @property string|null $location
 * @property string|null $trainingLink
 * @property int|null $organizationID
 * 
 * @property Organization|null $organization
 * @property Collection|Registration[] $registrations
 * @property Collection|Trainingbookmark[] $trainingbookmarks
 *
 * @package App\Models
 */

class Training extends Model
{
	protected $table = 'training';
	protected $primaryKey = 'trainingID';
	public $timestamps = false;

	protected $casts = [
		'schedule' => 'datetime',
		'end_time' => 'datetime', 
		'organizationID' => 'int',

	];

	protected $fillable = [
		'title',
		'description',
		'schedule',
		'end_time',
		'mode',
		'location',
		'trainingLink',
		'organizationID',
		'attendance_key',          // ✅ Add this
		'attendance_expires_at',   // ✅ Add this
		
	];


	public function organization(): BelongsTo
	{
		return $this->belongsTo(Organization::class, 'organizationID', 'organizationID');
	}

	public function registrations()
	{
		return $this->hasMany(Registration::class, 'trainingID');
	}

	public function trainingbookmarks()
	{
		return $this->hasMany(Trainingbookmark::class, 'trainingID');
	}

	
	public function tags()
	{
		return $this->belongsToMany(Tag::class, 'training_tag', 'trainingID', 'TagID');
	}

}
