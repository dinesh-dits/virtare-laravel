<?php

namespace App\Models\Patient;

use App\Models\Patient\Patient;
use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientEmergencyContact extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'patientEmergencyContacts';
    use HasFactory;
    protected $guarded = [];



    public function globalCode()
    {
        return $this->hasOne(GlobalCode::class, 'id');
    }

    // public function user()
    // {
    //     return $this->hasOne(User::class, 'id');
    // }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'id');
    }

    public function gender()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'genderId');
    }

    public function contactType()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'contactTypeId');
    }

    public function contactTime()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'contactTimeId');
    }

}
