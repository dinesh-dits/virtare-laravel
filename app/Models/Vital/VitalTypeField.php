<?php

namespace App\Models\Vital;

use App\Models\Vital\VitalField;
use App\Models\GlobalCode\GlobalCode;
use App\Models\Patient\PatientGoal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VitalTypeField extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'vitalTypeFields';
    use HasFactory;
    protected $guarded = [];


    public function vitalType()
    {
        return $this->belongsTo(GlobalCode::class,  'vitalTypeId');
    }

    public function vitalField()
    {
        return $this->belongsTo(VitalField::class,  'vitalFieldId');
    }

    public function patientGoal(){
        return $this->hasMany(PatientGoal::class,  'vitalFieldId');
    }
}
