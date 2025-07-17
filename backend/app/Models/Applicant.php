<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    protected $table = 'Applicant'; // Capitalized table name
    protected $primaryKey = 'applicantID';
    public $timestamps = false; // Your table doesn't have created_at/updated_at

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