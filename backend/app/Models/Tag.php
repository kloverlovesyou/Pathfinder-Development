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

    protected $fillable = ['TagName'];


    public function careers()
    {
        return $this->belongsToMany(Career::class, 'career_tag', 'TagID', 'careerID');
    }
}
