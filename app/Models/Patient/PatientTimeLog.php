<?php

namespace App\Models\Patient;;

use App\Models\CPTCode\CptCodeActivity;
use App\Models\Note\Note;
use App\Models\Staff\Staff;
use App\Models\Patient\Patient;
use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientTimeLog extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'patientTimeLogs';
    use HasFactory;
    protected $guarded = [];

    public function category()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'categoryId');
    }

    public function logged()
    {
        return $this->hasOne(Staff::class, 'id', 'loggedId');
    }

    public function performed()
    {
        return $this->hasOne(Staff::class, 'id', 'performedId');
    }

    public function patient()
    {
        return $this->hasOne(Patient::class, 'id', 'patientId');
    }

    public function notes()
    {
        return $this->hasOne(Note::class, 'referenceId')->where('entityType', 'auditlog');
    }

    public function cptCode()
    {
        return $this->hasOne(CptCodeActivity::class, 'id', 'cptCodeId');
    }
}
