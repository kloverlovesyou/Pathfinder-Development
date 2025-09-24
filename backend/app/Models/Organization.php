<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model   // ✅ Correct class name
{
    protected $table = 'organization'; // ✅ Use the actual table name
    protected $primaryKey = 'organizationID'; // ✅ Adjust to your PK column
    public $timestamps = false;

protected $fillable = [
    'name',
    'location',
    'websiteURL',
    'emailAddress',
    'password',
    'adminID',
];
}