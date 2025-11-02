<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'organization';
    protected $primaryKey = 'organizationID';
    public $timestamps = false;

    protected $casts = [
        'adminID' => 'int',
    ];

    protected $hidden = [
        'password',
    ];

    protected $fillable = [
        'name',
        'location',
        'websiteURL',
        'emailAddress',
        'password',
        'adminID',
    ];

    // ✅ Relationships
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'adminID');
    }

    public function careers(): HasMany
    {
        return $this->hasMany(Career::class, 'organizationID');
    }

    public function trainings(): HasMany
    {
        return $this->hasMany(Training::class, 'organizationID');
    }
}