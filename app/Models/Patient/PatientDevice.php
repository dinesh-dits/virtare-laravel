<?php

namespace App\Models\Patient;

use App\Models\Patient\Patient;
use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientDevice extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'patientDevices';
    use HasFactory;
	protected $guarded = [];
    
    public function otherDevice()
    {
        return $this->hasOne(GlobalCode::class,'id','otherDeviceId');
    }

    public function patient()
    {
        return $this->hasMany(Patient::class,'id','patientId');
    }
}
