<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientLogs extends Model
{
    protected $table = 'patientLogs';
    protected $guarded = [];
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = true;
}
