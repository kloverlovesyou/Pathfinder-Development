<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $table = 'tag';
    protected $primaryKey = 'TagID';
    public $timestamps = false;

    protected $fillable = ['tagName', 'TagName']; // Support both camelCase (database) and PascalCase (backward compatibility)

    /**
     * Accessor to get TagName (PascalCase) from tagName (camelCase) for backward compatibility
     */
    public function getTagNameAttribute($value)
    {
        // If accessing as TagName, return tagName value
        return $this->attributes['tagName'] ?? $value;
    }

    /**
     * A tag can be associated with many careers.
     */
    public function careers()
    {
        return $this->belongsToMany(Career::class, 'career_tag', 'TagID', 'careerID');
    }

    /**
     * A tag can be associated with many trainings.
     */
    public function trainings()
    {
        return $this->belongsToMany(Training::class, 'training_tag', 'TagID', 'trainingID');
    }
}
