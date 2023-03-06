<?php

namespace App\Models\Patient;

use App\Models\Condition\Condition;
use App\Models\Patient\Patient;
use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientCondition extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'patientConditions';
    use HasFactory;
	protected $guarded = [];
    
    public function condition()
    {
        return $this->hasOne(Condition::class,'id','conditionId');
    }

    public function patient()
    {
        return $this->hasMany(Patient::class,'id','patientId');
    }
}
