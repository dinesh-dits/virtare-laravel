<?php

namespace App\Models\Patient;

use App\Models\Patient\Patient;
use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientInsurance extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'patientInsurances';
    use HasFactory;
	protected $guarded = [];

    public function insuranceName()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'insuranceNameId');
    }

    public function insuranceType()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'insuranceTypeId');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patientId');
    }

    public function insuranceAdd(array $data)
    {
        return self::create($data);
    }
    
}
