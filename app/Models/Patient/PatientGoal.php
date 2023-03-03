<?php

namespace App\Models\Patient;

use App\Models\Note\Note;
use App\Models\Patient\Patient;
use App\Models\Vital\VitalField;
use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientGoal extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'patientGoals';
    use HasFactory;
    protected $guarded = [];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patientId');
    }

    public function vitalField()
    {
        return $this->belongsTo(VitalField::class, 'vitalFieldId');
    }

    public function deviceType()
    {
        return $this->belongsTo(GlobalCode::class, 'deviceTypeId');
    }

    public function frequencyType()
    {
        return $this->belongsTo(GlobalCode::class, 'frequencyTypeId');
    }

    public function notes()
    {
        return $this->hasOne(Note::class,'referenceId');
    }

}
