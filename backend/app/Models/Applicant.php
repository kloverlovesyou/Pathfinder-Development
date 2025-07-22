<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    protected $table = 'applicant'; // Use exact table name (case-sensitive on some systems)
    protected $primaryKey = 'applicantID'; // Custom primary key
    public $timestamps = false; // Disable timestamps if your table doesn't have created_at/updated_at

    protected $fillable = [
        'firstName',
        'middleName',
        'lastName',
        'address',
        'emailAddress',
        'phoneNumber',
        'password'
    ];
}