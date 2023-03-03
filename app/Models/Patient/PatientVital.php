<?php

namespace App\Models\Patient;

use App\Models\Note\Note;
use App\Models\Patient\Patient;
use App\Models\Vital\VitalField;
use App\Models\GlobalCode\GlobalCode;
use App\Models\Vital\VitalFlags;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientVital extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'patientVitals';
    use HasFactory;
    protected $guarded = [];


    public function vitalFieldNames()
    {
        return $this->hasOne(VitalField::class, 'id', 'vitalFieldId');
    }

    public function deviceType()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'deviceTypeId');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patientId');
    }

    public function notes()
    {
        return $this->hasOne(Note::class, 'referenceId');
    }

    public function icons()
    {
       return $this->hasOne(VitalFlags::class, 'id','flagId');
    }
}
