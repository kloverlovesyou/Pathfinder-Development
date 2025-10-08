<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    protected $table = 'applicant';
    protected $primaryKey = 'applicantID';
    public $timestamps = false;

    protected $fillable = [
        'firstName',
        'middleName',
        'lastName',
        'address',
        'emailAddress',
        'phoneNumber',
        'password',
        'api_token',
    ];

    // One applicant has one resume
    public function resume()
    {
        return $this->hasOne(Resume::class, 'applicantID', 'applicantID');
    }
}