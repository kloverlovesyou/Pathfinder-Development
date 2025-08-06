<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $table = 'organization'; // Use exact table name (case-sensitive on some systems)
    protected $primaryKey = 'organizationID'; // Custom primary key
    public $timestamps = false; // Disable timestamps if your table doesn't have created_at/updated_at

    protected $fillable = [
        'name',
        'location',
        'websiteURL',
        'phoneNumber',
        'password',
        'adminID'
    ];
    use HasFactory;
}
