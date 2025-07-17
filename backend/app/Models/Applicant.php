<?php
<<<<<<< HEAD

=======
>>>>>>> Main
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
<<<<<<< HEAD
    protected $table = 'applicant'; // Use exact table name (case-sensitive on some systems)
    protected $primaryKey = 'applicantID'; // Custom primary key
    public $timestamps = false; // Disable timestamps if your table doesn't have created_at/updated_at
=======
    protected $table = 'applicant'; // ✅ Matches your custom table name

    protected $primaryKey = 'applicantID'; // ✅ Matches your primary key

    public $timestamps = false; // ✅ You don't have created_at / updated_at fields
>>>>>>> Main

    protected $fillable = [
        'firstName',
        'middleName',
        'lastName',
        'address',
        'emailAddress',
        'phoneNumber',
        'password'
    ];
<<<<<<< HEAD
=======

    protected $hidden = ['password'];
>>>>>>> Main
}