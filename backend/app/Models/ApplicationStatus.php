<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ApplicationStatus
 * 
 * @property int $applicationStatusID
 * @property string $statusName
 */
class ApplicationStatus extends Model
{
    use HasFactory;

    protected $table = 'applicationstatus';
    protected $primaryKey = 'applicationStatusID';
    public $timestamps = false;

    protected $fillable = [
        'statusName'
    ];
}