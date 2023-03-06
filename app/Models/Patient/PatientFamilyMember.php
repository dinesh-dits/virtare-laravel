<?php

namespace App\Models\Patient;

use Carbon\Carbon;
use App\Models\User\User;
use App\Models\Patient\Patient;
use App\Models\GlobalCode\GlobalCode;
use App\Models\Role\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientFamilyMember extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'patientFamilyMembers';
    use HasFactory;
    protected $guarded = [];


    public function user()
    {
        return $this->hasOne(User::class, 'id','userId');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class,'patientId');
    }

    public function gender()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'genderId');
    }
    public function language()
    {
        return $this->belongsTo(GlobalCode::class, 'languageId');
    }

    public function contactType()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'contactTypeId');
    }

    public function contactTime()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'contactTimeId');
    }

    public function relation()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'relationId');
    }

    public function roles(){
        return $this->belongsTo(Role::class, 'roleId');
    }
    public function getAgeAttribute($dateOfBirth)
	{
		return Carbon::parse($dateOfBirth)->age;
	}

    public function patients(){
        return $this->belongsTo(Patient::class,'patientId');
    }
}
