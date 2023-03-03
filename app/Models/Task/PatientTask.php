<?php

namespace App\Models\Task;

use App\Models\GlobalCode\GlobalCode;
use App\Models\Patient\Patient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientTask extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'patientTasks';
    use HasFactory;
	protected $guarded = [];

    public function patient()
    {
        return $this->belongsTo(Patient::class,'patientId');
    }

    public function priority()
    {
        return $this->belongsTo(GlobalCode::class , 'priorityId');
    }

    public function status()
    {
        return $this->belongsTo(GlobalCode::class , 'statusId');
    }
}
