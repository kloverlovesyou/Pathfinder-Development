<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens; // ✅ Import HasApiTokens

class Admin extends Model
{
    use HasApiTokens; // ✅ Add this trait

    protected $table = 'admin';
    protected $primaryKey = 'adminID';
    public $timestamps = false;

    protected $hidden = [
        'password',
    ];

    protected $fillable = [
        'name',
        'location',
        'websiteURL',
        'emailAddress',
        'password',
        'login_otp',
        'login_otp_expires_at',
    ];

    public function organizations()
    {
        return $this->hasMany(Organization::class, 'adminID');
    }
}