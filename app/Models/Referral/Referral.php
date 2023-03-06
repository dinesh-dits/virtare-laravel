<?php

namespace App\Models\Referral;

use App\Models\GlobalCode\GlobalCode;
use App\Models\Patient\Patient;
use App\Models\Patient\PatientReferral;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Referral extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'referrals';
    use HasFactory;
    protected $guarded = [];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'id', 'patientId');
    }

    public function patientReferral()
    {
        return $this->belongsTo(PatientReferral::class, 'id', 'referralId');
    }

    public function referral()
    {
        return $this->hasMany(PatientReferral::class, 'patientReferralId');
    }

    public function designation()
    {
        return $this->belongsTo(GlobalCode::class, 'designationId', 'id');
    }
}
