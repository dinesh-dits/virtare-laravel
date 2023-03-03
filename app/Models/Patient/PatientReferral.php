<?php

namespace App\Models\Patient;

use App\Models\Patient\Patient;
use App\Models\GlobalCode\GlobalCode;
use App\Models\Referral\Referral;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientReferral extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'patientReferrals';
    use HasFactory;
	protected $guarded = [];
    

    public function designation()
    {
        return $this->hasOne(GlobalCode::class,'id','designationId');
    }

    public function patient()
    {
        return $this->hasOne(Patient::class,'id','patientId');
    }

    public function referral()
    {
        return $this->hasOne(Referral::class,'referralId','referralId');
    }

    public function patientReferral()
    {
        return $this->hasOne(Referral::class,'id','referralId');
    }
}
