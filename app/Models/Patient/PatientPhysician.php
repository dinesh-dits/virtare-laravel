<?php

namespace App\Models\Patient;

use Carbon\Carbon;
use App\Models\User\User;
use App\Models\Patient\Patient;
use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientPhysician extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'patientPhysicians';
    use HasFactory;
	protected $guarded = [];

    public function designation()
    {
        return $this->hasOne(GlobalCode::class,'id','designationId');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class,'id');
    }

    public function user()
    {
        return $this->hasOne(User::class,'id','userId');
    }
    
    public function getAgeAttribute($dateOfBirth)
	{
		return Carbon::parse($dateOfBirth)->age;
	}
}
