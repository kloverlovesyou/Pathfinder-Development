<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    protected $table = 'applicant'; // ✅ Matches your custom table name

    protected $primaryKey = 'applicantID'; // ✅ Matches your primary key

    public $timestamps = false; // ✅ You don't have created_at / updated_at fields

    protected $fillable = [
        'firstName',
        'middleName',
        'lastName',
        'address',
        'emailAddress',
        'phoneNumber',
        'password'
    ];

    protected $hidden = ['password'];
}